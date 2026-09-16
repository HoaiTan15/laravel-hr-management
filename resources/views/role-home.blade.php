<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $role }} · {{ config('app.name') }}</title>
</head>
<body>
    <main>
        <h1>{{ $role }} area</h1>
        <p>Authentication and role access are available. Later HR modules are not implemented yet.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
