<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    protected $table = 'reward_redemptions';
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'reward_id',
        'poin_used',
        'status',
        'redeemed_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}
