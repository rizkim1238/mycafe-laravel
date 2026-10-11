<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = 'rewards';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'poin_cost',
        'stock',
        'category',
        'image',
        'is_active',
        'valid_until',
    ];

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class, 'reward_id');
    }
}
