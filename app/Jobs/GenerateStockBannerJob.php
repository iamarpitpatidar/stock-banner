<?php

namespace App\Jobs;

use App\Models\Stock;
use App\Services\StockBannerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;

class GenerateStockBannerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Stock $stock;

    /**
     * Create a new job instance.
     */
    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }

    /**
     * Execute the job.
     */
    public function handle(StockBannerService $stockBannerService): void
    {
        $stockBannerService->generateStockBanner($this->stock);
    }
}
