<?php

namespace Tests\Feature;

use App\Models\User;

use Database\Seeders\DatabaseSeeder;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 勤怠一覧情報取得機能（一般ユーザー）
    public function test_attendance_user_list_page_data_get_function()
    {
        $user = User::find(11);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'clock_out'
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSeeText($attendance->date->format('m/d'));
        $response->assertSeeText($attendance->clock_in->format('H:i'));
        $response->assertSeeText($attendance->clock_out->format('H:i'));
    }

    public function test_attendance_user_list_page_month_get_function()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $startOfMonth = now()->format('Y/m');

        $response->assertSeeText($startOfMonth);
    }

    public function test_attendance_user_list_page_previous_month_get()
    {
        $user = \App\Models\User::factory()->create();

        $previousMonthDate = now()->subMonth()->startOfMonth();
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => $previousMonthDate->toDateString(),
            'clock_in'  => $previousMonthDate->copy()->setTime(9, 0),
            'clock_out' => $previousMonthDate->copy()->setTime(18, 0),
            'status'    => 'clock_out',
        ]);

        $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $previousMonthDate->copy()->setTime(12, 0),
            'break_end' => $previousMonthDate->copy()->setTime(13, 0),
        ]);

        $response = $this->actingAs($user)->get(
            route('attendance.list', ['month' => $previousMonthDate->format('Y-m')])
        );

        $response->assertStatus(200);

        $response->assertSeeText($attendance->date->format('m/d'));
        $response->assertSeeText($attendance->clock_in->format('H:i'));
        $response->assertSeeText($attendance->clock_out->format('H:i'));
    }
}
