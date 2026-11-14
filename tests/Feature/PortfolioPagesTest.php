<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Actions\SaveProject;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_and_contact_pages_use_the_saved_content_and_respect_drafts(): void
    {
        $portfolio = $this->portfolio();
        $this->get(route('portfolio.about', $portfolio->slug))->assertNotFound();
        $this->actingAs($portfolio->user)->patch(route('admin.portfolio.update'), [
            'slug' => $portfolio->slug, 'theme' => 'dark', 'primary_color' => '#6366f1', 'professional_name' => 'Ana Dev',
            'about' => "Comecei com jogos.\n\nHoje <b>crio</b> sistemas.", 'contact_intro' => 'Aberto a freelas.',
            'whatsapp' => '+55 (11) 91234-5678', 'instagram_url' => 'https://instagram.com/ana', 'location' => 'Recife, PE',
        ])->assertSessionHasNoErrors();
        $portfolio->refresh()->update(['is_published' => true]);

        $this->get(route('portfolio.about', $portfolio->slug))->assertOk()
            ->assertSee('Comecei com jogos.')->assertSee('&lt;b&gt;crio&lt;/b&gt;', false)->assertSee('Recife, PE')
            ->assertSee('aria-current="page"', false);
        $this->get(route('portfolio.contact', $portfolio->slug))->assertOk()
            ->assertSee('Aberto a freelas.')->assertSee('https://wa.me/5511912345678', false)->assertSee('instagram.com/ana');
        $this->get(route('admin.portfolio.preview.about'))->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get(route('admin.portfolio.preview.contact'))->assertOk();
    }

    public function test_invalid_whatsapp_is_rejected(): void
    {
        $portfolio = $this->portfolio();
        $this->actingAs($portfolio->user)->patch(route('admin.portfolio.update'), [
            'slug' => $portfolio->slug, 'theme' => 'dark', 'primary_color' => '#6366f1', 'whatsapp' => 'me chama',
        ])->assertSessionHasErrors('whatsapp');
    }

    public function test_project_status_and_brand_icons_are_shown(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->update(['is_published' => true]);
        $this->actingAs($portfolio->user)->post(route('admin.projects.store'), [
            'title' => 'Jogo', 'display_order' => 0, 'is_featured' => false, 'category' => 'game-web', 'status' => 'in_development', 'tags' => 'Godot, Node.js',
        ])->assertSessionHasNoErrors();
        $this->post(route('admin.projects.store'), ['title' => 'X', 'display_order' => 0, 'is_featured' => false, 'status' => 'quase'])->assertSessionHasErrors('status');

        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSee('Em desenvolvimento')->assertSee('Jogo web')->assertSee('pf-icon-brand', false);
    }

    private function portfolio(): Portfolio
    {
        return app(EnsurePortfolio::class)->handle(User::factory()->create());
    }
}
