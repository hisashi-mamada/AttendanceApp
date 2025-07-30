@extends('layouts.auth')

@section('title', 'トップページ')

@section('content')
<div class="top-container">
    <h1 class="top-heading">勤怠管理システムへようこそ</h1>

    <div class="top-buttons">
        <a href="{{ route('login') }}" class="top-button">一般ユーザーログイン</a>
        <a href="{{ route('admin.login') }}" class="top-button admin-button">管理者ログイン</a>
    </div>
</div>
@endsection