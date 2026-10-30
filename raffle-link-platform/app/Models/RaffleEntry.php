<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaffleEntry extends Model
{
    protected $fillable = ['raffle_id', 'raffle_link_id', 'full_name', 'phone'];

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(RaffleLink::class, 'raffle_link_id');
    }
}
