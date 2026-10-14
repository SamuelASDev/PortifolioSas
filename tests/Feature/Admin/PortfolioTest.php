<?php

namespace Tests\Feature\Admin;

use App\Actions\EnsurePortfolio;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_suite_uses_an_isolated_in_memory_database(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_registration_creates_a_portfolio(): void
    {
        $this->post('/register', [
            'name' => 'Maria Silva', 'email' => 'maria@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertRedirect('/dashboard');

        $user = User::where('email', 'maria@example.com')->firstOrFail();
        $this->assertNotNull($user->portfolio);
        $this->assertStringStartsWith('maria-silva-', $user->portfolio->slug);
        $this->assertSame('dark', $user->portfolio->theme);
        $this->assertSame('#6366f1', $user->portfolio->primary_color);
    }

    public function test_registration_is_rolled_back_if_portfolio_creation_fails(): void
    {
        $this->mock(EnsurePortfolio::class)->shouldReceive('handle')->once()->andThrow(new \RuntimeException('Creation failed'));

        $this->post('/register', [
            'name' => 'Maria', 'email' => 'maria@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
        ])->assertStatus(500);

        $this->assertDatabaseMissing('users', ['email' => 'maria@example.com']);
    }

    public function test_legacy_user_can_update_without_visiting_edit_first(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('admin.portfolio.update'), $this->data())
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('portfolios', ['user_id' => $user->id, 'slug' => 'meu-portfolio']);
        $this->get(route('admin.portfolio.edit'))->assertOk();
        $this->get(route('admin.portfolio.edit'))->assertOk();
        $this->assertSame(1, $user->portfolio()->count());
    }

    public function test_normalization_happens_before_uniqueness_validation(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $portfolio->update(['slug' => 'meu-portfolio']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('admin.portfolio.update'), $this->data(['slug' => 'Meu_Portfólio']))
            ->assertSessionHasErrors('slug');
        $this->assertSame('meu-portfolio', $portfolio->fresh()->slug);
    }

    public function test_own_slug_is_allowed_and_other_users_are_not_modified(): void
    {
        $user = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($user);
        $other = app(EnsurePortfolio::class)->handle(User::factory()->create());

        $this->actingAs($user)->patch(route('admin.portfolio.update'), $this->data([
            'slug' => $portfolio->slug, 'user_id' => $other->user_id, 'id' => $other->id,
        ]))->assertSessionHasNoErrors();

        $this->assertSame($user->id, $portfolio->fresh()->user_id);
        $this->assertSame($other->slug, $other->fresh()->slug);
    }

    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $input, string $field): void
    {
        $this->actingAs(User::factory()->create())
            ->patch(route('admin.portfolio.update'), $this->data($input))
            ->assertSessionHasErrors($field);
    }

    public static function invalidSettings(): array
    {
        return [
            'reserved normalized slug' => [['slug' => 'LOGIN'], 'slug'],
            'admin' => [['slug' => 'admin'], 'slug'],
            'health endpoint' => [['slug' => 'up'], 'slug'],
            'empty slug' => [['slug' => '!!!'], 'slug'],
            'array slug' => [['slug' => ['bad']], 'slug'],
            'invalid color' => [['primary_color' => 'red'], 'primary_color'],
            'short color' => [['primary_color' => '#fff'], 'primary_color'],
            'invalid hex' => [['primary_color' => '#gggggg'], 'primary_color'],
            'invalid theme' => [['theme' => 'custom'], 'theme'],
        ];
    }

    public function test_database_rejects_multiple_portfolios_per_user(): void
    {
        $user = User::factory()->create();
        app(EnsurePortfolio::class)->handle($user);
        $this->expectException(QueryException::class);
        $user->portfolio()->create(['slug' => 'outro-portfolio']);
    }

    public function test_generated_slug_does_not_conflict_with_a_custom_slug(): void
    {
        $first = User::factory()->create(['name' => 'Ana']);
        $second = User::factory()->create(['name' => 'Ana']);
        app(EnsurePortfolio::class)->handle($first)->update(['slug' => 'ana-'.$second->id]);

        $this->assertNotSame('ana-'.$second->id, app(EnsurePortfolio::class)->handle($second)->slug);
    }

    public function test_guests_cannot_access_portfolio_settings(): void
    {
        $this->get(route('admin.portfolio.edit'))->assertRedirect('/login');
        $this->patch(route('admin.portfolio.update'), $this->data())->assertRedirect('/login');
    }

    private function data(array $overrides = []): array
    {
        return array_replace(['slug' => 'Meu Portfólio', 'theme' => 'light', 'primary_color' => '#A1b2C3'], $overrides);
    }
}
