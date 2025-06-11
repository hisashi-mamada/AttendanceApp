<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '会員登録')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <header class="toppage-header auth-header">
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECHロゴ" class="toppage-header-icon">
    </header>


    <main>
        @yield('content')
    </main>
</body>

</html>
