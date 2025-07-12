@extends('layouts.user-header')

@section('title', '申請一覧画面（一般ユーザー）')

@section('content')
<div class="request-list-wrapper">
    <h2 class="request-list-title"><span class="title-bar"></span>申請一覧</h2>

    <div class="request-tab">
        <a href="{{ route('request.list', ['tab' => 'pending']) }}"
            class="tab {{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('request.list', ['tab' => 'approved']) }}"
            class="tab {{ $tab === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <div class="tab-underline"></div>

    <div class="request-table-container">
        <table class="request-table" id="table-pending">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                <tr>
                    <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $request->user->name ?? '不明' }}</td>
                    <td>{{ optional($request->attendance)->date ?? '---' }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td><a href="{{ route('request.detail', ['id' => $request->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">データがありません</td>
                </tr>
                @endforelse
            </tbody>

        </table>

        <table class="request-table hidden" id="table-approved">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>承認済み</td>
                    <td>西 伶奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <!-- ダミー承認済み行 -->
            </tbody>
        </table>
    </div>
</div>
@endsection