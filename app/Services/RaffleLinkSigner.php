<?php

namespace App\Services;

use RuntimeException;

/**
 * Short URL format:
 *   /r/{22-char-random-token}.{32-char-lowercase-hex-HMAC}
 * The MAC is the first 128 bits of HMAC-SHA-256.
 */
final class RaffleLinkSigner
{
    private const TOKEN_BYTES = 16;
    private const MAC_HEX_LENGTH = 32;

    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(self::TOKEN_BYTES)), '+/', '-_'), '=');
    }

    public function sign(string $token): string
    {
        $secret = $this->secret();
        $mac = hash_hmac('sha256', $token, $secret, true);

        return bin2hex(substr($mac, 0, self::MAC_HEX_LENGTH / 2));
    }

    public function verify(string $token, string $signature): bool
    {
        if (! preg_match('/\A[A-Za-z0-9_-]{22}\z/D', $token)) {
            return false;
        }

        if (! preg_match('/\A[a-f0-9]{32}\z/D', $signature)) {
            return false;
        }

        try {
            return hash_equals($this->sign($token), $signature);
        } catch (RuntimeException) {
            return false;
        }
    }

    private function secret(): string
    {
        $secret = (string) config('raffle.link_secret', '');

        if (strlen($secret) < 32) {
            throw new RuntimeException('RAFFLE_LINK_SECRET must contain at least 32 characters.');
        }

        return $secret;
    }
}
