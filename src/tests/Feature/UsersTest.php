<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

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
    public function test_all_users_list_check()
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
}
