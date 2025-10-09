<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\RequestBreak;
use App\Http\Requests\DetailRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;



class DetailController extends Controller
{
    // 勤怠詳細画面（一般ユーザー）を表示
    public function detail($id)
    {
        if (Str::startsWith($id, 'new-')) {
            $date = \Carbon\Carbon::createFromFormat('Ymd', Str::after($id, 'new-'))->format('Y-m-d');

            $attendance = new \App\Models\Attendance([
                'date' => $date,
                'clock_in' => null,
                'clock_out' => null,
                'remarks' => null,
                'user_id' => auth()->id(),
            ]);

            return view('user_detail', [
                'attendance' => $attendance,
                'break1' => null,
                'break2' => null,
                'myRequestStatus' => null,
                'isNew' => true,
            ]);
        }

        $attendance = Attendance::with(['breaks', 'user', 'changeRequest'])->findOrFail($id);
        $break1 = $attendance->breaks[0] ?? null;
        $break2 = $attendance->breaks[1] ?? null;

        $myRequestStatus = null;
        if ($attendance->changeRequest && (int)$attendance->changeRequest->request_by === (int)auth()->id()) {
            $myRequestStatus = $attendance->changeRequest->status;
        }

        return view('user_detail', compact('attendance','break1','break2', 'myRequestStatus'));
    }

    // 勤怠詳細画面（一般ユーザー）の勤怠の申請
    public function revise(DetailRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $base = $attendance->date->copy()->startOfDay();
        $toDT = fn (?string $hhmm) => $hhmm ? $base->copy()->setTimeFromTimeString($hhmm) : null;

        $clockIn = $toDT($request->input('clock_in'));
        $clockOut = $toDT($request->input('clock_out'));

        DB::transaction(function () use ($attendance, $request, $clockIn, $clockOut, $toDT) {
            $req = AttendanceRequest::updateOrCreate(
                ['attendance_id' => $attendance->id],
                [
                    'request_by' => $request->user()->id,
                    'approved_by' => null,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'remarks' => $request->input('remarks'),
                    'status' => 'pending',
                ]
            );

            $req->breaks()->delete();

            $rows = [];

            foreach ((array) $request->input('breaks', []) as $row) {
                $s = $row['start'] ?? null;
                $e = $row['end'] ?? null;
                if ($s && $e) {
                    $rows[] = [
                        'break_start' => $toDT($s),
                        'break_end' => $toDT($e),
                    ];
                }
            }

            if ($rows) {
                $req->breaks()->createMany($rows);
            }
        });

        return redirect()->route('attendance.list');
    }

    // 勤怠詳細画面（管理者）を表示
    public function adminDetail($id)
    {
        $attendance = Attendance::find($id);

        return view('admin_detail', compact('attendance'));
    }

    // 勤怠詳細画面（管理者）の勤怠の修正
    public function adminRevise(DetailRequest $request, $id)
    {
        $validated = $request->validated();
        $attendance = Attendance::findOrFail($id);
        $date = $attendance->date->toDateString();

        $date = $attendance->date->toDateString();
        $clockIn  = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$validated['clock_in']);
        $clockOut = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$validated['clock_out']);


        $attendance->update([
            'clock_in' => $validated['clock_in'],
            'clock_out' => $validated['clock_out'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $attendance->breaks()->delete();

        foreach (($validated['breaks'] ?? []) as $row) {
        $bs = $row['start'] ?? null;
        $be = $row['end']   ?? null;

            if ($bs && $be) {
                $start = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$bs);
                $end   = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$be);
                $attendance->breaks()->create([
                    'break_start' => $start,
                    'break_end'   => $end,
                ]);
            }
        }
            return redirect()->route('admin.attendances');
    }
}
