<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    public $timestamps = false;

    protected $fillable = [
        'invoice_no',
        'member_id',
        'kasir_id',
        'subtotal',
        'diskon',
        'total',
        'poin_earned',
        'poin_redeemed',
        'payment_method',
        'status',
        'notes',
        'created_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }
}
