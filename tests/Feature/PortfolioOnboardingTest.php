<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_login_and_creates_missing_portfolio_predictably(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Prepare seu portfólio')
            ->assertViewHas('completedSteps', 0)->assertSee(route('admin.projects.create'));
        $this->assertSame(1, $user->portfolio()->count());
        $this->get(route('dashboard'))->assertOk();
        $this->assertSame(1, $user->portfolio()->count());
    }

    public function test_progress_uses_only_current_owners_saved_presentation_projects_and_publication(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $other = app(EnsurePortfolio::class)->handle(User::factory()->create());
        $other->update(['headline' => 'Pronto', 'bio' => 'Pronto', 'is_published' => true]);
        $other->projects()->create(['title' => 'Projeto de outra conta']);
        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertViewHas('completedSteps', 0)
            ->assertDontSee('Projeto de outra conta');
        $portfolio->update(['headline' => 'Desenvolvedor', 'bio' => 'Minha apresentação']);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('completedSteps', 1);
        $project = $portfolio->projects()->create(['title' => 'Primeiro projeto']);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('completedSteps', 2);
        $portfolio->update(['is_published' => true]);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('completedSteps', 3)
            ->assertSee('Seu portfólio está pronto para compartilhar');
        $project->delete();
        $portfolio->update(['is_published' => false]);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('completedSteps', 1);
    }
}
