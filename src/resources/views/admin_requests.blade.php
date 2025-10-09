@extends('layouts.default')

@section('title', '申請一覧画面(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin_requests.css') }}">
@endsection

@section('content')
@include('components.admin_header')
<div class="container">
    <h1> 申請一覧</h1>
    <ul class="tabs">
        <li class="{{ $tab === 'pending' ? 'active' : '' }}">
            <a href="{{ route('admin.requests', ['tab' => 'pending']) }}">承認待ち</a>
        </li>
        <li class="{{ $tab === 'approved' ? 'active' : '' }}">
            <a href="{{ route('admin.requests', ['tab' => 'approved']) }}">承認済み</a>
        </li>
    </ul>

        <table class="request-table">
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
            @forelse ($requests as $req)
                <tr>
                    <td>
                        @if($req->status === 'pending')
                            承認待ち
                        @elseif ($req->status === 'approved')
                            承認済み
                        @else
                            {{ $req->status }}
                        @endif
                    </td>
                    <td>{{ $req->attendance?->user?->name ?? '不明' }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}</td>
                    <td>{{ $req->remarks }}</td>
                    <td>{{ $req->created_at->format('Y/m/d') }}</td>
                    <td> <a href="{{ route('admin.application', ['id' => $req->id]) }}" class="detail-btn">詳細</a></td>
                </tr>

            @empty
                <tr><td colspan="4">申請がありません</td></tr>
            @endforelse
            </tbody>
        </table>

</div>