<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $ticker
 */
class Stock extends Model
{
    protected $fillable = [
        'name',
        'ticker',
        'price',
        'change'
    ];
}
