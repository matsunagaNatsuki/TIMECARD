<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > 1) {
            @ob_end_clean();
        }
        parent::tearDown();
    }

    // 勤怠一覧情報取得機能（一般ユーザー）
    public function test_attendance_user_list_page_data_get_function()
    {
        $user = User::factory()->create();
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

        $breakMinutes = 60;
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);

        $workMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;

        $breakFormatted = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        $totalFormatted = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(
            route('attendance.list', ['month' => $previousMonthDate->format('Y-m')])
        );

        $response->assertStatus(200);

        $response->assertSeeText($attendance->date->format('m/d'));
        $response->assertSeeText($attendance->clock_in->format('H:i'));
        $response->assertSeeText($attendance->clock_out->format('H:i'));
        $response->assertSeeText($breakFormatted);
        $response->assertSeeText($totalFormatted);
    }

    public function test_attendance_user_list_page_next_month_get()
    {
        $user = \App\Models\User::factory()->create();

        $nextMonthDate = now()->subMonth()->startOfMonth();
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => $nextMonthDate->toDateString(),
            'clock_in'  => $nextMonthDate->copy()->setTime(9, 0),
            'clock_out' => $nextMonthDate->copy()->setTime(18, 0),
            'status'    => 'clock_out',
        ]);

        $attendance->breaks()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $nextMonthDate->copy()->setTime(12, 0),
            'break_end' => $nextMonthDate->copy()->setTime(13, 0),
        ]);

        $breakMinutes = 60;
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);

        $workMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;

        $breakFormatted = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        $totalFormatted = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(
            route('attendance.list', ['month' => $nextMonthDate->format('Y-m')])
        );

        $response->assertStatus(200);

        $response->assertSeeText($attendance->date->format('m/d'));
        $response->assertSeeText($attendance->clock_in->format('H:i'));
        $response->assertSeeText($attendance->clock_out->format('H:i'));
        $response->assertSeeText($breakFormatted);
        $response->assertSeeText($totalFormatted);
    }

    public function test_attendance_user_list_page_detail_redirect()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->setTime(9,0),
            'clock_out' => now()->setTime(18,0),
            'status' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('詳細');

        $detailResponse = $this->actingAs($user)->get(
            'attendance/detail/' . $attendance->id
        );

        $detailResponse->assertStatus(200);
    }

    // 勤怠一覧情報取得機能（管理者）
    public function test_attendance_admin_list_page_all_users_attendance_day()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/attendances');
        $response->assertStatus(200);

        $users = User::factory()->count(2)->create(['role' => 'users']);

        $date = now()->toDateString();

        foreach($users as $index => $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date,
                'clock_in' => sprintf('%02d:00', $index + 8),
                'clock_out' => sprintf('%02d:00', $index + 17),
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/attendances?date=' . $date);
        $response->assertStatus(200);

        foreach ($users as $index => $user) {
            $clockIn = Carbon::createFromTime($index + 8,0)->format('H:i');
            $clockOut = Carbon::createFromTime($index + 17,0)->format('H:i');

            $totalWorkTime = Carbon::parse($clockIn)->diffInHours(Carbon::parse($clockOut));

            $breakHours = 1;

            $response->assertSee($user->name);
            $response->assertSee($clockIn);
            $response->assertSee($clockOut);

            $response->assertSee((string)$totalWorkTime);
            $response->assertSee((string)$breakHours);


        }
    }

    public function test_attendance_admin_list_page_now_date_check()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $date = now()->format('Y/m/d');

        $response = $this->actingAs($admin)->get('/admin/attendances');
        $response->assertStatus(200);

        $response->assertSeeText($date);
    }

    public function test_attendance_admin_list_page_previous_day_get()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/attendances');
        $response->assertStatus(200);

        $yesterday = now()->subDay()->toDateString();

        $users = User::factory()->count(2)->create(['role' => 'users']);

        foreach($users as $index => $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $yesterday,
                'clock_in'  => Carbon::parse($yesterday)->setTime($index + 8, 0, 0),
                'clock_out' => Carbon::parse($yesterday)->setTime($index + 17, 0, 0),

            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/attendances?date=' . $yesterday);
        $response->assertStatus(200);

        foreach ($users as $index => $user) {
            $clockIn = Carbon::createFromTime($index + 8,0)->format('H:i');
            $clockOut = Carbon::createFromTime($index + 17,0)->format('H:i');

            $totalWorkTime = Carbon::parse($clockIn)->diffInHours(Carbon::parse($clockOut));

            $breakHours = 1;

            $response->assertSee($user->name);
            $response->assertSee($clockIn);
            $response->assertSee($clockOut);

            $response->assertSee((string)$totalWorkTime);
            $response->assertSee((string)$breakHours);
        }
    }
}
