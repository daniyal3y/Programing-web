<?php

return [
    // Must be identical in Laravel and the Nginx master process environment.
    'link_secret' => env('RAFFLE_LINK_SECRET'),
    'admin_token' => env('RAFFLE_ADMIN_TOKEN'),
    'link_cache_ttl' => (int) env('RAFFLE_LINK_CACHE_TTL', 30),
    'entry_rate_limit' => (int) env('RAFFLE_ENTRY_RATE_LIMIT', 30),
    'admin_rate_limit' => (int) env('RAFFLE_ADMIN_RATE_LIMIT', 120),
];
