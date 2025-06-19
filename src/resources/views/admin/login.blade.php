@extends('layouts.auth')

@section('title', 'ログイン')

@section('content')
<div class="auth-content">
    <h1>管理者ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'is-error' : '' }}">
        @error('email')
        <div class="error-message">{{ $message }}</div>
        @enderror

        <label for="password">パスワード</label>
        <input type="password" id="password" name="password" class="{{ $errors->has('password') ? 'is-error' : '' }}">
        @error('password')
        <div class="error-message">{{ $message }}</div>
        @enderror

        <button type="submit" class="register-btn">管理者ログインする</button>
    </form>

</div>
@endsection
