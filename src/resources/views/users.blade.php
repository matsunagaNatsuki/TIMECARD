@extends('layouts.default')

@section('title', 'スタッフ一覧画面(管理画面）)')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/users.css') }}">
@endsection

@section('content')

@include('components.admin_header')
<div class="container">
    <h1 class="users-title">スタッフ一覧</h1>

    <table class="table staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('users.attendance', ['user' => $user->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>