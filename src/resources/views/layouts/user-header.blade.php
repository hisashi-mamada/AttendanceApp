<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理システム')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>

    <header class="toppage-header">
        <div class="toppage-header-icon">
            <a href="{{ route('attendances.index') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECHロゴ">
            </a>
        </div>

        <nav class="toppage-header-nav">
            {{-- @auth --}}
            @if (Route::has('attendances.index'))
            <a href="{{ route('attendances.index') }}" class="toppage-nav-link">勤怠</a>
            @endif
            @if (Route::has('attendances.list'))
            <a href="{{ route('attendances.list') }}" class="toppage-nav-link">勤怠一覧</a>
            @endif
            @if (Route::has('request.list'))
            <a href="{{ route('request.list') }}" class="toppage-nav-link">申請</a>
            @endif
            {{-- @endauth --}}

            {{-- @guest --}}
            <a href="{{ route('login') }}" class="toppage-nav-link">ログイン</a>
            {{-- @endguest --}}
        </nav>
    </header>

    <main class="main-content">
        @yield('content')
    </main>

</body>

</html>
