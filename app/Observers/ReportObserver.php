<?php

namespace App\Observers;

use App\Models\Report;

class ReportObserver
{
    /**
     * Handle the Report "created" event.
     */
    public function saved(Report $report): void
    {

        if ($report->source == 'openrent_scraper') {
            \App\Jobs\FetchOpenrentScraper::dispatch($report);
        }

        if ($report->source == 'zoopla_api') {
            \App\Jobs\FetchZooplaApi::dispatch($report);
        }
    }
}
