<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Models\Outcode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Report;
use Exception;

class FetchZooplaApi implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Report $report,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->report->status = 'processing';
        $this->report->saveQuietly();
        try {
            foreach ($this->report->outcodes as $outcode) {
                $this->fetchZooplaOutcode($this->report->id, $outcode, 'to-rent');
            }
            $this->report->status = 'complete';
            $this->report->saveQuietly();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->report->error = $e->getMessage();
            $this->report->status = 'error';
            $this->report->saveQuietly();
        }
    }


    public function fetchZooplaOutcode($report_id, $outcode, $listing_type)
    {
        $currentPage = 1;
        $totalPages = null;
        while ($currentPage <= $totalPages || $totalPages === null) {
            $response = Http::retry(3, 3000)->withHeaders([
                'x-rapidapi-host' => 'zoopla.p.rapidapi.com',
                'x-rapidapi-key' => 'e05b6cba34mshfac615480d8b5ecp1261b1jsnad06f891ed05',
            ])->get("https://zoopla.p.rapidapi.com/properties/v2/list?locationValue={$outcode}&section={$listing_type}&locationIdentifier={$outcode}&page={$currentPage}");

            $result = $response->getBody();
            $json = json_decode($result, true);

            if (!isset($json['data']) || !isset($json['data']['listings']) || !isset($json['data']['listings']['regular']) || !$json['data']['listings']['regular']) {
                $totalPages = 0;
                return;
            }


            if ($totalPages === null) {
                $totalPages = ceil($json['data']['pagination']['totalResults'] / count($json['data']['listings']['regular']));
            }

            $listings = $json['data']['listings']['regular'];

            $prices = Arr::pluck($listings, 'pricing.value');

            foreach ($listings as $listing) {

                try {
                    $latitude = $listing['location']['coordinates']['latitude'];
                    $longitude = $listing['location']['coordinates']['longitude'];

                    $postcode = Http::timeout(10)->retry(3, function (int $attempt, Exception $esception) {
                        return $attempt * 1000;
                    })->get("https://api.postcodes.io/postcodes/?lon={$longitude}&lat={$latitude}")->json()['result'][0] ?? null;
                } catch (\Exception $e) {
                    $postcode = [];
                }

                if ($postcode) {

                    try {
                        Listing::updateOrCreate(
                            [
                                'listing_id' => 'Z_' . $listing['listingId'],
                                'report_id' => $report_id,
                            ],
                            [
                                // 'sale_price' => $listing['pricing']['value'] ?? null,
                                // 'rental_price' => $this->priceIsWeekly($listing['pricing']['value'], $prices) ? $listing['pricing']['value'] * 4.34524 : $listing['pricing']['value'],
                                'rental_price' => match (true) {
                                    Str::of($listing['pricing']['label'])->contains(['pppm', 'person']) => preg_replace('/[^0-9]/', '', $listing['pricing']['label']) * $listing['attributes']['bedrooms'],
                                    Str::of($listing['pricing']['label'])->contains(['pcm', 'month']) => preg_replace('/[^0-9]/', '', $listing['pricing']['label']),
                                    Str::of($listing['pricing']['label'])->contains(['pppw']) => preg_replace('/[^0-9]/', '', $listing['pricing']['label']) * $listing['attributes']['bedrooms'] * 4.34524,
                                    Str::of($listing['pricing']['label'])->contains(['pw', 'week']) => preg_replace('/[^0-9]/', '', $listing['pricing']['label']) * 4.34524,
                                    default => '??? ' . $listing['pricing']['label'],
                                },

                                'raw_price' => $listing['pricing']['label'] ?? null,

                                'bedrooms' => $listing['attributes']['bedrooms'],
                                'bathrooms' => $listing['attributes']['bathrooms'],

                                'property_type' => Str::of($listing['title'])
                                    ->replace(' to rent', '')
                                    ->replace(' to buy', '')
                                    ->replace(($listing['attributes']['bedrooms'] . ' bed '), ''),
                                'property_status' => str_replace('-', ' ', $listing_type),
                                'description' => $listing['title'],

                                'landlord' => $listing['agent']['branchName'] ?? null,

                                'address' => $listing['address'],
                                'latitude' => $listing['location']['coordinates']['latitude'],
                                'longitude' => $listing['location']['coordinates']['longitude'],

                                'postcode' => $postcode['postcode'] ?? null,
                                'outcode' => $postcode['outcode'] ?? null,
                                'district' =>
                                preg_replace('/[^A-Z].*/', '', $postcode['postcode'] ?? null),
                                'subcode' => substr($postcode['postcode'] ?? null, 0, -2),

                            ]
                        );
                    } catch (Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
            }
            $currentPage++;
        }
    }
    // private function priceIsWeekly($price, $prices)
    // {
    //     $average_prices = array_sum($prices) / count($prices);
    //     $stdDev = sqrt(array_sum(array_map(function ($x) use ($average_prices) {
    //         return pow($x - $average_prices, 2);
    //     }, $prices)) / count($prices));

    //     if ($price < $average_prices - 3 * $stdDev) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
}
