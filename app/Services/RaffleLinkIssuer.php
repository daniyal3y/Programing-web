<?php

namespace App\Services;

use App\Models\Raffle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use RuntimeException;

final class RaffleLinkIssuer
{
    private const INSERT_BATCH_SIZE = 500;

    public function __construct(private readonly RaffleLinkSigner $signer)
    {
    }

    /**
     * Insert links in bounded batches. The unique token index remains the final
     * collision guard; failed candidates are regenerated instead of duplicated.
     *
     * @return list<array{token:string,url:string}>
     */
    public function issue(Raffle $raffle, int $quantity, ?\DateTimeInterface $expiresAt = null): array
    {
        $result = [];
        $pending = $quantity;
        $attempts = 0;

        while ($pending > 0 && $attempts < 5) {
            $attempts++;
            $batchSize = min(self::INSERT_BATCH_SIZE, $pending);
            $rows = [];
            $tokens = [];
            $timestamp = now();

            for ($i = 0; $i < $batchSize; $i++) {
                $token = $this->signer->generateToken();
                $tokens[] = $token;
                $rows[] = [
                    'raffle_id' => $raffle->id,
                    'token' => $token,
                    'is_active' => true,
                    'expires_at' => $expiresAt,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::table('raffle_links')->insertOrIgnore($rows);

            $insertedTokens = DB::table('raffle_links')
                ->where('raffle_id', $raffle->id)
                ->whereIn('token', $tokens)
                ->pluck('token')
                ->all();

            foreach ($insertedTokens as $token) {
                $result[] = [
                    'token' => $token,
                    'url' => URL::to('/r/'.$token.'.'.$this->signer->sign($token)),
                ];
            }

            $pending = $quantity - count($result);
        }

        if ($pending > 0) {
            throw new RuntimeException('Could not issue the requested number of unique raffle links.');
        }

        return $result;
    }
}
