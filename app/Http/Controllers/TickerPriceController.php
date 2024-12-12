<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class TickerPriceController extends Controller
{
    public function index(string $ticker): View
    {
//        $this->getTickerData($ticker);
//        $this->getTickerPrice($ticker);
        $stock = Stock::query()->where('ticker', $ticker)->first();
        return $this->printStockBanner($stock);
    }

    private function getTickerData(string $ticker)
    {
        $apiKey = 'YOUR_API_KEY';
        $ticker = 'AAPL';
        $apiUrl = "https://finnhub.io/api/v1/quote?symbol={$ticker}&token={$apiKey}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            $data = json_decode($response, true);

            $currentPrice = $data['c'];
            $previousClose = $data['pc'];
            $percentChange = (($currentPrice - $previousClose) / $previousClose) * 100;

            // Output the results
            echo "Stock: {$ticker}\n";
            echo "Current Price: \$" . number_format($currentPrice, 2) . "\n";
            echo "Percentage Change: " . number_format($percentChange, 2) . "%\n";
            Stock::query()->updateOrCreate(
                [
                    'name' => 'Real'
                ],
                ['ticker' => $ticker],
            );
        }
        curl_close($ch);
    }

    private function getTickerPrice(string $ticker)
    {

    }

    private function printStockBanner()
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

        $image = $manager->create(512, 512)->fill('ccc');
        $imageData = $image->toPng();
        Storage::put('public/ticker/' . 'AAPL' . '.png', $imageData);
        exit;
    }
}
