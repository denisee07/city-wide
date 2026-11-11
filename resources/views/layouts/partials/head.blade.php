<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', config('app.name'))</title>

<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%2322d3ee'/><text x='50' y='68' font-size='52' font-family='Segoe UI,sans-serif' font-weight='800' text-anchor='middle' fill='%2306121a'>BS</text></svg>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">

{{-- Deliberately not deferred: stored display preferences must be on <html>
     before the first paint, or every page load flashes the wrong theme. --}}
<script src="{{ asset('js/access.js') }}?v={{ @filemtime(public_path('js/access.js')) }}"></script>

@stack('styles')

<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}" defer></script>
@stack('scripts')
