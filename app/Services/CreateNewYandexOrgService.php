<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\ParseTask;
use Illuminate\Support\Facades\Auth;

class CreateNewYandexOrgService
{
    protected $task;
    protected $apiService;
    public function __construct(ParseTask $task)
    {
        $this->task = $task;
        $this->apiService = new ExternalApiService(Auth::id());

    }

    public function __invoke()
    {
        $this->createOrganization();
    }

    function createOrganization()
    {
        $this->task->update([
            'status' => 'processing'
        ]);

        try{
            $id = $this->extractYandexMapsId($this->task['url']);

            $request = $this->apiService->get('/search',[
                'ajax' => 1,
                'business_oid' => $id,
                'locale' => 'ru_RU',
                'origin' => 'maps-home_feed',
                'results' => 1,
                'search_add_snippet[0]' => 'tycoon_events/1.x',
                'snippets' => 'masstransit/2.x,panoramas/1.x,businessrating/1.x,businessimages/1.x,photos/2.x,videos/1.x,experimental/1.x,subtitle/1.x,visits_histogram/2.x,tycoon_owners_personal/1.x,tycoon_events/1.x,city_chains/1.x,route_point/1.x,topplaces/1.x,metrika_snippets/1.x,place_summary/1.x,online_snippets/1.x,provider_data/1.x,service_orgs_experimental/1.x,business_awards_experimental/1.x,business_filter/1.x,histogram/1.x,attractions/1.x,potential_company_owners:user,pin_info/1.x,lavka/1.x,bookings/1.x,bookings_personal/1.x,trust_features/1.x,plus_offers_experimental/1.x,discovery/1.x,toponym_discovery/1.x,relevant_discovery/1.x,visual_hints/1.x,matchedobjects/1.x,topobjects/1.x,org_offer/2.x,hotels_booking/1.x,stories_experimental/1.x,ugc_friends_likes/1.x,tycoon_promo_actions/1.x,route_to_choose/1.x,photogrammetry/1.x,fuel/1.x,realty_experimental/2.x,hot_water/1.x,neurosummary,mentioned_on_site/1.x,showtimes/1.x,afisha_json_geozen/1.x,realty_buildings/1.x,media_flow_stories_content/1.x'
            ]);

            $data = array_shift($request['data']['items']);

            $name = $data['title'] ?? $id;
//            $alt_rating_count = 0;
//            $alt_review_count = 0;
//            $alt_rating = 0;

//            foreach ($request['data']['items'] as $item) {
//                if (isset($item['ratingData'])) {
            $ratingData = $data['ratingData'];
            $alt_rating_count = $ratingData['ratingCount'];
            $alt_review_count = $ratingData['reviewCount'];
            $alt_rating = $ratingData['ratingValue'];
//                }
//            }
//            $ratingFromHint = null;
//            if($alt_rating > 0){
//                $ratingFromHint = $alt_rating;
//            }else{
//                foreach ($request['data']['items'] as $item) {
//                    $subtitleHints = $item['modularPin']['subtitleHints'] ?? [];
//
//                    foreach ($subtitleHints as $hint) {
//                        if (isset($hint['type']) && $hint['type'] === 'RATING') {
//                            $ratingFromHint = $hint['text'];
//                            break 2;
//                        }
//                    }
//                }
//            }

//            $count_reviews = 0;
//
//            if($alt_review_count > 0){
//                $count_reviews = $alt_review_count;
//            }else{
//
//                $answer = $this->apiService->get('/business/fetchReviews',[
//                    'ajax' => 1,
//                    'businessId' => $id,
//                    'locale' => 'ru_RU',
//                    'page' => 1,
//                    'pageSize' => 1,
//                ]);
//
//                if(isset($answer['data'])) {
//                    $count_reviews = $answer['data']['params']['count'];
//                }
//            }

            $org = Organization::create([
                'yandex_business_id' => $id,
                'user_id' => Auth::id(),
                'parse_task_id' => $this->task['id'],
                'name' => $name,
                'avg_rating' => floatval($alt_rating),
                'total_ratings_count' => intval($alt_rating_count),
                'total_reviews_count' => intval($alt_review_count)
            ]);

            $this->task->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            dispatch(new UpdateYandexReviewsService($org->id));

        }catch (\Exception $exception){
            $this->task->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage()
            ]);
        }

    }

    function extractYandexMapsId($url) {
        $patterns = [
            '/\/maps\/org\/[^\/]+\/(\d+)\//',
            '/poi%5Buri%5D=ymapsbm1%3A%2F%2Forg%3Foid%3D(\d+)/',
            '/poi\[uri\]=ymapsbm1:\/\/org\?oid=(\d+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
