<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Justin',
            'email' => 'justSMBOelot@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonStructure(['user', 'token']);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create(['email' => 'justSMBOelot@gmail.com']);

        $this->postJson('/api/login', ['email' => 'justSMBOelot@gmail.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'justSMBOelot@gmail.com']);

        $this->postJson('/api/login', ['email' => 'justSMBOelot@gmail.com', 'password' => 'salah'])
            ->assertUnauthorized();
    }

    public function test_guest_gets_401_from_browser(): void
    {
        $this->get('/api/books')->assertUnauthorized();
    }
}
