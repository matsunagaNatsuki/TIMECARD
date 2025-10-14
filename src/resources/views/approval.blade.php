@extends('layouts.default')

@section('title', '修正申請承認画面(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/approval.css') }}">
@endsection

@section('content')
@include('components.admin_header')

@php
    $break1 = $attendanceRequest->breaks[0] ?? null;
    $break2 = $attendanceRequest->breaks[1] ?? null;
    $remarks = $attendanceRequest->remarks;
@endphp

<div class="container">
    <h1 class="page-title">勤怠詳細</h1>
    <form method="POST" action="{{ route('admin.approval', ['id' => $attendanceRequest->id]) }}" class="approval-form" novalidate>
        @csrf
        <div class="admin-approval">
            <table class="approval-table">
                <colgroup>
                    <col class="col-hd">
                    <col class="col-v">
                    <col class="col-v">
                </colgroup>
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td colspan="2" class="cell-center name">{{$attendanceRequest->requester->name}}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td class="cell-center left">{{$attendanceRequest->clock_in?->format('Y年') }}</td>
                        <td class="cell-center right">{{$attendanceRequest->clock_in?->format('n月j日') }}</td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td colspan="2" class="cell-pair">
                            <div class="pair">
                                <input type="time" name="clock_in" value="{{ old('clock_in', optional($attendanceRequest->clock_in)->format('H:i')) }}" class="time-input" >
                                <span class="sep">〜</span>
                                <input type="time" name="clock_out" value="{{ old('clock_out', optional($attendanceRequest->clock_out)->format('H:i')) }}" class="time-input">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td colspan="2" class="cell-pair">
                            <div class="pair">
                                <input type="time" name="breaks[0][start]" value="{{ old('breaks.0.start',optional($break1?->break_start)->format('H:i')) }}" class="time-input">
                                <span class="sep">〜</span>
                                <input type="time" name="breaks[0][end]" value="{{ old('breaks.0.end', optional($break1?->break_end)->format('H:i')) }}" class="time-input">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>休憩2</th>
                        <td colspan="2" class="cell-pair">
                            <div class="pair">
                                <input type="time" name="breaks[2][start]" value="{{ old('breaks.2.start', optional($break2?->break_start)->format('H:i')) }}" class="time-input">
                                <span type="sep">〜</span>
                                <input type="time" name="breaks[2][end]" value="{{ old('breaks.2.end', optional($break2?->break_end)->format('H:i')) }}" class="time-input">
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td colspan="2">
                            <textarea name="remarks" class="notes" rows="3">{{ old('remarks',$remarks) }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
@if ($attendanceRequest->status !== 'approved')
        <button type="submit" class="btn btn-approve">承認</button>
    </form>
@else
    <form>
        <button type="button" class="btn btn-approve" disabled>承認済み</button>
    </form>
@endif
</div>
@endsection
