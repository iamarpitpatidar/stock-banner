<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AlpacaService
{
    protected string $apiKeyId;
    protected string $apiKeySecret;
    protected string $baseURL;

    public function __construct()
    {
        $this->apiKeyId = config('services.alpaca.api_key_id');
        $this->apiKeySecret = config('services.alpaca.api_key_secret');
        $this->baseURL = 'https://data.alpaca.markets';
    }

    public function fetchTradeData(Stock $stock): void
    {
        try {
            $response = Http::withHeaders([
                'APCA-API-KEY-ID' => $this->apiKeyId,
                'APCA-API-SECRET-KEY' => $this->apiKeySecret,
            ])->get("{$this->baseURL}/v2/stocks/{$stock->ticker}/snapshot");

            if ($response->failed()) {
                throw new ConnectionException("Failed to fetch trade data for {$stock->ticker}");
            }

            $data = $response->json();
            $latestTrade = $data['latestTrade'] ?? null;
            $previousDayBar = $data['prevDailyBar'] ?? null;

            if (!$latestTrade || !$previousDayBar) {
                throw new \Exception("Incomplete data received for {$stock->ticker}");
            }

            $change = round($latestTrade['p'] - $previousDayBar['c'], 2);
            $percent_change = round(($change / $previousDayBar['c']) * 100, 2);

            $stock->update([
                'price' => $latestTrade['p'],
                'change' => $change,
                'percent_change' => $percent_change,
            ]);
        } catch (\Exception $e) {
            logger()->error("Error fetching data for {$stock->ticker}: {$e->getMessage()}");
        }
    }

    public function fetchAllTradeData(): void
    {
        $stocks = Stock::all();

        foreach ($stocks as $stock) {
            $this->fetchTradeData($stock);
        }
    }
}
