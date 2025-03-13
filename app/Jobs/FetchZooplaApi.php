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
            $this->fetchZooplaOutcode($this->report->id, $this->report->outcode, 'to-rent');
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
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'zoopla.p.rapidapi.com',
                'x-rapidapi-key' => 'dbb14e07dcmsh1113cc6e5f1914dp15b1e6jsn904fb28e06e2',
            ])->get("https://zoopla.p.rapidapi.com/properties/v2/list?locationValue={$outcode}&section={$listing_type}&locationIdentifier={$outcode}&page={$currentPage}");

            $result = $response->getBody();
            $json = json_decode($result, true);


            if ($totalPages === null) {
                $totalPages = ceil($json['data']['pagination']['totalResults'] / count($json['data']['listings']['regular']));
            }

            $listings = $json['data']['listings']['regular'];

            $prices = Arr::pluck($listings, 'pricing.value');


            foreach ($listings as $listing) {
                $latitude = $listing['location']['coordinates']['latitude'];
                $longitude = $listing['location']['coordinates']['longitude'];

                $postcode = Http::get("https://api.postcodes.io/postcodes/?lon={$longitude}&lat={$latitude}")->json()['result'][0] ?? null;

                ray($listing);
                Listing::updateOrCreate(
                    [
                        'listing_id' => 'Z_' . $listing['listingId'],
                        'report_id' => $report_id,
                    ],
                    [
                        // 'sale_price' => $listing['pricing']['value'] ?? null,
                        'rental_price' => $this->priceIsWeekly($listing['pricing']['value'], $prices) ? $listing['pricing']['value'] * 4.34524 : $listing['pricing']['value'],

                        'bedrooms' => $listing['attributes']['bedrooms'],
                        'bathrooms' => $listing['attributes']['bathrooms'],

                        'property_type' => Str::of($listing['title'])
                            ->replace(' to rent', '')
                            ->replace(' to buy', '')
                            ->replace(($listing['attributes']['bedrooms'] . ' bed '), ''),
                        'property_status' => str_replace('-', ' ', $listing_type),
                        'description' => $listing['title'],

                        'address' => $listing['address'],
                        'latitude' => $listing['location']['coordinates']['latitude'],
                        'longitude' => $listing['location']['coordinates']['longitude'],

                        'postcode' => $postcode['postcode'],
                        'outcode' => $postcode['outcode'],
                        'district' =>
                        preg_replace('/[^A-Z].*/', '', $postcode['postcode'],),
                        'subcode' => substr($postcode['postcode'], 0, -2),

                    ]
                );
            }
            $currentPage++;
        }
    }
    private function priceIsWeekly($price, $prices)
    {
        $average_prices = array_sum($prices) / count($prices);
        $stdDev = sqrt(array_sum(array_map(function ($x) use ($average_prices) {
            return pow($x - $average_prices, 2);
        }, $prices)) / count($prices));

        if ($price < $average_prices - 3 * $stdDev) {
            return true;
        } else {
            return false;
        }
    }
}
