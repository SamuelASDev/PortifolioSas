<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Models\Portfolio;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_portfolios_start_as_drafts(): void
    {
        $user = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($user);

        $this->assertFalse($portfolio->is_published);
        $this->assertSame($user->name, $portfolio->professional_name);
        $this->get(route('portfolio.show', $portfolio->slug))->assertNotFound();
    }

    public function test_drafts_and_their_projects_are_inaccessible_even_to_the_owner(): void
    {
        $portfolio = $this->portfolio(['is_published' => false]);
        $project = $this->project($portfolio);
        $this->get(route('portfolio.show', $portfolio->slug))->assertNotFound();
        $this->get($this->projectUrl($portfolio, $project))->assertNotFound();
        $this->actingAs($portfolio->user);
        $this->get(route('portfolio.show', $portfolio->slug))->assertNotFound();
        $this->get($this->projectUrl($portfolio, $project))->assertNotFound();
    }

    public function test_unknown_slugs_and_project_ids_return_404(): void
    {
        $portfolio = $this->portfolio();
        $this->get('/slug-inexistente')->assertNotFound();
        $this->get('/slug-inexistente/projects/1')->assertNotFound();
        $this->get(route('portfolio.projects.show', [$portfolio->slug, 999]))->assertNotFound();
        $this->get('/'.$portfolio->slug.'/projects/nao-e-numero')->assertNotFound();
    }

    public function test_published_page_displays_presentation_and_only_explicit_public_contacts(): void
    {
        $portfolio = $this->portfolio([
            'professional_name' => 'Nome profissional', 'headline' => 'Desenvolvedor Laravel',
            'bio' => "Minha apresentação.\nSegunda linha.", 'contact_email' => 'publico@example.com',
            'github_url' => 'https://github.com/example', 'linkedin_url' => 'https://linkedin.com/in/example',
            'website_url' => 'https://example.com', 'photo_path' => 'portfolios/1/photos/photo.png',
        ]);

        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSee('Nome profissional')->assertSee('Desenvolvedor Laravel')->assertSee('Minha apresentação.')
            ->assertSee('mailto:publico@example.com')->assertSee('https://github.com/example')
            ->assertSee('https://linkedin.com/in/example')->assertSee('https://example.com')
            ->assertSee($portfolio->photo_url)
            ->assertDontSee($portfolio->user->email)->assertDontSee('Nome privado da conta')
            ->assertSee('name="viewport"', false)
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    #[DataProvider('appearances')]
    public function test_theme_and_accent_are_rendered_independently_of_system_theme(string $theme, string $color, string $foreground): void
    {
        $portfolio = $this->portfolio(['theme' => $theme, 'primary_color' => $color]);

        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSee('data-theme="'.$theme.'"', false)
            ->assertSee('--accent: '.$color.'; --accent-ink: '.$foreground.';', false);
    }

    public static function appearances(): array
    {
        return [['light', '#ffffff', '#000000'], ['dark', '#000000', '#ffffff']];
    }

    public function test_invalid_legacy_color_cannot_inject_css(): void
    {
        $portfolio = $this->portfolio(['primary_color' => 'red; background: url(https://evil.example)']);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSee('--accent: #6366f1;', false)->assertDontSee('evil.example');
    }

    public function test_projects_are_ordered_stably_and_other_accounts_are_excluded(): void
    {
        $portfolio = $this->portfolio();
        $last = $this->project($portfolio, ['title' => 'Último projeto', 'display_order' => 9]);
        $first = $this->project($portfolio, ['title' => 'Primeiro projeto', 'display_order' => 0, 'is_featured' => true]);
        $second = $this->project($portfolio, ['title' => 'Segundo projeto', 'display_order' => 0]);
        $other = $this->project($this->portfolio(), ['title' => 'Projeto de outra conta']);

        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSeeInOrder([$first->title, $second->title, $last->title])
            ->assertSee('Em destaque')->assertDontSee($other->title)
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$first->id, $second->id, $last->id]);
    }

    public function test_public_projects_are_paginated_twelve_per_page(): void
    {
        $portfolio = $this->portfolio();
        $ids = [];
        for ($i = 0; $i < 13; $i++) {
            $ids[] = $this->project($portfolio, ['title' => 'Projeto '.$i])->id;
        }

        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->total() === 13 && $projects->modelKeys() === array_slice($ids, 0, 12));
        $this->get(route('portfolio.show', ['slug' => $portfolio->slug, 'page' => 2]))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === array_slice($ids, 12));
    }

    public function test_published_portfolio_without_projects_has_an_empty_state(): void
    {
        $portfolio = $this->portfolio();
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSee('Novos projetos serão apresentados aqui em breve.');
    }

    public function test_project_page_displays_full_description_cover_and_links(): void
    {
        $portfolio = $this->portfolio();
        $description = str_repeat('Descrição completa. ', 30).'Fim da descrição.';
        $project = $this->project($portfolio, [
            'description' => $description, 'cover_image' => 'portfolios/'.$portfolio->id.'/covers/cover.png',
            'link_demo' => 'https://demo.example.com', 'link_github' => 'https://github.com/example/repo',
            'is_featured' => true,
        ]);

        $this->get($this->projectUrl($portfolio, $project))->assertOk()
            ->assertSee($project->title)->assertSee($description)->assertSee($project->cover_url)
            ->assertSee('https://demo.example.com')->assertSee('https://github.com/example/repo')
            ->assertSee('rel="noopener noreferrer"', false)->assertSee('Em destaque')
            ->assertSee(route('portfolio.show', $portfolio->slug).'#projetos');
    }

    public function test_project_cannot_be_accessed_through_another_portfolio_slug(): void
    {
        $portfolio = $this->portfolio();
        $other = $this->portfolio();
        $project = $this->project($portfolio);

        $this->get($this->projectUrl($portfolio, $project))->assertOk();
        $this->get($this->projectUrl($other, $project))->assertNotFound();
    }

    public function test_public_content_is_escaped_and_unsafe_legacy_links_are_not_rendered(): void
    {
        $payload = '<script>alert("xss")</script>';
        $portfolio = $this->portfolio(['professional_name' => $payload, 'bio' => $payload, 'github_url' => 'javascript:alert(1)']);
        $project = $this->project($portfolio, ['title' => $payload, 'description' => $payload, 'link_demo' => 'javascript:alert(2)']);

        foreach ([route('portfolio.show', $portfolio->slug), $this->projectUrl($portfolio, $project)] as $url) {
            $this->get($url)->assertOk()->assertSee($payload)->assertDontSee($payload, false)
                ->assertDontSee('href="javascript:', false);
        }
    }

    public function test_unpublishing_removes_both_public_pages_and_republishing_restores_them(): void
    {
        $portfolio = $this->portfolio();
        $project = $this->project($portfolio);
        $this->actingAs($portfolio->user);

        $this->patch(route('admin.portfolio.publication.update'), ['is_published' => '0'])->assertSessionHasNoErrors();
        $this->get(route('portfolio.show', $portfolio->slug))->assertNotFound();
        $this->get($this->projectUrl($portfolio, $project))->assertNotFound();
        $this->patch(route('admin.portfolio.publication.update'), ['is_published' => '1'])->assertSessionHasNoErrors();
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk();
        $this->get($this->projectUrl($portfolio, $project))->assertOk();
    }

    public function test_changing_slug_invalidates_both_old_public_urls(): void
    {
        $portfolio = $this->portfolio();
        $project = $this->project($portfolio);
        $oldSlug = $portfolio->slug;
        $this->actingAs($portfolio->user)->patch(route('admin.portfolio.update'), [
            'slug' => 'novo-endereco', 'theme' => 'light', 'primary_color' => '#123456',
        ])->assertSessionHasNoErrors();

        $this->get(route('portfolio.show', $oldSlug))->assertNotFound();
        $this->get(route('portfolio.projects.show', [$oldSlug, $project->id]))->assertNotFound();
        $this->get(route('portfolio.show', 'novo-endereco'))->assertOk();
        $this->get(route('portfolio.projects.show', ['novo-endereco', $project->id]))->assertOk();
    }

    private function portfolio(array $attributes = []): Portfolio
    {
        $owner = User::factory()->create(['name' => 'Nome privado da conta']);
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $portfolio->update(array_replace(['professional_name' => 'Profissional público', 'is_published' => true], $attributes));

        return $portfolio;
    }

    private function project(Portfolio $portfolio, array $attributes = []): Project
    {
        return $portfolio->projects()->create(array_replace(['title' => 'Meu projeto', 'display_order' => 0], $attributes));
    }

    private function projectUrl(Portfolio $portfolio, Project $project): string
    {
        return route('portfolio.projects.show', [$portfolio->slug, $project->id]);
    }
}
