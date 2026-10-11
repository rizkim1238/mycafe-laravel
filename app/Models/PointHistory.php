<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointHistory extends Model
{
    protected $table = 'poin_history';
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'type',
        'poin',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
        'created_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
