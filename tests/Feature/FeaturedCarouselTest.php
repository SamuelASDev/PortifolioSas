<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_carousel_is_disabled_by_default_even_with_featured_projects(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->projects()->create(['title' => 'Destaque', 'is_featured' => true]);

        $this->assertFalse($portfolio->fresh()->show_featured_carousel);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertDontSee('data-featured-carousel', false)->assertSee('Destaque');
    }

    public function test_enabled_carousel_uses_only_own_featured_projects_before_the_full_listing(): void
    {
        $portfolio = $this->portfolio(['show_featured_carousel' => true]);
        $later = $portfolio->projects()->create(['title' => 'Destaque posterior', 'is_featured' => true, 'display_order' => 9]);
        $first = $portfolio->projects()->create(['title' => 'Primeiro destaque', 'is_featured' => true, 'display_order' => 0, 'cover_image' => 'cover.png']);
        $portfolio->projects()->create(['title' => 'Sem destaque', 'is_featured' => false]);
        $other = $this->portfolio(['show_featured_carousel' => true]);
        $other->projects()->create(['title' => 'Projeto alheio', 'is_featured' => true]);

        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSeeInOrder(['data-featured-carousel', 'Primeiro destaque', 'id="projetos"'], false)
            ->assertSee($first->cover_url)->assertSee('data-carousel-toggle', false)->assertSee('Sem destaque')
            ->assertDontSee('Projeto alheio')
            ->assertViewHas('featuredProjects', fn ($projects) => $projects->modelKeys() === [$first->id, $later->id]);
    }

    public function test_enabled_carousel_without_featured_projects_is_hidden_and_one_slide_has_no_rotation_controls(): void
    {
        $portfolio = $this->portfolio(['show_featured_carousel' => true]);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()->assertDontSee('data-featured-carousel', false);
        $portfolio->projects()->create(['title' => 'Único destaque', 'is_featured' => true]);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()->assertSee('data-featured-carousel', false)
            ->assertDontSee('data-carousel-toggle', false);
    }

    public function test_featured_projects_are_not_limited_to_the_current_listing_page(): void
    {
        $portfolio = $this->portfolio(['show_featured_carousel' => true]);
        for ($i = 0; $i < 12; $i++) {
            $portfolio->projects()->create(['title' => 'Projeto '.$i, 'display_order' => $i]);
        }
        $last = $portfolio->projects()->create(['title' => 'Destaque da segunda página', 'display_order' => 99, 'is_featured' => true]);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertViewHas('featuredProjects', fn ($projects) => $projects->modelKeys() === [$last->id])
            ->assertViewHas('projects', fn ($projects) => $projects->count() === 12 && ! in_array($last->id, $projects->modelKeys(), true));
    }

    public function test_carousel_setting_can_be_enabled_and_disabled_only_for_the_current_owner(): void
    {
        $portfolio = $this->portfolio();
        $other = $this->portfolio();
        $data = ['slug' => $portfolio->slug, 'theme' => 'dark', 'primary_color' => '#123456', 'id' => $other->id, 'user_id' => $other->user_id];
        $this->actingAs($portfolio->user)->patch(route('admin.portfolio.update'), $data + ['show_featured_carousel' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($portfolio->fresh()->show_featured_carousel);
        $this->assertFalse($other->fresh()->show_featured_carousel);
        $this->get(route('admin.portfolio.edit'))->assertOk()->assertSee('Exibir carrossel de projetos em destaque');
        $this->patch(route('admin.portfolio.update'), $data + ['show_featured_carousel' => '0'])->assertSessionHasNoErrors();
        $this->assertFalse($portfolio->fresh()->show_featured_carousel);
        $this->patch(route('admin.portfolio.update'), $data + ['show_featured_carousel' => 'invalid'])->assertSessionHasErrors('show_featured_carousel');
    }

    public function test_gmail_compose_link_has_the_configured_recipient_and_no_private_account_email(): void
    {
        $portfolio = $this->portfolio(['contact_email' => 'dev+contato@example.com']);
        parse_str(parse_url($portfolio->gmail_compose_url, PHP_URL_QUERY), $query);
        $this->assertSame('dev+contato@example.com', $query['to']);
        $this->assertSame('cm', $query['view']);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()->assertSee($portfolio->gmail_compose_url)
            ->assertDontSee('mailto:', false)->assertDontSee($portfolio->user->email);
        $portfolio->update(['contact_email' => null]);
        $this->assertNull($portfolio->gmail_compose_url);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()->assertDontSee('mail.google.com');
    }

    private function portfolio(array $attributes = []): Portfolio
    {
        $portfolio = app(EnsurePortfolio::class)->handle(User::factory()->create());
        $portfolio->update(array_replace(['is_published' => true], $attributes));

        return $portfolio;
    }
}
