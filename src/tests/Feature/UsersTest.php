<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UsersTest extends TestCase
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


    // ユーザー情報取得機能
    public function test_all_users_list_page_name_and_mail_check()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $users = User::factory()->count(3)->create([
            'role' => 'users',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);

        foreach($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_all_users_list_data_get_function()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'users',
        ]);

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $month = CarbonPeriod::create($start, $end);

        $attendances = collect();
        foreach($month as $date) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0),
                'clock_out' => $date->copy()->setTime(18, 0),
                'status' => 'clock_out'
            ]);

            $attendance->breaks()->create([
                'break_start' => $date->copy()->setTime(12,0),
                'break_end' => $date->copy()->setTime(13,0),
            ]);

            $attendance->push($attendance);
        }

        $breakMinutes = 60;
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);
        $workMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;

        $breakFormatted = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        $totalFormatted = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

        $response = $this->actingAs($admin)->get(route('users.attendance', [
            'user' => $user->id,
        ]));
        $response->assertStatus(200);

        $response->assertSeeText($user->name);

        foreach($attendances as $attendance) {
            $response->assertSeeText(Carbon::parse($attendance->date)->format('m/d'));
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($breakFormatted);
            $response->assertSeeText($totalFormatted);
        }
    }

    public function test_all_users_list_page_previous_month_get()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'role' => 'users',
        ]);

        $start = now()->subMonth()->startOfMonth();
        $end = now()->subMonth()->endOfMonth();
        $month = CarbonPeriod::create($start, $end);

        $attendances = collect();
        foreach($month as $date) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0),
                'clock_out' => $date->copy()->setTime(18, 0),
                'status' => 'clock_out',
            ]);

            $attendance->breaks()->create([
                'break_start' => $date->copy()->setTime(12, 0),
                'break_end' => $date->copy()->setTime(13, 0),
            ]);
        }

        $monthPrevious = $start->format('Y-m');
        $response = $this->actingAs($admin)->get(
            route('users.attendance', ['user' => $user->id, 'month' => $monthPrevious])
        );
        $response->assertStatus(200);

        $breakMinutes = 60;
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);

        $workMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;

        $breakFormatted = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        $totalFormatted = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

        $response = $this->actingAs($admin)->get(route('users.attendance',
            ['user' => $user->id,
            'month' => $monthPrevious
        ]));
        $response->assertStatus(200);

        foreach($attendances as $attendance) {
            $response->assertSeeText(Carbon::parse($attendance->date)->format('m/d'));
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($breakFormatted);
            $response->assertSeeText($totalFormatted);
        }
    }

    public function test_all_users_list_page_next_month_get()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'role' => 'users',
        ]);

        $start = now()->addMonth()->startOfMonth();
        $end = now()->addMonth()->endOfMonth();
        $month = CarbonPeriod::create($start, $end);

        $attendances = collect();
        foreach($month as $date) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9,0),
                'clock_out' => $date->copy()->setTime(18,0),
                'status' => 'clock_out',
            ]);

            $attendance->breaks()->create([
                'break_start' => $date->copy()->setTime(12,0),
                'break_end' => $date->copy()->setTime(13,0),
            ]);
            $attendances->push($attendance);
        }

        $monthNext = $start->format('Y-m');
        $response = $this->actingAs($admin)->get(
            route('users.attendance', ['user' => $user->id,'month'=>$monthNext])
        );
        $response->assertStatus(200);

        $breakMinutes = 60;
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);

        $workMinutes = $clockOut->diffInMinutes($clockIn) - $breakMinutes;

        $breakFormatted = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
        $totalFormatted = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

        $response =$this->actingAs($admin)->get(route('users.attendance',
            ['user' => $user->id,
            'month' => $monthNext
        ]));
        $response->assertStatus(200);

        foreach($attendances as $attendance) {
            $response->assertSeeText(Carbon::parse($attendance->date)->format('m/d'));
            $response->assertSeeText(Carbon::parse($attendance->clock_in)->format('H:i'));
            $response->assertSeeText(Carbon::parse($attendance->clock_out)->format('H:i'));
            $response->assertSeeText($breakFormatted);
            $response->assertSeeText($totalFormatted);
        }
    }

    public function test_all_users__list_page_detail_page_redirect_check()
    {
        $admin = User::factory()->create([
            'role' =>  'admin',
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create(['role' => 'users']);

        $start = now();
        $end = now();
        $month = CarbonPeriod::create($start,$end);

        $attendances = collect();
        foreach($month as $date) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0),
                'clock_out' => $date->copy()->setTime(18, 0),
                'status' => 'clock_out'
            ]);

            $attendance->push($attendance);
        }

        $response = $this->actingAs($admin)->get('/admin/attendances/', ['attendance' => $attendance->id]);
        $response->assertStatus(200);

        $response->assertSee('詳細');

        $detailUrl = route('admin.detail', $attendance->id);
        $response = $this->actingAs($admin)->get($detailUrl);

        $response->assertStatus(200);

        $response->assertSeeText(Carbon::parse($attendance->date)->format('Y年'));
        $response->assertSeeText(Carbon::parse($attendance->date)->format('n月j日'));
    }


}
