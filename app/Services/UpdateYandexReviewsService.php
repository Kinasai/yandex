<?php
namespace App\Services;

use App\Models\Organization;
use App\Models\Review;
use Carbon\Carbon;

class UpdateYandexReviewsService
{

    protected $org_id;
    protected $org;
    protected $apiService;
    public function __construct($org_id)
    {
        $this->org_id = $org_id;
        $this->org = Organization::query()->findOrFail($org_id);
        $this->apiService = new ExternalApiService($this->org['user_id']);
    }

    public function __invoke()
    {
        $this->request_reviews_all();
    }

    public function update_reviews_count(): void
    {
        $answer = $this->apiService->get('/business/fetchReviews',[
            'ajax' => 1,
            'businessId' => $this->org['yandex_business_id'],
            'locale' => 'ru_RU',
            'page' => 1,
            'pageSize' => 1,
        ]);

        if(isset($answer['data'])) {
            Review::query()->updateOrCreate(
                [
                    'organization_id' => $this->org['id'],
                    'yandex_review_id' => (string) $answer['data']['reviews'][0]['reviewId'],
                ],
                [
                    'review_text' => $answer['data']['reviews'][0]['text'] ?? '',
                    'rating' => (float) ($answer['data']['reviews'][0]['rating'] ?? 0),
                    'author_name' => $answer['data']['reviews'][0]['author']['name'] ?? 'Anonymous',
                    'review_date' => Carbon::parse($answer['data']['reviews'][0]['updatedTime']),
                ]
            );

            $list = Organization::query()
                ->where('yandex_business_id', $this->org['yandex_business_id'])
                ->get();

            foreach ($list as $item) {
                $item->update([
                    'total_reviews_count' => $answer['data']['params']['count'],
                    'needs_update' => $answer['data']['params']['count'] > $this->org['total_reviews_count'],
                ]);
            }
        }
    }
    public function request_reviews_all($forced = false): array
    {
        $reviews = [];
        $pages = 1;
        $force = false;

        for ($page = 1; $page <= $pages; $page++) {
            $array = $this->apiService->get('/business/fetchReviews',[
                'ajax' => 1,
                'businessId' => $this->org['yandex_business_id'],
                'locale' => 'ru_RU',
                'page' => $page,
                'pageSize' => 50,
            ]);

            if(isset($array['data'])){
                if(isset($array['data']['reviews'])){
                    $reviews = [...$reviews, ...$array['data']['reviews']];
                    $this->update_revives($array['data']['reviews']);
                }
                if(isset($array['data']['params'])){
                    $pages = intval($array['data']['params']['totalPages']);
                }
            }else if(isset($array['error'])){
                if(isset($array['error']['code'])){
                    break;
                }
            }else{
                if(!$forced){
                    $force = true;
                }
                break;
            }
        }
        if($force){
            $this->apiService->getToken();
            $this->request_reviews_all(true);
        }

        return $reviews;
    }

    public function update_revives($array)
    {
        $reviews = $array;
        foreach ($reviews as $review) {
            Review::query()->updateOrCreate(
                [
                    'organization_id' => $this->org['id'],
                    'yandex_review_id' => (string) $review['reviewId'],
                ],
                [
                    'review_text' => $review['text'] ?? '',
                    'rating' => (float) ($review['rating'] ?? 0),
                    'author_name' => $review['author']['name'] ?? 'Anonymous',
                    'review_date' => Carbon::parse($review['updatedTime']),
                ]
            );
        }
    }
}
