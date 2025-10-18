<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;

class RequestTest extends TestCase
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


    // 勤怠情報修正機能（管理者）
    public function test_requests_list_page_pending_tab_check()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $users = User::factory()->count(3)->create(['role' => 'users']);

        $requests = collect();
        foreach($users as $user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);

            $request = AttendanceRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'request_by' => $user->id,
                'clock_in' => now()->setTime(10,0),
                'clock_out' => now()->setTime(19,0),
                'remarks' => 'シフト変更',
                'status' => 'pending',
            ]);

            $requests->push($request);
        }

        $response = $this->actingAs($admin)->get(
            route('admin.requests', ['tab' => 'pending'])
        );
        $response->assertStatus(200);

        foreach($requests as $request) {
            $response->assertSeeText($request->remarks);
            $response->assertSeeText($user->name);
        }
    }

    public function test_requests_list_page_approved_tab_check()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $users = User::factory()->count(3)->create(['role' => 'users']);

        $requests = collect();
        foreach($users as $user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);

            $request = AttendanceRequest::factory()->create([
                'attendance_id' => $attendance->id,
                'request_by' => $user->id,
                'clock_in' => now()->setTime(10,0),
                'clock_out' => now()->setTime(19,0),
                'remarks' => 'シフト変更',
                'status' => 'approved',
            ]);

            $requests->push($request);
        }

        $response = $this->actingAs($admin)->get(
            route('admin.requests', ['tab' => 'approved'])
        );
        $response->assertStatus(200);

        foreach($requests as $request) {
            $response->assertSeeText($request->remarks);
            $response->assertSeeText($user->name);
        }
    }

    public function test_revise_application_application_page_detail_check()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['role' => 'users']);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'request_by' => $user->id,
            'clock_in' => noW()->setTime(10,0),
            'clock_out' => now()->setTime(19,0),
            'remarks' => 'シフト変更',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.application', $request->id)
        );
        $response->assertStatus(200);

        $response->assertSeeText($user->name);
        $response->assertSeeText(Carbon::parse($attendance->date)->format('Y年'));
        $response->assertSeeText(Carbon::parse($attendance->date)->format('n月j日'));
        $response->assertSee($request->clock_in->format('H:i'));
        $response->assertSee($request->clock_out->format('H:i'));
        $response->assertSeeText('シフト変更');


    }


}
