@extends('layouts.default')

@section('title', '勤怠詳細画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin_detail.css') }}">
@endsection


@section('content')

@include('components.admin_header')

@php
    $break1 = $attendance->breaks[0] ?? null;
    $break2 = $attendance->breaks[1] ?? null;
    $remarks = $attendance->remarks;
@endphp

<div class="detail-wrap">
    <h1 class="page-title">勤怠詳細</h1>


    <form method="POST" action="{{ route('admin.revise', ['id' => $attendance->id]) }}" class="detail-form" novalidate>
        @csrf
        <div class="card attendance-detail">
            <table class="detail-table">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td colspan="2" class="cell-center name">{{ $attendance->user->name }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td class="cell-center">{{ $attendance->date?->format('Y年') }}</td>
                        <td class="cell-center">{{ $attendance->date?->format('n月j日') }}</td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td colspan="2" class="cell-pair">
                            <div class="pair">
                                <input type="time" name="clock_in"
                                        value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}"
                                        class="time-input">
                                <div class="form__error">
                                    @error('clock_in')
                                    {{ $message }}
                                    @enderror
                                </div>
                                <span class="sep">〜</span>
                                <input type="time" name="clock_out"
                                        value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}"
                                        class="time-input">
                                <div class="form__error">
                                    @error('clock_out')
                                    {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td colspan="2" class="cell-pair">
                            <div class="pair">
                                <input type="time" name="breaks[0][start]"
                                value="{{ old('breaks.0.start', optional($break1?->break_start)->format('H:i')) }}"
                                class="time-input">
                                    <div class="form__error">
                                        @error('breaks.0.start')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                <span class="sep">〜</span>
                                <input type="time" name="breaks[0][end]"
                                value="{{ old('breaks.0.end', optional($break1?->break_end)->format('H:i')) }}"
                                class="time-input">
                                    <div class="form__error">
                                        @error('breaks.0.end')
                                            {{ $message }}
                                        @enderror
                                    </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>休憩2</th>
                        <td colspan="2" class="cell-pair">
                            <div class="pair">
                                <input type="time" name="breaks[2][start]"
                                value="{{ old('breaks.2.start', optional($break2?->break_start)->format('H:i')) }}"
                                class="time-input">
                                    <div class="form__error">
                                        @error('breaks.2.start')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                <span class="sep">〜</span>
                                <input type="time" name="breaks[2][end]"
                                value="{{ old('breaks.2.end', optional($break2?->break_end)->format('H:i')) }}"
                                class="time-input">
                                    <div class="form__error">
                                        @error('breaks.2.end')
                                            {{ $message }}
                                        @enderror
                                    </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td colspan="2">
                            <textarea name="remarks" class="notes" rows="3">{{ old('remarks', $remarks) }}</textarea>
                            <div class="form__error">
                                @error('remarks')
                                    {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <button type="submit" class="btn-primary">修正</button>
        </div>
    </form>
</div>
@endsection
