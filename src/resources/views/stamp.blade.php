@extends('layouts.default')

@section('title', '勤怠登録画面')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/stamp.css') }}">
@endsection

@section('content')

@include('components.header')

    @php
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[$now->dayOfWeek];
        $labels = [
            'off_duty' => '勤務外',
            'working' => '出勤中',
            'on_break' => '休憩中',
            'clock_out' => '退勤済',
        ];
        $label = $labels[$status] ?? '勤務外';
    @endphp

<main class="stamps">
    <div class="status-label">{{ $label }}</div>

    <div class="date">
        {{ $now->format('Y年m月d日') }} ({{ $weekday }})
    </div>

    <div class="time">{{ $now->format('H:i') }}</div>

    @if ($status === 'off_duty')
        <form method="POST" action="{{ route('attendance.create') }}">
            @csrf
            <input type="hidden" name="action" value="clock_in">
            <button type="submit" class="work-btn">出勤</button>
        </form>

    @elseif ($status === 'working')
    <div class="btn-group">
        <form method="POST" action="{{ route('attendance.create') }}">
            @csrf
            <input type="hidden" name="action" value="clock_out">
            <button type="submit" class="work-btn">退勤</button>
        </form>

        <form method="POST" action="{{ route('attendance.create') }}">
            @csrf
            <input type="hidden" name="action" value="break_start">
            <button type="submit" class="break-btn">休憩入</button>
        </form>
    </div>


    @elseif ($status === 'on_break')
        <form method="POST" action="{{ route('attendance.create') }}">
            @csrf
            <input type="hidden" name="action" value="break_end">
            <button type="submit" class="break-btn">休憩戻</button>
        </form>

    @elseif ($status === 'clock_out')
        <form method="POST" action="{{ route('attendance.create') }}">
            @csrf
            <input type="hidden" name="action" value="clock_out">
        </form>
        <div class="clock_out-message">お疲れ様でした。</div>
    @endif
</main>
@endsection

