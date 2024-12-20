<?php

namespace App\Services;

use App\Models\Stock;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;

class StockBannerService
{
    protected ImageManager $imageManager;
    protected string $fontPath;
    protected string $iconPath;

    public function __construct()
    {
        $this->imageManager = new ImageManager(Driver::class);
        $this->fontPath = resource_path('fonts/roboto/');
        $this->iconPath = resource_path('icons/');
    }

    public function generateStockBanner(Stock $stock): void
    {
        $image = $this->imageManager->create(540, 220)->fill('#ffffff');

        $fontBold = "{$this->fontPath}Roboto-Bold.ttf";
        $fontRegular = "{$this->fontPath}Roboto-Regular.ttf";
        $fontMedium = "{$this->fontPath}Roboto-Medium.ttf";

        $upArrow = $this->imageManager->read("{$this->iconPath}arrow_up.png")->resize(42, 54);
        $downArrow = $this->imageManager->read("{$this->iconPath}arrow_down.png")->resize(42, 54);

        $isPositive = $stock->change >= 0;
        $changeColor = $isPositive ? 'green' : 'red';
        $changeSign = $isPositive ? '+' : '';

        $image->text(strtoupper($stock->ticker), 20, 48, function ($font) use ($fontBold) {
            $font->file($fontBold);
            $font->size(48);
            $font->color('#000');
        });

        $image->text($stock->name, 20, 84, function ($font) use ($fontRegular) {
            $font->file($fontRegular);
            $font->size(36);
            $font->color('#666');
        });

        $image->place($isPositive ? $upArrow : $downArrow, 'top-left', 20, 130);

        $image->text($stock->price, 60, 184, function ($font) use ($fontMedium) {
            $font->file($fontMedium);
            $font->size(80);
            $font->color('#000');
        });

        $image->text("{$changeSign}{$stock->change}", 320, 150, function ($font) use ($fontRegular, $changeColor) {
            $font->file($fontRegular);
            $font->size(28);
            $font->color($changeColor);
        });

        $image->text("{$changeSign}{$stock->percent_change}%", 320, 180, function ($font) use ($fontRegular, $changeColor) {
            $font->file($fontRegular);
            $font->size(28);
            $font->color($changeColor);
        });

        $filePath = "ticker/" . strtolower($stock->ticker) . ".png";
        Storage::put($filePath, $image->toPng());
    }

    public function getStockBannerPath(string $ticker): string
    {
        return storage_path('app/private/ticker/' . strtolower($ticker) . '.png');
    }
}
