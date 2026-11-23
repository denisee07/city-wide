<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session expired — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <meta http-equiv="refresh" content="3;url={{ request()->session()->previousUrl() ?: route('login') }}">
</head>
<body>
<section class="auth-shell" style="align-items:center;justify-content:center">
    <main class="auth-main">
        <div class="auth-card" style="text-align:center">
            <h1>Session expired</h1>
            <p class="muted">Your form was open for too long. A fresh form has been loaded for you.</p>
            <p><a href="{{ request()->session()->previousUrl() ?: route('login') }}" class="btn btn-primary">Try again</a></p>
            <p class="tiny dim">You will be redirected automatically in 3 seconds.</p>
        </div>
    </main>
</section>
</body>
</html>