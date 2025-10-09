@extends('layouts.default')

@section('title', '勤怠一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin_list.css') }}">
@endsection

@section('content')

@include('components.admin_header')
<div class="container">
    <h1 class="attendance-day">  {{ $displayDate1 }}の勤怠 </h1>

    <div class="Day-change">
        <a href="{{ route('admin.attendances',['date' => $prev->toDateString()]) }}" class="yesterday">← 前日</a>
        <h2 class="today">📅 {{ $displayDate2 }}</h2>
        <a href="{{ route('admin.attendances', ['date' => $next->toDateString()]) }}" class="tomorrow">翌日 →</a>
    </div>

    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                @php
                    $attendance = $user->attendances->first();
                @endphp
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $attendance?->clock_in?->format('H:i') ?? '' }}</td>
                    <td>{{ $attendance?->clock_out?->format('H:i') ?? '' }}</td>
                    <td>{{ $attendance?->break_time_formatted ?? '' }}</td>
                    <td>{{ $attendance?->total_work_time ?? '' }}</td>
                    <td>
                        @if($attendance)
                            <a href="{{ route('admin.detail', ['id' => $attendance->id]) }}">詳細</a>
                        @endif
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

</div>

