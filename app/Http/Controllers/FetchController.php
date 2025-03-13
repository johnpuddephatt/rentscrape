<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class FetchController extends Controller
{

    function priceIsWeekly($price, $prices)
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

    public function zoopla($outcode, $listing_type)
    {
        $currentPage = 1;
        $totalPages = null;
        // while ($currentPage <= $totalPages || $totalPages === null) {
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

            Listing::updateOrCreate(
                ['zoopla_id' => $listing['listingId']],
                [

                    // 'sale_price' => $listing['pricing']['value'] ?? null,
                    'rental_price' => $this->priceIsWeekly($listing['pricing']['value'], $prices) ? $listing['pricing']['value'] * 4.34524 : $listing['pricing']['value'],

                    'bedrooms' => $listing['attributes']['bedrooms'],
                    'bathrooms' => $listing['attributes']['bathrooms'],

                    // 'property_type' => $listing['property_type'],
                    'property_status' => $listing_type,
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
        // }
    }
}
