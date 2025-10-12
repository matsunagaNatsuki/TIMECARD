// 退勤機能
    


    public function test_stamp_page_clock_out_function_clock_btn()
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

        $payload = [
            'action' => 'clock_out',
        ];

        $this->followingRedirects()
            ->actingAs($user)
            ->post(route('attendance.create'), $payload)
            ->assertok();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'clock_out,'
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }




