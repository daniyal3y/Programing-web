<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRaffleRequest;
use App\Models\Raffle;
use Illuminate\Http\JsonResponse;

final class RaffleController extends Controller
{
    public function store(StoreRaffleRequest $request): JsonResponse
    {
        $raffle = Raffle::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'data' => [
                'id' => $raffle->id,
                'name' => $raffle->name,
                'is_active' => $raffle->is_active,
            ],
        ], 201);
    }
}
