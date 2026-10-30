<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ثبت‌نام قرعه‌کشی | {{ $link->raffle_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<main class="shell">
    <section class="panel form-panel">
        <span class="eyebrow">EXCLUSIVE INVITATION</span>
        <h1>{{ $link->raffle_name }}</h1>
        <p>این لینک اختصاصی و امضاشده است. برای ثبت‌نام، اطلاعات زیر را وارد کنید.</p>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('raffles.entries.store', [$token, $signature]) }}">
            @csrf
            <label for="full_name">نام و نام خانوادگی</label>
            <input id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="120" required autocomplete="name">

            <label for="phone">شماره همراه (قالب بین‌المللی)</label>
            <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+989121234567" maxlength="16" required inputmode="tel" autocomplete="tel">

            <button class="button" type="submit">ثبت‌نام در قرعه‌کشی</button>
        </form>
        <p class="muted">هر شماره همراه در هر قرعه‌کشی فقط یک‌بار امکان ثبت‌نام دارد.</p>
    </section>
    <footer>درخواست‌های نامعتبر پیش از رسیدن به Laravel در Nginx رد می‌شوند.</footer>
</main>
</body>
</html>
