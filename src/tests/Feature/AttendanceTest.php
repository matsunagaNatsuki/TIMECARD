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
}
