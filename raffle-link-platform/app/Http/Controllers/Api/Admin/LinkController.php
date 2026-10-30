<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IssueLinksRequest;
use App\Models\Raffle;
use App\Services\RaffleLinkIssuer;
use Illuminate\Http\JsonResponse;

final class LinkController extends Controller
{
    public function store(IssueLinksRequest $request, Raffle $raffle, RaffleLinkIssuer $issuer): JsonResponse
    {
        abort_unless($raffle->is_active, 409, 'This raffle is inactive.');

        $validated = $request->validated();
        $links = $issuer->issue(
            $raffle,
            (int) $validated['quantity'],
            isset($validated['expires_at']) ? new \DateTimeImmutable($validated['expires_at']) : null,
        );

        return response()->json([
            'data' => [
                'raffle_id' => $raffle->id,
                'issued' => count($links),
                'links' => $links,
            ],
        ], 201);
    }
}
