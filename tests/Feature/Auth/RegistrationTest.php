<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        Notification::assertSentTo(User::where('email', 'test@example.com')->sole(), VerifyEmail::class);
        $this->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_registration_attempts_are_limited_per_ip(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10']);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/register', [])->assertUnprocessable();
        }

        $this->postJson('/register', [])->assertTooManyRequests();
        $this->assertDatabaseCount('users', 0);

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])
            ->postJson('/register', [])->assertUnprocessable();
    }
}
