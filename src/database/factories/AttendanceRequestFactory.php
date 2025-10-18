<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceRequestFactory extends Factory
{
    protected $model = AttendanceRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'request_by'    => User::factory(),
            'approved_by'   => null,
            'clock_in'      => Carbon::now()->setTime(10, 0),
            'clock_out'     => Carbon::now()->setTime(19, 0),
            'remarks'       => $this->faker->sentence,
            'status'        => 'pending',
        ];
    }
}
