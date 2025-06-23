@extends('layouts.admin-header')

@section('title', 'スタッフ一覧画面（管理者）')

@section('content')

<div class="staff-list-wrapper">
    <h2 class="attendance-detail-title"><span class="title-bar"></span>スタッフ一覧</h2>

    <table class="user-list-table">
        <thead>
            <tr>
                <th class="left">名前</th>
                <th>メールアドレス</th>
                <th class="right">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td class="left">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td class="right">
                    <a href="{{ route('admin.attendances.user_index', ['user' => $user->id]) }}">詳細</a>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
