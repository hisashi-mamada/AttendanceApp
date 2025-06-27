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

            @auth
            @if (Route::has('admin.attendances.index'))
            <a href="{{ route('admin.attendances.index') }}" class="toppage-nav-link">勤怠一覧</a>
            @endif
            @if (Route::has('admin.users.index'))
            <a href="{{ route('admin.users.index') }}" class="toppage-nav-link">スタッフ一覧</a>
            @endif
            @if (Route::has('admin.requests.index'))
            <a href="{{ route('admin.requests.index') }}" class="toppage-nav-link">申請一覧</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="toppage-nav-link" style="background:none; border:none; padding:0; cursor:pointer;">
                    ログアウト
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="toppage-nav-link">ログイン</a>
            @endauth
        </nav>

    </header>

    <main class="main-content">
        @yield('content')
    </main>

</body>

</html>