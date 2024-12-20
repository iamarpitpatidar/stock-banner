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
        $stock = Stock::query()->where('ticker', $ticker)->first();
//        if (!$stock) {
//            $stock = Stock::query()->create([
//                'name' => $ticker,
//                'ticker' => strtoupper($ticker),
//                'price' => '-',
//                'change' => '-'
//            ]);
            $this->getTickerData($stock);
//            $this->generateStockBanner($ticker);
//        }

        exit;
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
        $change = round(($latestTrade['p'] - $previousDayBar['c']) / $previousDayBar['c'] * 100, 2);

        $stock->update([
            'price' => $latestTrade['p'],
            'change' => $change
        ]);
    }

    private function generateStockBanner(string $ticker)
    {
        //        $width = request('width', 300);
//        $height = request('height', 300);
//        $color = request('color', 'ff0000');
//        $text = request('text', 'Dynamic Image');
//
//        $img = Image::canvas($width, $height, '#' . $color)
//            ->text($text, $width / 2, $height / 2, function ($font) {
//                $font->file(public_path('fonts/arial.ttf'));
//                $font->size(24);
//                $font->color('#ffffff');
//                $font->align('center');
//                $font->valign('middle');
//            });

        $manager = new ImageManager(Driver::class);

        $image = $manager->create(512, 512)->fill('456');
        $imageData = $image->toPng();
        Storage::put('ticker/' . $ticker . '.png', $imageData);
    }

    private function printStockBanner(string $ticker): BinaryFileResponse
    {
        return response()->file(storage_path('app/private/ticker/' . $ticker . '.png'));
    }
}
