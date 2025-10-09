<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{

    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::today();

        $clockInHour = rand(8,11);
        $clockInMin = [0,15,30,45][rand(0,3)];
        $clockIn = $date->copy()->setTime($clockInHour, $clockInMin);

        $clockOutHour = rand(17,20);
        $clockOutMin = [0,15,30,45][rand(0,3)];
        $clockOut =$date->copy()->setTime($clockOutHour, $clockOutMin);

        return [
            'user_id' => 1,
            'date' => $date->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
