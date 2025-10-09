<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_register_user()
    {
        $response = $this->post('/register', [
            'name' => "堤 菜月",
            'email' => "test2@gmail.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $this->assertDatabaseHas(User::class, [
            'name' => "堤 菜月",
            'email' => "test2@gmail.com",
        ]);
    }

    public function test_register_user_validate_name()
    {
        $response = $this->post('/register', [
            'name' => "",
            'email' => "test2@gmail.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    public function test_register_user_validate_email()
    {
        $response = $this->post('/register', [
            'name' => "堤 菜月",
            'email' => "",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    public function test_register_user_validate_password_under8()
    {
        $response = $this->post('/register', [
            'name' => "堤 菜月",
            'email' => "test2@gmail.com",
            'password' => "passwor",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    public function test_register_user_validate_confirm_password()
    {
        $response = $this->post('/register', [
            'name' => "堤 菜月",
            'email' => "test2@gmail.com",
            'password' => "password",
            'password_confirmation' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

    public function test_register_user_validate_password()
    {
        $response = $this->post('/register', [
            'name' => "堤 菜月",
            'email' => "test2@gmail.com",
            'password' => "",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }
}
