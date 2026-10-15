<?php

namespace Tests\Feature\Auth;

use App\Actions\EnsurePortfolio;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_email_blocks_the_panel_until_the_new_address_is_verified(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.update'), ['name' => $user->name, 'email' => 'changed@example.test'])
            ->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->get(route('admin.portfolio.edit'))->assertRedirect(route('verification.notice'));
        $this->get(route('verification.notice'))->assertOk()->assertSee('Corrigir o e-mail da conta');
    }

    public function test_unverified_accounts_cannot_access_or_mutate_the_administration(): void
    {
        $user = User::factory()->unverified()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($user);
        $this->actingAs($user);
        foreach (['dashboard', 'admin.portfolio.edit', 'admin.portfolio.preview', 'admin.projects.index', 'admin.projects.create', 'admin.projects.organize'] as $route) {
            $this->get(route($route))->assertRedirect(route('verification.notice'));
        }
        $this->patch(route('admin.portfolio.publication.update'), ['is_published' => true])
            ->assertRedirect(route('verification.notice'));
        $this->postJson(route('admin.project-media.video'), ['url' => 'https://youtu.be/dQw4w9WgXcQ'])->assertForbidden();
        $this->assertFalse($portfolio->fresh()->is_published);
        $this->get(route('profile.edit'))->assertOk();
        $user->markEmailAsVerified();
        $this->get(route('admin.projects.index'))->assertOk();
    }

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
