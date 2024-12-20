<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $ticker
 * @property int $price
 * @property int $change
 * @property int percent_change
 */
class Stock extends Model
{
    protected $fillable = [
        'name',
        'ticker',
        'price',
        'change',
        'percent_change',
    ];
}
