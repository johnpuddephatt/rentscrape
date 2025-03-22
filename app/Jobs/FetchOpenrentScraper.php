<?php

namespace App\Jobs;

use App\Models\Listing;
use App\Models\Outcode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Report;
use RoachPHP\Roach;

class FetchOpenrentScraper implements ShouldQueue
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
                Roach::startSpider(\App\Spiders\OpenRentSpider::class, context: [
                    'report_id' => $this->report->id,
                    'outcode' => $outcode,
                ]);
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
}
