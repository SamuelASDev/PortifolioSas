<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPresetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_preset_can_be_saved_and_rendered_in_both_public_pages(): void
    {
        $portfolio = app(EnsurePortfolio::class)->handle(User::factory()->create());
        $portfolio->update(['is_published' => true]);
        $project = $portfolio->projects()->create(['title' => 'Projeto de teste']);
        $this->actingAs($portfolio->user);
        foreach (config('portfolio-appearance.presets') as $key => $preset) {
            $appearance = [
                ...array_map(fn ($option) => $option['default'], config('portfolio-appearance.options')),
                ...($preset['appearance'] ?? []),
                'font' => $preset['font'], 'base_size' => $preset['base_size'] ?? 16,
                'title_scale' => $preset['title_scale'] ?? 100, 'colors' => $preset['colors'],
            ];
            $this->patchJson(route('admin.portfolio.appearance.update'), [
                'theme' => $preset['theme'], 'primary_color' => $preset['primary_color'], 'appearance' => $appearance,
            ])->assertOk();
            foreach ([route('portfolio.show', $portfolio->slug), route('portfolio.projects.show', [$portfolio->slug, $project->id])] as $url) {
                $this->get($url)->assertOk()->assertSee('--accent: '.$preset['primary_color'].';', false)
                    ->assertSee('data-hero="'.$appearance['hero'].'"', false)
                    ->assertSee('data-grid="'.$appearance['grid'].'"', false);
            }
            $this->assertTrue($portfolio->fresh()->is_published, $key);
        }
    }

    public function test_presets_have_readable_text_on_every_configurable_surface(): void
    {
        foreach (config('portfolio-appearance.presets') as $key => $preset) {
            $colors = array_replace(config('portfolio-appearance.palettes.'.$preset['theme']), $preset['colors']);
            foreach (['page', 'surface', 'card', 'header', 'footer'] as $background) {
                foreach (['ink', 'title', 'muted'] as $foreground) {
                    $a = $this->luminance($colors[$foreground]);
                    $b = $this->luminance($colors[$background]);
                    $ratio = (max($a, $b) + .05) / (min($a, $b) + .05);
                    $this->assertGreaterThanOrEqual(4.5, $ratio, $key.': '.$foreground.' on '.$background);
                }
            }
        }
    }

    private function luminance(string $color): float
    {
        $channels = array_map(function ($channel) {
            $value = hexdec($channel) / 255;

            return $value <= .04045 ? $value / 12.92 : (($value + .055) / 1.055) ** 2.4;
        }, str_split(substr($color, 1), 2));

        return $channels[0] * .2126 + $channels[1] * .7152 + $channels[2] * .0722;
    }
}
