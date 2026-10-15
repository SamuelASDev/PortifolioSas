<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Models\Portfolio;
use App\Models\User;
use App\Support\PortfolioAppearance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioAppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_requires_login_and_drafts_remain_inaccessible_publicly(): void
    {
        $portfolio = $this->portfolio();
        $this->get(route('admin.portfolio.preview'))->assertRedirect(route('login'));
        $this->get(route('portfolio.show', $portfolio->slug))->assertNotFound();
        $this->actingAs($portfolio->user)->get(route('admin.portfolio.preview'))->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'")
            ->assertSee('noindex, nofollow')->assertSee('data-preview-schema', false);
        $this->assertFalse($portfolio->fresh()->is_published);
    }

    public function test_preview_always_uses_current_owner_and_scopes_project_details(): void
    {
        $owner = $this->portfolio();
        $other = $this->portfolio();
        $project = $owner->projects()->create(['title' => 'Meu projeto privado']);
        $foreign = $other->projects()->create(['title' => 'Projeto de outra conta']);
        $this->actingAs($owner->user)->get(route('admin.portfolio.preview', ['portfolio_id' => $other->id]))->assertOk()
            ->assertSee('Meu projeto privado')->assertDontSee('Projeto de outra conta')
            ->assertSee(route('admin.portfolio.preview.project', $project->id));
        $this->get(route('admin.portfolio.preview.project', $project->id))->assertOk()->assertSee('Meu projeto privado');
        $this->get(route('admin.portfolio.preview.project', $foreign->id))->assertNotFound();
        $this->get(route('portfolio.projects.show', [$owner->slug, $project->id]))->assertNotFound();
        $this->actingAs($other->user)->get(route('admin.portfolio.preview.project', $project->id))->assertNotFound();
    }

    public function test_preview_get_does_not_persist_query_parameters_and_keeps_pagination_private(): void
    {
        $portfolio = $this->portfolio();
        for ($i = 0; $i < 13; $i++) {
            $portfolio->projects()->create(['title' => 'Projeto '.$i]);
        }
        $before = $portfolio->fresh()->getAttributes();
        $this->actingAs($portfolio->user)->get(route('admin.portfolio.preview', ['theme' => 'light', 'primary_color' => '#abcdef']))->assertOk()
            ->assertSee(route('admin.portfolio.preview').'?page=2', false);
        $this->assertSame($before, $portfolio->fresh()->getAttributes());
    }

    public function test_appearance_save_is_validated_and_only_changes_own_appearance(): void
    {
        $portfolio = $this->portfolio();
        $other = $this->portfolio();
        $data = $this->settings() + ['user_id' => $other->user_id, 'id' => $other->id, 'is_published' => true, 'slug' => 'changed'];
        $this->actingAs($portfolio->user)->patchJson(route('admin.portfolio.appearance.update'), $data)->assertOk();
        $portfolio->refresh();
        $this->assertSame($this->settings()['appearance'], $portfolio->appearance);
        $this->assertSame('light', $portfolio->theme);
        $this->assertFalse($portfolio->is_published);
        $this->assertNotSame('changed', $portfolio->slug);
        $this->assertNull($other->fresh()->appearance);
        $this->assertSame('dark', $other->fresh()->theme);
        $this->get(route('portfolio.show', $portfolio->slug))->assertNotFound();
    }

    public function test_invalid_fonts_colors_sizes_or_unknown_properties_are_rejected_atomically(): void
    {
        $portfolio = $this->portfolio();
        $this->actingAs($portfolio->user);
        foreach ([
            ['theme', 'unknown'], ['primary_color', 'red; display:none'],
            ['appearance.font', 'url(https://example.com)'], ['appearance.base_size', 999],
            ['appearance.title_scale', 500], ['appearance.colors.page', 'red'],
            ['appearance.colors.unknown', '#ffffff'], ['appearance.custom_css', 'body{}'],
        ] as [$key, $value]) {
            $data = $this->settings();
            data_set($data, $key, $value);
            $response = $this->patchJson(route('admin.portfolio.appearance.update'), $data)->assertUnprocessable();
            $this->assertNotEmpty($response->json('errors'));
            $this->assertNull($portfolio->fresh()->appearance);
            $this->assertSame('dark', $portfolio->fresh()->theme);
        }
    }

    public function test_guests_cannot_save_and_published_settings_apply_to_both_public_pages(): void
    {
        $portfolio = $this->portfolio();
        $project = $portfolio->projects()->create(['title' => 'Meu projeto']);
        $this->patchJson(route('admin.portfolio.appearance.update'), $this->settings())->assertUnauthorized();
        $portfolio->update(['is_published' => true]);
        $this->actingAs($portfolio->user)->patchJson(route('admin.portfolio.appearance.update'), $this->settings())->assertOk();
        foreach ([route('portfolio.show', $portfolio->slug), route('portfolio.projects.show', [$portfolio->slug, $project->id])] as $url) {
            $this->get($url)->assertOk()->assertSee('--page: #eeddcc;', false)
                ->assertSee('--font: Georgia, Cambria, serif;', false)->assertSee('font-size: 18px', false)
                ->assertDontSee('data-preview-schema', false)->assertDontSee('noindex, nofollow');
        }
        $this->assertTrue($portfolio->fresh()->is_published);
    }

    public function test_existing_portfolios_keep_defaults_and_editor_is_available_in_panel(): void
    {
        $portfolio = $this->portfolio();
        $settings = app(PortfolioAppearance::class)->settings($portfolio);
        $this->assertSame('system', $settings['appearance']['font']);
        $this->assertSame(16, $settings['appearance']['base_size']);
        $this->actingAs($portfolio->user)->get(route('admin.portfolio.edit'))->assertOk()
            ->assertSee('data-appearance-editor', false)->assertSee('Personalizar aparência')
            ->assertSee('Salvar mantém seu portfólio em rascunho.');
        $this->get(route('admin.portfolio.preview'))->assertOk()->assertSee('--page: #101216;', false);
    }

    public function test_empty_color_overrides_restore_palette_and_main_form_preserves_customization(): void
    {
        $portfolio = $this->portfolio();
        $data = $this->settings();
        $data['appearance']['colors'] = [];
        $this->actingAs($portfolio->user)->patchJson(route('admin.portfolio.appearance.update'), $data)->assertOk();
        $this->patch(route('admin.portfolio.update'), ['slug' => $portfolio->slug, 'theme' => 'light', 'primary_color' => '#24634b', 'bio' => 'Nova bio'])
            ->assertSessionHasNoErrors();
        $this->assertSame($data['appearance'], $portfolio->fresh()->appearance);
        $this->get(route('admin.portfolio.preview'))->assertOk()->assertSee('--page: #fafaf8;', false);
    }

    public function test_layout_options_are_saved_rendered_and_preserved_by_legacy_updates(): void
    {
        $portfolio = $this->portfolio();
        $project = $portfolio->projects()->create(['title' => 'Layout project']);
        $data = $this->settings();
        foreach (config('portfolio-appearance.options') as $key => $option) {
            $data['appearance'][$key] = array_key_last($option['values']);
        }
        $this->actingAs($portfolio->user)->patchJson(route('admin.portfolio.appearance.update'), $data)->assertOk();
        foreach ([route('admin.portfolio.preview'), route('admin.portfolio.preview.project', $project->id)] as $url) {
            $response = $this->get($url)->assertOk();
            foreach (config('portfolio-appearance.options') as $key => $option) {
                $response->assertSee('data-'.$key.'="'.array_key_last($option['values']).'"', false);
            }
        }
        $this->patchJson(route('admin.portfolio.appearance.update'), $this->settings())->assertOk();
        $this->assertSame('list', $portfolio->fresh()->appearance['grid']);
        foreach (array_keys(config('portfolio-appearance.options')) as $key) {
            $invalid = $this->settings();
            $invalid['appearance'][$key] = 'invalid-value';
            $this->patchJson(route('admin.portfolio.appearance.update'), $invalid)->assertUnprocessable();
            $this->assertSame('list', $portfolio->fresh()->appearance['grid']);
        }
    }

    private function portfolio(): Portfolio
    {
        return app(EnsurePortfolio::class)->handle(User::factory()->create());
    }

    private function settings(): array
    {
        return ['theme' => 'light', 'primary_color' => '#24634b', 'appearance' => [
            'font' => 'serif', 'base_size' => 18, 'title_scale' => 110,
            'colors' => ['page' => '#eeddcc', 'ink' => '#112233'],
        ]];
    }
}
