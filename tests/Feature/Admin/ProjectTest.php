<?php

namespace Tests\Feature\Admin;

use App\Actions\EnsurePortfolio;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_edit_update_and_delete_a_project(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.projects.create'))->assertOk();
        $this->post(route('admin.projects.store'), $this->data())->assertRedirect(route('admin.projects.index'));
        $project = $user->portfolio->projects()->sole();
        $this->assertTrue($project->is_featured);
        $this->assertSame(2, $project->display_order);
        $this->get(route('admin.projects.edit', $project))->assertOk()->assertSee('Meu projeto');
        $this->put(route('admin.projects.update', $project), $this->data([
            'title' => 'Atualizado', 'is_featured' => '0', 'display_order' => 1,
        ]))->assertRedirect(route('admin.projects.index'));
        $this->assertSame('Atualizado', $project->fresh()->title);
        $this->assertFalse($project->fresh()->is_featured);
        $this->assertSame(1, $project->fresh()->display_order);
        $this->delete(route('admin.projects.destroy', $project))->assertRedirect(route('admin.projects.index'));
        $this->assertModelMissing($project);
    }

    public function test_listing_is_scoped_paginated_and_ordered_with_stable_ties(): void
    {
        $user = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($user);
        $ids = [];
        for ($i = 0; $i < 12; $i++) {
            $ids[] = $portfolio->projects()->create($this->data(['title' => 'Projeto '.$i, 'display_order' => intdiv($i, 2)]))->id;
        }
        $other = $this->projectFor(User::factory()->create(), ['title' => 'Projeto privado alheio']);

        $this->actingAs($user)->get(route('admin.projects.index'))->assertOk()
            ->assertDontSee($other->title)
            ->assertViewHas('projects', fn ($projects) => $projects->total() === 12 && $projects->modelKeys() === array_slice($ids, 0, 10));
        $this->get(route('admin.projects.index', ['page' => 2]))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === array_slice($ids, 10));
    }

    public function test_user_cannot_edit_update_or_delete_another_users_project(): void
    {
        $project = $this->projectFor(User::factory()->create());
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.projects.edit', $project))->assertForbidden();
        $this->put(route('admin.projects.update', $project), $this->data(['title' => 'Invadido']))->assertForbidden();
        $this->delete(route('admin.projects.destroy', $project))->assertForbidden();
        $this->assertSame('Meu projeto', $project->fresh()->title);
        $this->assertModelExists($project);
    }

    public function test_client_cannot_choose_or_change_project_owner_or_cover(): void
    {
        $other = app(EnsurePortfolio::class)->handle(User::factory()->create());
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.projects.store'), $this->data([
            'portfolio_id' => $other->id, 'cover_image' => '/injected.png',
        ]))->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $this->assertSame($user->portfolio->id, $project->portfolio_id);
        $this->assertNull($project->cover_image);
        $this->put(route('admin.projects.update', $project), $this->data([
            'portfolio_id' => $other->id, 'cover_image' => '/injected.png',
        ]))->assertSessionHasNoErrors();
        $this->assertSame($user->portfolio->id, $project->fresh()->portfolio_id);
        $this->assertNull($project->fresh()->cover_image);
        $this->assertSame(0, $other->projects()->count());
    }

    public function test_description_is_rendered_as_escaped_plain_text(): void
    {
        $user = User::factory()->create();
        $this->projectFor($user, ['description' => '<script>alert("xss")</script>']);

        $this->actingAs($user)->get(route('admin.projects.index'))->assertOk()
            ->assertSee('<script>alert("xss")</script>')
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    #[DataProvider('invalidProjects')]
    public function test_invalid_project_data_is_rejected_on_create_and_update(array $input, string $field): void
    {
        $user = User::factory()->create();
        $project = $this->projectFor($user);
        $this->actingAs($user)->post(route('admin.projects.store'), $this->data($input))->assertSessionHasErrors($field);
        $this->put(route('admin.projects.update', $project), $this->data($input))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('projects', 1);
        $this->assertSame('Meu projeto', $project->fresh()->title);
    }

    public static function invalidProjects(): array
    {
        return [
            'empty title' => [['title' => ''], 'title'],
            'long title' => [['title' => str_repeat('a', 256)], 'title'],
            'invalid demo protocol' => [['link_demo' => 'javascript:alert(1)'], 'link_demo'],
            'invalid repo protocol' => [['link_github' => 'ftp://example.com'], 'link_github'],
            'negative order' => [['display_order' => -1], 'display_order'],
            'fractional order' => [['display_order' => 1.5], 'display_order'],
            'overflow order' => [['display_order' => 2147483648], 'display_order'],
            'invalid highlight' => [['is_featured' => 'yes'], 'is_featured'],
            'long description' => [['description' => str_repeat('a', 50001)], 'description'],
        ];
    }

    public function test_guests_cannot_access_any_project_action(): void
    {
        $project = $this->projectFor(User::factory()->create());
        $this->get(route('admin.projects.index'))->assertRedirect('/login');
        $this->get(route('admin.projects.create'))->assertRedirect('/login');
        $this->get(route('admin.projects.edit', $project))->assertRedirect('/login');
        $this->post(route('admin.projects.store'), $this->data())->assertRedirect('/login');
        $this->put(route('admin.projects.update', $project), $this->data())->assertRedirect('/login');
        $this->delete(route('admin.projects.destroy', $project))->assertRedirect('/login');
    }

    public function test_missing_projects_return_404_and_empty_listing_is_available(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.projects.index'))->assertOk()->assertSee('Você ainda não cadastrou projetos.');
        $this->get(route('admin.projects.edit', 999))->assertNotFound();
        $this->put(route('admin.projects.update', 999), $this->data())->assertNotFound();
        $this->delete(route('admin.projects.destroy', 999))->assertNotFound();
    }

    private function projectFor(User $user, array $overrides = []): Project
    {
        return app(EnsurePortfolio::class)->handle($user)->projects()->create($this->data($overrides));
    }

    private function data(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Meu projeto', 'description' => "Descrição simples.\nSegunda linha.",
            'link_demo' => 'https://example.com', 'link_github' => 'https://github.com/example/repo',
            'is_featured' => '1', 'display_order' => 2,
        ], $overrides);
    }
}
