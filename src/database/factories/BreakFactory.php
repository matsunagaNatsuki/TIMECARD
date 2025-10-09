<?php

namespace Database\Factories;


use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreakFactory extends Factory
{
    protected $model = BreakTime::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = Carbon::today()->setTime(rand(11,16), [0,15,30,45][rand(0,3)]);
        $end   = (clone $start)->addMinutes(rand(15,60));


        return [
            'attendance_id' => 1,
            'break_start' => $start,
            'break_end' => $end
        ];
    }
}
