<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{

    protected $baseUrl;
    protected $sessionKey;
    protected $timeout = 30;
    protected $signatureService;

    public function __construct($userId = null)
    {
        $this->baseUrl = 'https://yandex.ru/maps/api';
        $this->sessionKey = 'external_api_' . ($userId ?? 'guest');
        $this->signatureService = new SignatureService();

    }

    public function getToken()
    {
        //костыль для получения сессии и токена
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . '/bookmarks/getSharedLists', ['ajax' => 1]);

            if ($response->successful()) {
                $data = $response->json();
                $cookies = $response->cookies();

                // Сохраняем данные сессии в кэш
                Cache::put($this->sessionKey, [
                    'csrfToken' => $data['csrfToken'] ?? null,
                    'cookies' => $cookies->toArray(),
                    'expires_at' => now()->addHours(2)->timestamp,
                    'created_at' => now()->timestamp,
                ], now()->addHours(2));

                Log::info('External API login successful', ['user_id' => auth()->id()]);

                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            Log::warning('External API login failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Login failed: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('External API login exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    public function get($endpoint, $params = [])
    {
        return $this->makeRequest('get', $endpoint, $params);
    }

    protected function makeRequest($method, $endpoint, $data = [], bool $refresh = false)
    {
        // Получаем данные сессии из кэша
        $sessionData = Cache::get($this->sessionKey);

        if (!$sessionData) {
            $this->getToken();
            $sessionData = Cache::get($this->sessionKey);
        }

        try {
            // Создаем HTTP клиент с куками и токеном
            $client = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]);

            if (!empty($sessionData['csrfToken'])) {
                $data['csrfToken'] = $sessionData['csrfToken'];
            }

            $data = $this->signatureService->generateQueryWithSignature($data);


            // Добавляем куки из сессии
            if (!empty($sessionData['cookies'])) {
                $cookies = [];
                foreach ($sessionData['cookies'] as $cookie) {
                    $cookies[$cookie['Name']] = $cookie['Value'];
                }
                $client = $client->withCookies($cookies, parse_url($this->baseUrl, PHP_URL_HOST));
            }

            // Выполняем запрос
            $response = $client->$method($this->baseUrl . $endpoint, $data);

            //не верно собрана сигнатура
            if ($response->status() === 400) {
                return [
                    'success' => false,
                    'message' => 'Invalid signature.',
                    'status' => 400,
                    'response' => $response->body()
                ];
            }
            // Если получили 200 и новый токен, возможно сессия истекла
            if ($response->status() === 200) {
                $responseData = $response->json();
                $responseCookies = $response->cookies();
                if(isset($responseData['csrfToken'])){
                    if($refresh){
                        return [
                            'success' => false,
                            'message' => 'Loop. New csrf token generated.',
                            'status' => 400,
                            'response' => $response->body()
                        ];
                    }
                    // Сохраняем данные сессии в кэш
                    Cache::put($this->sessionKey, [
                        'csrfToken' => $responseData['csrfToken'],
                        'cookies' => $responseCookies->toArray(),
                        'expires_at' => now()->addHours(2)->timestamp,
                        'created_at' => now()->timestamp,
                    ], now()->addHours(2));

                    Log::info('External API login successful', ['user_id' => auth()->id()]);

                    //запрос по новой
                    $this->makeRequest($method, $endpoint, $data, true);
                }else{
                    return $response->json();
                }
            }

//            return [
//                'success' => $response->successful(),
//                'status' => $response->status(),
//                'data' => $response->json(),
//                'headers' => $response->headers(),
//                'response' => $response
//            ];

        } catch (\Exception $e) {
            Log::error('External API request failed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function checkSession()
    {
        $sessionData = Cache::get($this->sessionKey);

        if (!$sessionData) {
            return [
                'active' => false,
                'message' => 'No active session'
            ];
        }

        if ($sessionData['expires_at'] < now()->timestamp) {
            Cache::forget($this->sessionKey);

            return [
                'active' => false,
                'message' => 'Session expired'
            ];
        }

        return [
            'active' => true,
            'expires_at' => date('Y-m-d H:i:s', $sessionData['expires_at']),
            'created_at' => date('Y-m-d H:i:s', $sessionData['created_at']),
            'sessionData' => $sessionData
        ];
    }

}
