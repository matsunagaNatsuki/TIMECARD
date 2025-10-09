<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StampController extends Controller
{
    public function stamp()
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->with('breaks')
            ->first();

        $statusForView = $attendance->status ?? 'off_duty';

        if ($attendance && $attendance->status === 'clock_out') {
            $statusForView = 'clock_out';
            $attendance->save();
        }

        return view('stamp', [
            'attendance' => $attendance,
            'status' => $statusForView,
            'now' => now(),
        ]);
    }

    public function create(Request $request)
    {
        $action = $request->input('action');
        $userId= auth()->id();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrNew([
            'user_id' => $userId,
            'date' => $today,
        ]);

        switch($action) {
            case 'clock_in':
                if ($attendance->exists && $attendance->clock_in) {
                    return back();
                }
                $attendance->clock_in = now();
                $attendance->status = 'working';
                $attendance->save();
                return back();

            case 'break_start':
                if (! $attendance->exists || $attendance->status !== 'working') {
                    return back();
                }
                $attendance->breaks()->create(['break_start' => now()]);
                $attendance->status = 'on_break';
                $attendance->save();
                return back();

            case 'break_end';
                if (! $attendance->exists || $attendance->status !== 'on_break') {
                    return back();
                }
                $break = $attendance->breaks()
                    ->whereNull('break_end')
                    ->latest('break_start')
                    ->first();

                if ($break) {
                    $break->break_end = now();
                    $break->save();
                }
                $attendance->status = 'working';
                $attendance->save();
                return back();

            case 'clock_out':
                if (! $attendance->exists || $attendance->clock_out) {
                    return back();
                }
                if ($attendance->clock_out) {
                    return back();
                }
                $attendance->clock_out = now();
                $attendance->status ='clock_out';
                $attendance->save();
                return back();

            default:
                return back();
        }
    }
}
