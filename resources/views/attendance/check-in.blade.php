<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-in required · {{ config('app.name') }}</title>
</head>
<body>
    <main>
        <h1>Check-in required</h1>
        <p>You must check in before accessing the Employee workspace.</p>
        <p>The check-in operation will be implemented in the Attendance phase.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
