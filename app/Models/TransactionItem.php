<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'qty',
        'unit_price',
        'subtotal',
        'poin_per_item',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
