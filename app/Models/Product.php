<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'category',
        'price',
        'poin_value',
        'stock',
        'image',
        'is_active',
    ];
}
