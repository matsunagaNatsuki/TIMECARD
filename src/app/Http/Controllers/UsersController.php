<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UsersController extends Controller
{
    // スタッフ一覧画面（管理者用）
    public function allUsers()
    {
        $users = User::query()
            ->select('id','name','email')
            ->get();

        return view('users', compact('users'));
    }

    // スタッフ別勤怠一覧画面（管理者用）
    public function userAttendance(Request $request, User $user)
    {
        $yearMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($yearMonth)->startOfMonth();
        $endOfMonth   = Carbon::parse($yearMonth)->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn($item) => $item->date instanceof Carbon
                ? $item->date->format('Y-m-d')
                : Carbon::parse($item->date)->format('Y-m-d')
            );

        return view('users_attendance', compact('yearMonth', 'startOfMonth', 'endOfMonth', 'attendances', 'user'));
    }

    public function export(Request $request, User $user): StreamedResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->toDateString());

        $filename = "{$user->name}さんの{$month}月分の勤怠.csv";

        return response()->streamDownload(function () use ($attendances, $start, $end, $user) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [$user->name . 'さんの勤怠']);

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    $key = $date->toDateString();
                    $record = $attendances->get($key);

                    $ymd = $date->format('m/d');
                    $weekMap = ['日','月','火','水','木','金','土'];
                    $weekday = $weekMap[$date->dayOfWeek];

                    $clockIn  = $record?->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '_';
                    $clockOut = $record?->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '_';

                    $breakMinutes = 0;
                        if ($record) {
                            foreach ($record->breaks as $break) {
                                if ($break->break_start && $break->break_end) {
                                    $breakMinutes += Carbon::parse($break->break_end)
                                        ->diffInMinutes(Carbon::parse($break->break_start));
                                }
                            }
                        }
                    $breakHours = $breakMinutes > 0
                        ? sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                        : '_';

                    $total = '_';
                        if ($record?->clock_in && $record?->clock_out) {
                            $workMinutes = Carbon::parse($record->clock_out)
                                ->diffInMinutes(Carbon::parse($record->clock_in)) - $breakMinutes;
                            $total = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                        }

                    fputcsv($handle, [
                        $ymd . "($weekday)",
                        $clockIn,
                        $clockOut,
                        $breakHours,
                        $total,
                    ]);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
