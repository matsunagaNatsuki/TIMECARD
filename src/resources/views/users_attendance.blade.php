@extends('layouts.default')

@section('title', 'スタッフ別勤怠一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/users_attendance.css') }}">
@endsection

@section('content')

@include('components.admin_header')
<div class="container">
    <h1>{{ $user->name}}さんの勤怠</h1>

    {{-- 月切り替え --}}
    <div class="Month-change">
        <a href="{{ route('users.attendance', ['user' => $user, 'month' => $startOfMonth->copy()->subMonth()->format('Y-m')]) }}" class="previous-month">← 前月</a>
        <h2 class="month-display">📅 {{ $startOfMonth->format('Y/m') }}</h2>
        <a href="{{ route('users.attendance', ['user' => $user, 'month' => $startOfMonth->copy()->addMonth()->format('Y-m')]) }}" class="next-month">翌月 →</a>
    </div>

    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay())
                @php
                    $record = $attendances->get($date->format('Y-m-d'));
                    $breakMinutes = $record?->breaks->sum('break_time') ?? 0;
                    $breakFormatted = $breakMinutes ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60) : '';

                    if ($record && $record->clock_in && $record->clock_out) {
                        $totalMinutes = \Carbon\Carbon::parse($record->clock_in)
                            ->diffInMinutes(\Carbon\Carbon::parse($record->clock_out)) - $breakMinutes;
                        $totalFormatted = sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
                    } else {
                        $totalFormatted = '';
                    }
                @endphp

                <tr>
                    <td>{{ $date->format('m/d') }}({{ $date->locale('ja')->isoFormat('dd') }})</td>
                    <td>{{ $record?->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : ''}}</td>
                    <td>{{ $record?->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : ''}}</td>
                    <td>{{ $breakFormatted }}</td>
                    <td>{{ $totalFormatted }}</td>
                    <td>
                        @if($record)
                            <a href="{{ route('admin.detail', ['id' => $record->id]) }}">詳細</a>
                        @endif
                    </td>

                </tr>
            @endfor
        </tbody>
    </table>

    <div class="export-form">
        <a href="{{ route('export.csv', ['user' => $user->id, 'month' => $startOfMonth->format('Y-m')]) }}"   class="export_btn btn">CSV出力</a>
    </div>



</div>