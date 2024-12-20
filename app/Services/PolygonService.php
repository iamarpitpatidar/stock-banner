<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Support\Facades\Http;

class PolygonService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://api.polygon.io/v3/reference';
        $this->apiKey = config('services.polygon.api_key');
    }

    /**
     * Fetch stock details by ticker from Polygon.
     *
     * @param Stock $stock
     * @return void
     */
    public function fetchStockName(Stock $stock): void
    {
        $response = Http::get("{$this->baseUrl}/tickers/{$stock->ticker}", [
            'apiKey' => $this->apiKey,
        ]);

        if ($response->successful() && isset($response['results']['name'])) {
            $stock->update(['name' => $response['results']['name']]);
        }
    }
}
