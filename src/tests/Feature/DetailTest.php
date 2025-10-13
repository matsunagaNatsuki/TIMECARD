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

        $response = $this->actingAs($user)->get(
            'attendance/detail/' . $attendance->id
        );

        $response->assertStatus(200);

        $date = Carbon::parse($attendance->date);

        $response->assertSeeText($date->format('Y年'));
        $response->assertSeeText($date->format('n月j日'));
    }

    public function test_attendance_user_detail_page_attendance_mach()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9,0),
            'clock_out' => now()->setTime(18,0),
            'status' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get(
            'attendance/detail/' . $attendance->id)
            ->assertStatus(200);

        $response->assertSeeText($user->name);
        $response->assertSee('value="' . $attendance->clock_in->format('H:i') . '"', false);
        $response->assertSee('value="' . $attendance->clock_out->format('H:i') . '"', false);
    }

    public function test_attendance_user_detail_page_break_mach()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9,0),
            'clock_out' => now()->setTime(18,0),
            'status' => 'clock_out',
        ]);

        $break = $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->setTime(12,0),
            'break_end' => now()->setTime(13,0),
        ]);

        $response = $this->actingAs($user)->get(
            'attendance/detail/' . $attendance->id)
            ->assertStatus(200);

        $response->assertSeeText($user->name);
        $response->assertSee('value="' . $break->break_start->format('H:i') . '"', false);
        $response->assertSee('value="' . $break->break_end->format('H:i') . '"', false);
    }

    // 勤怠詳細情報修正機能（一般ユーザー）
    public function test_attendance_user_detail_page_clock_in_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9,0),
            'clock_out' => now()->setTime(18,0),
            'status' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get(
            'attendance/detail/' . $attendance->id
        );

        $response->assertStatus(200);

        $response = $this->actingAs($user)->post('attendance/detail/' . $attendance->id, [
            'date' => $attendance->date,
            'clock_in' => '19:00',
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_attendance_user_detail_page_break_start_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9,0),
            'clock_out' => now()->setTime(18,0),
            'status' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get(
            'attendance/detail/' . $attendance->id
        );

        $response->assertStatus(200);

        $response = $this->actingAs($user)->post('attendance/detail/' . $attendance->id, [
            'date' => $attendance->date,
            'breaks' => [
                ['start' => '19:00', 'end' => null],
            ],
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }
}
