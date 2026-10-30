<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Raffle extends Model
{
    protected $fillable = ['name', 'is_active', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }

    public function links(): HasMany
    {
        return $this->hasMany(RaffleLink::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RaffleEntry::class);
    }

    public function isAcceptingEntries(): bool
    {
        $now = now();

        return $this->is_active
            && ($this->starts_at === null || $now->greaterThanOrEqualTo($this->starts_at))
            && ($this->ends_at === null || $now->lessThanOrEqualTo($this->ends_at));
    }
}
