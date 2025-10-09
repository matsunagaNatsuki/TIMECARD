<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $months = [7,8,9];
        $year  = 2025;
        $users = User::all();

        foreach ($months as $month) {
                $start = Carbon::create($year, $month, 1)->startOfDay();
                $end   = (clone $start)->endOfMonth();

            foreach ($users as $user) {
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    if ($date->isSaturday() || $date->isSunday()) {
                        continue;
                    }

                    $clockInHour = rand(8, 11);
                    $clockInMin  = [0, 15, 30, 45][rand(0, 3)];
                    $clockIn     = $date->copy()->setTime($clockInHour, $clockInMin);


                    $workMinutes = rand(6 * 60, 10 * 60);


                    $proposedOut = (clone $clockIn)->addMinutes($workMinutes);
                    $latestOut   = $date->copy()->setTime(20, 0);
                    $clockOut    = $proposedOut->gt($latestOut) ? $latestOut : $proposedOut;


                    $attendance = Attendance::factory()->create([
                        'user_id'   => $user->id,
                        'date'      => $date->toDateString(),
                        'clock_in'  => $clockIn,
                        'clock_out' => $clockOut,
                        'status'    => 'clock_out',
                    ]);

                    $breakCount = rand(1, 2);
                    for ($i = 0; $i < $breakCount; $i++) {
                        $breakStartWindowStart = (clone $clockIn)->addHours(2);
                        $breakStartWindowEnd   = (clone $clockOut)->subHour();


                        if ($workMinutes < 240) {
                            $breakCount = 0;
                        } else {
                            $breakCount = rand(1, 2);
                        }

                        $breakLen   = rand(15, 60);
                        $breakStart = $clockIn->copy()->addHours(rand(2, 5));
                        $breakEnd   = (clone $breakStart)->addMinutes($breakLen);

                        if ($breakEnd->gt($clockOut)) {
                            $breakEnd = (clone $clockOut)->subMinutes(5);
                            if ($breakEnd->lte($breakStart)) {
                                $breakEnd = (clone $breakStart)->addMinutes(15);
                                if ($breakEnd->gt($clockOut)) {
                                    $breakEnd = (clone $clockOut)->subMinutes(1);
                                }
                            }
                        }

                        BreakTime::factory()->create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $breakStart,
                            'break_end'     => $breakEnd,
                        ]);
                    }
                }
            }
        }
    }
}
