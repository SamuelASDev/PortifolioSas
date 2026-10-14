<?php

namespace Tests\Feature\Admin;

use App\Actions\EnsurePortfolio;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRichDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_update_sanitize_html_on_the_server(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('admin.projects.store'), $this->data([
            'description' => '<h2>O problema</h2><p onclick="alert(1)"><strong>Solução</strong></p><script>alert(1)</script>',
        ]))->assertSessionHasNoErrors();
        $project = Project::query()->sole();

        $this->assertSame('html', $project->description_format);
        $this->assertStringContainsString('<h2>O problema</h2>', $project->description);
        $this->assertStringContainsString('<strong>Solução</strong>', $project->description);
        $this->assertStringNotContainsString('onclick', $project->description);
        $this->assertStringNotContainsString('<script', $project->description);

        $this->put(route('admin.projects.update', $project), $this->data([
            'description' => '<ul><li>Resultado</li></ul><a href="javascript:alert(1)">Link</a>',
        ]))->assertSessionHasNoErrors();
        $this->assertStringContainsString('<ul><li>Resultado</li></ul>', $project->fresh()->description);
        $this->assertStringNotContainsString('javascript:', $project->fresh()->description);
    }

    public function test_legacy_plain_text_is_preserved_and_has_a_safe_editor_initial_value(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $text = "Código literal: <strong>sem formatação</strong>\nOutra linha.";
        $project = $portfolio->projects()->create(['title' => 'Antigo', 'description' => $text]);

        $this->assertSame('text', $project->fresh()->description_format);
        $this->actingAs($owner)->get(route('admin.projects.edit', $project))->assertOk()
            ->assertSee('data-initial-format="text"', false)->assertSee($text);
        $data = $this->data(['description' => $text]);
        unset($data['description_format']);
        $this->put(route('admin.projects.update', $project), $data)->assertSessionHasNoErrors();
        $this->assertSame($text, $project->fresh()->description);
        $this->assertSame('text', $project->fresh()->description_format);
    }

    public function test_public_html_is_sanitized_again_even_if_database_contents_are_untrusted(): void
    {
        $portfolio = app(EnsurePortfolio::class)->handle(User::factory()->create());
        $portfolio->update(['is_published' => true]);
        $project = $portfolio->projects()->create([
            'title' => 'Projeto seguro', 'description_format' => 'html',
            'description' => '<h2>Detalhes</h2><p><strong>Seguro</strong></p><img src=x onerror=alert(1)><script>dangerous()</script>',
        ]);

        $this->get(route('portfolio.projects.show', [$portfolio->slug, $project->id]))->assertOk()
            ->assertSee('<h2>Detalhes</h2>', false)->assertSee('<strong>Seguro</strong>', false)
            ->assertDontSee('onerror', false)->assertDontSee('dangerous()', false);
    }

    public function test_admin_and_public_summaries_and_metadata_do_not_show_html_markup(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $portfolio->update(['is_published' => true]);
        $project = $portfolio->projects()->create([
            'title' => 'Exemplo', 'description_format' => 'html',
            'description' => '<h2>Problema</h2><p>Uma <strong>solução</strong> &amp; resultado.</p>',
        ]);

        $this->actingAs($owner)->get(route('admin.projects.index'))->assertOk()
            ->assertSee('Problema Uma solução & resultado.')->assertDontSee('&lt;strong&gt;', false);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSee('Problema Uma solução & resultado.')->assertDontSee('&lt;strong&gt;', false);
        $this->get(route('portfolio.projects.show', [$portfolio->slug, $project->id]))->assertOk()
            ->assertSee('name="description" content="Problema Uma solução &amp; resultado."', false);
    }

    public function test_old_input_is_preserved_but_sanitized_before_editor_loading(): void
    {
        $this->actingAs(User::factory()->create())->from(route('admin.projects.create'))
            ->post(route('admin.projects.store'), $this->data([
                'title' => '', 'description' => '<h2>Texto que não pode sumir</h2><script>dangerous()</script>',
            ]))->assertSessionHasErrors('title');

        $this->get(route('admin.projects.create'))->assertOk()
            ->assertSee('&lt;h2&gt;Texto que não pode sumir&lt;/h2&gt;', false)
            ->assertSee('data-initial-format="html"', false)->assertDontSee('dangerous()', false);
    }

    public function test_empty_rich_description_is_stored_as_null(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.projects.store'), $this->data([
            'description' => '<p></p><p><br></p>',
        ]))->assertSessionHasNoErrors();

        $this->assertNull(Project::query()->sole()->description);
    }

    public function test_invalid_format_or_format_without_description_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.projects.store'), $this->data([
            'description_format' => 'markdown',
        ]))->assertSessionHasErrors('description_format');
        $data = $this->data();
        unset($data['description']);
        $this->post(route('admin.projects.store'), $data)->assertSessionHasErrors('description');
    }

    public function test_rich_content_cannot_be_written_to_another_accounts_project(): void
    {
        $portfolio = app(EnsurePortfolio::class)->handle(User::factory()->create());
        $project = $portfolio->projects()->create(['title' => 'Alheio', 'description' => 'Intacto']);

        $this->actingAs(User::factory()->create())->put(route('admin.projects.update', $project), $this->data())
            ->assertForbidden();
        $this->assertSame('Intacto', $project->fresh()->description);
        $this->assertSame('text', $project->fresh()->description_format);
    }

    private function data(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Projeto', 'description' => '<p>Descrição <em>formatada</em>.</p>',
            'description_format' => 'html', 'display_order' => 0, 'is_featured' => '0',
        ], $overrides);
    }
}
