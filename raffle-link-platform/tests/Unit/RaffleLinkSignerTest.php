<?php

namespace Tests\Unit;

use App\Services\RaffleLinkSigner;
use Tests\TestCase;

class RaffleLinkSignerTest extends TestCase
{
    public function test_it_generates_a_short_signed_token(): void
    {
        $signer = app(RaffleLinkSigner::class);
        $token = $signer->generateToken();
        $signature = $signer->sign($token);

        $this->assertMatchesRegularExpression('/\A[A-Za-z0-9_-]{22}\z/', $token);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\z/', $signature);
        $this->assertTrue($signer->verify($token, $signature));
    }

    public function test_a_changed_token_or_signature_is_rejected(): void
    {
        $signer = app(RaffleLinkSigner::class);
        $token = $signer->generateToken();
        $signature = $signer->sign($token);

        $changedToken = ($token[0] === 'A' ? 'B' : 'A').substr($token, 1);
        $changedSignature = ($signature[0] === 'a' ? 'b' : 'a').substr($signature, 1);

        $this->assertFalse($signer->verify($changedToken, $signature));
        $this->assertFalse($signer->verify($token, $changedSignature));
        $this->assertFalse($signer->verify($token.'x', $signature));
    }
}
