<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TickerPriceController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function index(string $ticker): BinaryFileResponse
    {
        $stock = Stock::query()->where('ticker', strtoupper($ticker))->first();
        if (!$stock) {
            $stock = Stock::query()->create([
                'name' => $ticker,
                'ticker' => strtoupper($ticker),
                'price' => '-',
                'change' => '-',
                'percent_change' => '-',
            ]);
            $this->getTickerData($stock);
            $this->generateStockBanner($stock);
        }

        return $this->printStockBanner($ticker);
    }

    /**
     * @throws ConnectionException
     */
    private function getTickerData(Stock $stock): void
    {
        // todo - fetch ticker name and update
        $this->fetchTradeData($stock);
    }

    /**
     * @throws ConnectionException
     */
    function fetchTradeData(Stock $stock): void
    {
        $apiKeyId = config('services.alpaca.api_key_id');
        $apiKeySecret = config('services.alpaca.api_key_secret');

        $baseURL = 'https://data.alpaca.markets';
        $response = Http::withHeaders([
            'APCA-API-KEY-ID' => $apiKeyId,
            'APCA-API-SECRET-KEY' => $apiKeySecret,
        ])
            ->get($baseURL. '/v2/stocks/'. $stock->ticker . '/snapshot')->json();

        $latestTrade = $response['latestTrade'];
        $previousDayBar = $response['prevDailyBar'];
        $change = round($latestTrade['p'] - $previousDayBar['c'], 2);
        $percent_change = round($change / $latestTrade['p'] * 100, 2);

        $stock->update([
            'price' => $latestTrade['p'],
            'change' => $change,
            'percent_change' => $percent_change,
        ]);
    }

    private function generateStockBanner(Stock $stock): void
    {
        $manager = new ImageManager(Driver::class);

        $image = $manager->create(540, 220)->fill('fff');

        $font_regular = resource_path().'/fonts/roboto/Roboto-Regular.ttf';
        $font_medium = resource_path().'/fonts/roboto/Roboto-Medium.ttf';
        $font_bold = resource_path().'/fonts/roboto/Roboto-Bold.ttf';
        $up_arrow = $manager->read(resource_path().'/icons/arrow_up.png')->resize(42, 54);
        $down_arrow = $manager->read(resource_path().'/icons/arrow_down.png')->resize(42, 54);
        $stock_positive = $stock->change >= 0;
        $change_sign = $stock_positive ? '+' : '-';
        $change_color = $stock_positive ? 'green' : 'red';

        $image->text(strtoupper($stock->ticker), 20, 48, function ($font) use ($font_bold) {
            $font->filename($font_bold);
            $font->size(48);
        });
        $image->text($stock->name, 20, 84, function ($font) use ($font_regular) {
            $font->filename($font_regular);
            $font->size(36);
        });
        $image->place(
            $stock_positive ? $up_arrow : $down_arrow,
            'top-left',
            20,
            130
        );
        $image->text($stock->price, 60, 184, function ($font) use ($font_medium) {
            $font->filename($font_medium);
            $font->size(80);
        });
        $image->text($change_sign.$stock->change, 320, 150, function ($font) use ($font_regular, $change_color) {
            $font->filename($font_regular);
            $font->size(28);
            $font->color($change_color);
        });
        $image->text($change_sign.$stock->percent_change.'%', 320, 180, function ($font) use ($font_regular, $change_color) {
            $font->filename($font_regular);
            $font->size(28);
            $font->color($change_color);
        });

        $imageData = $image->toPng();
        Storage::put('ticker/' . strtolower($stock->ticker) . '.png', $imageData);
    }

    private function printStockBanner(string $ticker): BinaryFileResponse
    {
        return response()->file(storage_path('app/private/ticker/' . strtolower($ticker) . '.png'));
    }
}
