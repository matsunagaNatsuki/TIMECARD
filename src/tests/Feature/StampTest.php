<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;

class StampTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 日時取得機能
    public function test_stamp_page_datetime_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee(now()->format('Y年m月d日'));
        $response->assertSee(now()->format('H:i'));
    }

    // ステータス確認機能
    public function test_stamp_page_status_off_duty()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'off_duty',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務外');
    }

    public function test_stamp_page_status_working()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_stamp_page_status_on_break()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'on_break',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_stamp_page_status_clock_out()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    // 出勤機能
    public function test_stamp_page_attendance_function_attendance_btn()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_stamp_page_attendance_function_attendance_check()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'clock_out'
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertDontSee('<button type="submit">出勤</button>', false);
    }

    public function test_stamp_page_attendance_function_work_time_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $attendance = \App\Models\Attendance::where('user_id', $user->id)->first();

        $date = \Carbon\Carbon::parse($attendance->clock_in)->format('m/d');
        $time = \Carbon\Carbon::parse($attendance->clock_in)->format('H:i');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($date);
        $response->assertSee($time);
    }

    // 休憩機能
    public function test_stamp_page_break_function_break_btn()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_start'
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }

    public function test_stamp_page_break_function_break_check()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_start'
        ]);

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_end'
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    public function test_stamp_page_break_function_break_end_btn()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_start'
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_end'
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSeeText('出勤中');
    }

    public function test_stamp_page_break_function_break_end_check()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_start'
        ]);

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_end'
        ]);

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_start'
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_stamp_page_break_function_break_time_list()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_start'
        ]);

        $this->actingAs($user)->post('/attendance', [
            'attendance_id' => $attendance->id,
            'action' => 'break_end'
        ]);

        $break = \App\Models\BreakTime::where('attendance_id', $attendance->id)->first();

        $breakStartDate = \Carbon\Carbon::parse($break->break_start)->format('m/d');
        $breakStartTime = \Carbon\Carbon::parse($break->break_start)->format('H:i');

        $breakEndDate = \Carbon\Carbon::parse($break->break_end)->format('m/d');
        $breakEndTime = \Carbon\Carbon::parse($break->break_end)->format('H:i');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($breakStartDate);
        $response->assertSee($breakStartTime);

        $response->assertSee($breakEndDate);
        $response->assertSee($breakEndTime);
    }

    public function test_stamp_page_clock_out_function_clock_out_btn()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => null,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test_stamp_page_clock_out_function_clock_out_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_in',
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'clock_out',
        ]);

        $attendance = \App\Models\Attendance::where('user_id', $user->id)->first();

        $date = \Carbon\Carbon::parse($attendance->clock_out)->format('m/d');
        $time = \Carbon\Carbon::parse($attendance->clock_out)->format('H:i');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($date);
        $response->assertSee($time);
    }






}
