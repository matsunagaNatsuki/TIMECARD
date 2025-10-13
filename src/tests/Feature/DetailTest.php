<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;

class DetailTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 勤怠詳細情報取得機能（一般ユーザー）
    public function test_attendance_user_detail_page_name_check()
    {
        $user = User::factory()->create([
            'name' => '松永 菜月',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'clock_out'
        ]);

        $response = $this->actingAs($user)->get(
            'attendance/detail/' . $attendance->id
        );

        $response->assertStatus(200);

        $response->assertSeeText($user->name);
    }

    public function test_attendance_user_detail_page_date_check()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->subMonth()->endOfMonth()->toDateString(),
            'clock_in' => now()->setTime(9,0),
            'clock_out' => now()->setTime(18,0),
            'status' => 'clock_out',
        ]);

        $response = $this->actingAS($user)->get(
            'attendance/detail/' . $attendance->id
        );

        $response->assertStatus(200);

        $date = Carbon::parse($attendance->date);

        $response->assertSeeText($date->format('Y年'));
        $response->assertSeeText($date->format('n月j日'));
    }
}
