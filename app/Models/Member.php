<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'members';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'member_code',
        'tier',
        'total_poin',
        'total_transaksi',
        'total_belanja',
        'join_date',
        'last_visit',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'member_id');
    }

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class, 'member_id');
    }

    public function pointHistory()
    {
        return $this->hasMany(PointHistory::class, 'member_id')->orderBy('id', 'desc');
    }
}
