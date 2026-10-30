<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;

final class RaffleLinkResolver
{
    public function __construct(private readonly RaffleLinkSigner $signer)
    {
    }

    /** Returns a minimal cached projection; invalid or inactive links return null. */
    public function resolve(string $token, string $signature): ?stdClass
    {
        if (! $this->signer->verify($token, $signature)) {
            return null;
        }

        $ttl = max(1, (int) config('raffle.link_cache_ttl', 30));

        /** @var stdClass|null $link */
        $link = Cache::remember("raffle-link:{$token}", now()->addSeconds($ttl), function () use ($token) {
            return DB::table('raffle_links')
                ->join('raffles', 'raffles.id', '=', 'raffle_links.raffle_id')
                ->where('raffle_links.token', $token)
                ->where('raffle_links.is_active', true)
                ->where('raffles.is_active', true)
                ->select([
                    'raffle_links.id as link_id',
                    'raffle_links.raffle_id',
                    'raffle_links.token',
                    'raffle_links.expires_at',
                    'raffles.name as raffle_name',
                    'raffles.starts_at',
                    'raffles.ends_at',
                ])
                ->first();
        });

        if ($link === null) {
            return null;
        }

        $now = now();

        if ($link->expires_at !== null && $now->greaterThan($link->expires_at)) {
            return null;
        }

        if ($link->starts_at !== null && $now->lessThan($link->starts_at)) {
            return null;
        }

        if ($link->ends_at !== null && $now->greaterThan($link->ends_at)) {
            return null;
        }

        return $link;
    }
}
