<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TickerPriceController;

Route::get('ticker/{ticker}', [TickerPriceController::class, 'index']);
