<?php

namespace App\Spiders;

use App\Models\Listing;
use Generator;
use Illuminate\Support\Facades\Log;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;
use Symfony\Component\DomCrawler\Crawler;
use RoachPHP\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class OpenRentSpider extends BasicSpider
{
    // public array $startUrls = [
    //     'https://www.openrent.co.uk/properties-to-rent/LS8?area=1'
    // ];

    protected function initialRequests(): array
    {
        return [
            new Request(
                'GET',
                'https://www.openrent.co.uk/properties-to-rent/' . $this->context['outcode'] . '?area=' . $this->context['radius'],
                [$this, 'parse']
            ),
        ];
    }

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        //
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    public int $concurrency = 4;

    public int $requestDelay = 1;

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {


        $result_count = -1;

        $result_count = $response
            ->filter('.filter-info-output .prop-count')
            ->text();


        $links = $response->filter('.property-list a.pli')->each(
            fn(Crawler $node, $i) => $node->attr('href')
        );

        foreach ($links as $link) {
            // echo 'requesting property at ' . $link . PHP_EOL;
            yield $this->request('GET', 'https://www.openrent.co.uk' . $link, 'parseProperty');
        }

        for ($page = 2; $page <= ceil($result_count / 20); $page++) {
            // echo 'requesting page ' . $page . PHP_EOL;
            $skip = ($page - 1) * 20;
            yield $this->request('GET', "https://www.openrent.co.uk/properties-to-rent/" . $this->context['outcode'] . "?skip=$skip&area=" . $this->context['radius'], 'parsePage');
        }
    }

    public function parsePage(Response $response): \Generator
    {
        // echo 'Parsing page... ' . PHP_EOL;
        $links = $response->filter('.property-list a.pli')->each(
            fn(Crawler $node, $i) => $node->attr('href')
        );

        foreach ($links as $link) {
            yield $this->request('GET', 'https://www.openrent.co.uk' . $link, 'parseProperty');
        }
    }

    public function parseProperty(Response $response): \Generator
    {

        $url = $response->getRequest()->getUri();
        $url_parts = explode('/', $url);
        $id = end($url_parts);

        $price = $this->safeFilter($response, ['.fs-d-4.fs-sm-d-3.fw-semibold.text-black', '.mb-1.fs-d-3.fw-semibold.lh-1'], null);
        $heading = $this->safeFilter($response, ['.fs-d-4.fs-lg-d-3.lh-md-sm', 'h1'], null);

        try {
            $postcode = $response->filter('a[href^="/comparebroadband"]')->attr('href');
            $postcode = Str::of($postcode)
                ->after('/comparebroadband?postCode=')
                ->replace('%20', ' ')
                ->__toString();

            try {
                $address_data = Http::retry(3, 1000)->get("https://api.postcodes.io/postcodes/{$postcode}")->json()['result'];
            } catch (\Exception $e) {
                $address_data = [];
            }
        } catch (\Exception $e) {
            $postcode = null;
        }

        if (!$postcode) {
            return;
        }

        try {
            $bedrooms = str_replace(' bedrooms', '', $response->filter('[data-lucide="bed"]')->nextAll()->text());
        } catch (\Exception $e) {
            try {
                $bedrooms = $response->filter('[data-lucide="bed"]')->closest('dt')->nextAll()->text();
            } catch (\Exception $e) {
                $bedrooms = null;
            }
        }

        try {
            $bathrooms = str_replace(' bathrooms', '', $response->filter('[data-lucide="bath"]')->nextAll()->text());
        } catch (\Exception $e) {
            try {
                $bathrooms = $response->filter('[data-lucide="bath"]')->closest('dt')->nextAll()->text();
            } catch (\Exception $e) {
                $bathrooms = null;
            }
        }

        // try {
        //     $tenants = $response->filter('[data-lucide="users"]')->nextAll()->text();
        // } catch (\Exception $e) {
        //     try {
        //         $tenants = $response->filter('[data-lucide="users"]')->closest('dt')->nextAll()->text();
        //     } catch (\Exception $e) {
        //         $tenants = null;
        //     }
        // }

        if ($heading) {
            if ($pos = strpos($heading, ',')) {
                $address = ltrim(substr($heading, $pos + 1));
            }

            $heading_parts = explode(',', $heading);
            // replace 'n Bed' where n is a number
            $property_type = preg_replace('/\d+ Bed /', '', $heading_parts[0]);
        } else {
            $address = null;
            $property_type = null;
        }

        try {
            $trs = $response->filterXpath("//h2[text()='Tenant Preference']")->nextAll()->filter('tr');
            if ($trs->count() == 0) {
                // Fallback for variations in the page layout
                $trs = $response->filterXpath("//h2[text()='Tenant Preference']")->closest('div')->nextAll()->filter('tr');
            }

            $preferences = [];
            $trs->each(function (Crawler $node) use (&$preferences) {
                $label = $node->filter('td')->text();
                $preferences[$label] = $node->filter('td')->nextAll()->filter('span')->attr('data-lucide') == 'check';
            });
        } catch (\Exception $e) {
            $preferences = [];
        }



        try {
            $landlord = $response->filterXpath("//h2[text()='Meet the landlord']")->nextAll()->text();
        } catch (\Exception $e) {
            try {
                $landlord = $response->filterXpath("//h2[text()='Meet the Landlord']")->nextAll()->nextAll()->children('p')->text();
            } catch (\Exception $e) {
                $landlord = null;
            }
        }


        $listing = [
            // 'url'   => $url,
            'description' => $heading,
            'address' => $address ?? '',
            'latitude' => $address_data['latitude'] ?? null,
            'longitude' => $address_data['longitude'] ?? null,
            'property_status' => 'to rent',
            'property_type' => $property_type,
            'rental_price' => preg_replace('/[^0-9\.]/', '', $price),
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'postcode' => $postcode,
            'outcode' => rtrim(substr($postcode, 0, -3)),
            'district' =>
            preg_replace('/[^A-Z].*/', '', $postcode),
            'subcode' => substr($postcode, 0, -2),

            'student_friendly' => $preferences['Student Friendly'] ?? null,
            'families_allowed' => $preferences['Families Allowed'] ?? null,
            'pets_allowed' => $preferences['Pets Allowed'] ?? null,
            'smokers_allowed' => $preferences['Smokers Allowed'] ?? null,
            'dss_covers_rent' => Arr::first($preferences, fn($value, $key) => Str::contains($key, 'DSS')) ?? null,

            'landlord' => $landlord,
            // 'tenants' => $tenants,
            // 'preferences' => $preferences,
        ];

        Listing::updateOrCreate(
            [
                'report_id' => $this->context['report_id'],
                'listing_id' => 'OR_' . $id,
            ],
            $listing
        );

        yield $this->item($listing);
    }

    private function safeFilter(Response $response, array $selectors, ?string $default = null): ?string
    {
        try {
            return $response->filter($selectors[0])->text();
        } catch (\Exception $e) {

            try {
                return $response->filter($selectors[1])->text();
            } catch (\Exception $e) {

                try {
                    return $response->filter($selectors[2])->text();
                } catch (\Exception $e) {
                    return $default;
                }
            }
        }
    }
}
