<?php

namespace App\Http\Controllers;

use App\Services\RaffleLinkResolver;

final class RaffleLandingController extends Controller
{
    public function show(string $token, string $signature, RaffleLinkResolver $resolver)
    {
        $link = $resolver->resolve($token, $signature);

        abort_if($link === null, 404);

        return response()
            ->view('raffles.show', ['link' => $link, 'token' => $token, 'signature' => $signature])
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function store(
        \App\Http\Requests\StoreEntryRequest $form,
        string $token,
        string $signature,
        RaffleLinkResolver $resolver,
    ) {
        $link = $resolver->resolve($token, $signature);
        abort_if($link === null, 404);

        $validated = $form->validated();
        $now = now();

        // The composite unique index (raffle_id, phone) makes this atomic under
        // concurrent requests. No pre-check-then-insert race is possible.
        $inserted = \Illuminate\Support\Facades\DB::table('raffle_entries')->insertOrIgnore([
            'raffle_id' => $link->raffle_id,
            'raffle_link_id' => $link->link_id,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($inserted === 0) {
            return back()->withErrors(['phone' => 'این شماره قبلاً در این قرعه‌کشی ثبت شده است.'])->withInput();
        }

        return redirect()->route('raffles.show', [$token, $signature])
            ->with('success', 'ثبت‌نام شما با موفقیت انجام شد.');
    }
}
