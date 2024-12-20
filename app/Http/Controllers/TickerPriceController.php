<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Services\AlpacaService;
use App\Services\StockBannerService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TickerPriceController extends Controller
{
    protected AlpacaService $alpacaService;
    protected StockBannerService $stockBannerService;

    public function __construct(
        AlpacaService $alpacaService,
        StockBannerService $stockBannerService
    ) {
        $this->alpacaService = $alpacaService;
        $this->stockBannerService = $stockBannerService;
    }

    /**
     */
    public function index(string $ticker): BinaryFileResponse
    {
        $stock = Stock::query()->firstOrCreate(
            ['ticker' => strtoupper($ticker)],
            ['name' => $ticker, 'price' => '-', 'change' => '-', 'percent_change' => '-']
        );

        if ($stock->wasRecentlyCreated) {
            // todo - fetch stock name as well
            $this->alpacaService->fetchTradeData($stock);
            $this->stockBannerService->generateStockBanner($stock);
        }

        return $this->printStockBanner($ticker);
    }

    private function printStockBanner(string $ticker): BinaryFileResponse
    {
        $path = $this->stockBannerService->getStockBannerPath($ticker);

        if (!file_exists($path)) {
            abort(404, "Stock banner for {$ticker} not found. Please check again later.");
        }

        return response()->file($path);
    }
}
