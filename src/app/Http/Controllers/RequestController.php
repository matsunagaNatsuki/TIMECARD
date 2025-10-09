<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // 申請一覧画面(一般ユーザー)
    public function request(Request $request)
    {
        $tab = $request->query('tab', 'pending');
        $query = AttendanceRequest::query()
            ->where('request_by', auth()->id());

        if ($tab === 'approved') {
            $query->where('status', 'approved');
        }elseif ($tab === 'pending') {
            $query->where('status', 'pending');
        }

        $requests = $query->latest()->paginate(10);

        return view('user_request', compact('tab','requests'));
    }

    // 申請一覧画面（管理者）
    public function adminRequest(Request $request)
    {
        $tab = $request->get('tab', 'pending');
        $requests = AttendanceRequest::query()
        ->select('requests.*', DB::raw('requests.id as request_id'))
        ->with(['attendance.user'])
        ->when($tab === 'pending', fn($q) => $q->where('status', 'pending'))
        ->when($tab === 'approved', fn($q) => $q->where('status','approved'))
        ->orderByDesc('created_at')
        ->paginate(10);

        return view('admin_requests', compact('tab', 'requests'));
    }

    public function application($id)
    {
        $attendanceRequest = AttendanceRequest::with(['attendance.user','breaks'])->findOrFail($id);

        return view('approval', [
            'attendanceRequest' => $attendanceRequest,
            'attendance'        => $attendanceRequest->attendance,
        ]);
    }

    public function approval(Request $http, $id)
    {
        $attendanceRequest = AttendanceRequest::with('requester','breaks','attendance')->findOrFail($id);

        if ($attendanceRequest->status !== 'approved') {
            $attendanceRequest->update([
                'status'      => 'approved',
                'approved_by' => $http->user()->id,
            ]);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $attendanceRequest->requester->id,
                'date' => $attendanceRequest->clock_in->toDateString(),
            ],
            [
                'clock_in' => $attendanceRequest->clock_in,
                'clock_out' => $attendanceRequest->clock_out,
                'remarks' => $attendanceRequest->remarks,
            ]
        );

        $attendance->breaks()->delete();

        foreach ($attendanceRequest->breaks()->orderBy('break_start')->get() as $rb) {
                    $attendance->breaks()->create([
                        'break_start' => $rb->break_start,
                        'break_end' => $rb->break_end,
                    ]);
                }

        return redirect()
            ->route('admin.application', ['id' => $attendanceRequest->id])
            ->with('success','承認しました。');

    }
}




