@extends('layouts.auth')

@section('title', 'メール認証')

@section('content')
<div class="verify-email" style="text-align: center; margin-top: 80px;">
    <h1 style="font-size: 18px; font-weight: bold; margin-bottom: 20px;">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </h1>

    <a href="#" class="verify-button" style="
        display: inline-block;
        background-color: #f3f3f3;
        border: 1px solid #999;
        padding: 12px 30px;
        border-radius: 4px;
        font-size: 16px;
        text-decoration: none;
        color: black;
        margin-bottom: 30px;
    ">
        認証はこちらから
    </a>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" style="
            background: none;
            border: none;
            color: #1e90ff;
            font-size: 14px;
            cursor: pointer;
            text-decoration: underline;
        ">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection
