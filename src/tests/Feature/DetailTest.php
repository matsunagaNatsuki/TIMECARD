<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;


class DetailTest extends TestCase
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

    public function test_attendance_user_detail_page_break_end_after_clock_out()
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
                ['start' => 'null', 'end' => '19:00'],
            ],
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_attendance_user_detail_page_remarks_null_check()
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
            'remarks' => null,
        ]);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    public function test_attendance_user_detail_page_approval_and_Application_list_check()
    {
        $user = User::factory()->create(['role' => 'users']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => $attendance->date,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '修正申請テスト',
        ]);
        $response->assertStatus(302);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);


        $listResponse = $this->actingAs($admin)->get(route('admin.requests'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee($attendance->date->format('Y/m/d'));
        $listResponse->assertSee('修正申請テスト');

        $request = AttendanceRequest::first();

        $appResponse = $this->actingAs($admin)->get(
            route('admin.application', ['id' => $request->id])
        );
        $appResponse->assertStatus(200);

        $appResponse->assertSee($attendance->date->format('Y年'));
        $appResponse->assertSee($attendance->date->format('n月j日'));
        $appResponse->assertSee('10:00');
        $appResponse->assertSee('19:00');
        $appResponse->assertSee('修正申請テスト');
    }

    public function test_attendance_user_detail_page_pending_list_check()
    {
        $user = User::factory()->create(['role' => 'users']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'date' => $attendance->date,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '修正申請テスト',
            'status' => 'pending',
        ]);
        $response->assertStatus(302);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $listResponse = $this->actingAs($admin)->get('/admin/requests?tab=pending');
        $listResponse->assertStatus(200);

        $listResponse->assertSee('承認待ち');
        $listResponse->assertSee($user->name);
        $listResponse->assertSee($attendance->date->format('Y/m/d'));
        $listResponse->assertSee('修正申請テスト');
    }

    public function test_attendance_user_detail_page_approved_list_check()
    {
        $user = User::factory()->create(['role' => 'users']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '修正申請テスト',
        ]);
        $response->assertStatus(302);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $request = AttendanceRequest::first();

        $this->actingAs($admin)->post(
            route('admin.approval', ['id' => $request->id]));

        $listResponse = $this->actingAs($admin)->get('/admin/requests');
        $listResponse->assertStatus(200);

        $listResponse = $this->actingAs($admin)->get('/admin/requests?tab=approved');
        $listResponse->assertStatus(200);

        $listResponse->assertSee('承認済み');
        $listResponse->assertSee($user->name);
        $listResponse->assertSee($attendance->date->format('Y/m/d'));
        $listResponse->assertSee('修正申請テスト');
    }

    public function test_attendance_user_detail_page_detail_redirect()
    {
        $user = User::factory()->create(['role' => 'users']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id, [
            'attendance_id' => $attendance->id,
            'date' => $attendance->date,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'remarks' => '修正申請テスト',
        ]);
        $response->assertStatus(302);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $listResponse = $this->actingAs($admin)->get('/admin/requests');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('詳細');

        $requestsResponse = $this->actingAs($admin)->get(
            'admin/requests/' . $attendance->id
        );
    }

    // 勤怠詳細情報取得・修正機能（管理者）
    public function test_attendance_admin_detail_page_data_select_check()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['role' => 'users']);
        $date = Carbon::now();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9,0,0),
            'clock_out' => $date->copy()->setTime(18,0,0),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.detail',
            ['id' => $attendance->id]
        ));
        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSeeText($date->format('Y年'));
        $response->assertSeeText($date->format('m月d日'));
        $response->assertSee($attendance->clock_in->format('H:i'));
        $response->assertSee($attendance->clock_out->format('H:i'));
    }

    public function test_attendance_admin_detail_page_clock_in_after_clock_out()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['role' => 'users']);
        $date = Carbon::now();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9,0,0),
            'clock_out' => $date->copy()->setTime(18,0,0),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.detail',
            ['id' => $attendance->id]
        ));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('admin.revise' , $attendance->id),[
            'date' => $attendance->date,
            'clock_in' => '19:00',
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_attendance_admin_detail_page_break_start_after_clock_out()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['role' => 'users']);
        $date = Carbon::now();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9,0,0),
            'clock_out' => $date->copy()->setTime(18,0,0),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.detail',
                ['id' => $attendance->id]
        ));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('admin.revise' , $attendance->id), [
            'date' => $attendance->date,
            'breaks' => [
                ['start' => '19:00', 'end' => null,]
            ],
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_attendance_admin_detail_page_break_end_after_clock_out()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['role' => 'users']);
        $date = Carbon::now();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9,0,0),
            'clock_out' => $date->copy()->setTime(18,0,0),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.detail',
                ['id' => $attendance->id]
        ));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post(route('admin.revise' , $attendance->id), [
            'date' => $attendance->date,
            'breaks' => [
                ['start' => 'null', 'end' => '19:00'],
            ],
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
        $response->assertStatus(302);
    }
}
