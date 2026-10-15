<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Models\Portfolio;
use App\Models\User;
use App\Support\ProjectOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_requires_login_and_includes_all_own_projects_across_pages(): void
    {
        $owner = $this->portfolio();
        $other = $this->portfolio();
        $other->projects()->create(['title' => 'Projeto de outra conta']);
        for ($i = 0; $i < 13; $i++) {
            $owner->projects()->create(['title' => 'Projeto '.$i, 'display_order' => $i]);
        }
        $this->get(route('admin.projects.organize'))->assertRedirect(route('login'));
        $this->actingAs($owner->user)->get(route('admin.projects.organize'))->assertOk()->assertSee('Projeto 12')
            ->assertDontSee('Projeto de outra conta')->assertViewHas('projects', fn ($projects) => $projects->count() === 13);
    }

    public function test_save_reorders_and_sets_features_for_both_public_grid_and_carousel(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->update(['is_published' => true, 'show_featured_carousel' => true]);
        $first = $portfolio->projects()->create(['title' => 'Primeiro', 'display_order' => 8, 'is_featured' => true]);
        $second = $portfolio->projects()->create(['title' => 'Segundo', 'display_order' => 12]);
        $third = $portfolio->projects()->create(['title' => 'Terceiro', 'display_order' => 12]);
        $revision = $this->revision($portfolio);
        $this->actingAs($portfolio->user)->patchJson(route('admin.projects.organize.update'), ['revision' => $revision, 'projects' => [
            ['id' => $third->id, 'is_featured' => true], ['id' => $first->id, 'is_featured' => false], ['id' => $second->id, 'is_featured' => true],
        ]])->assertOk()->assertJsonPath('revision', $this->revision($portfolio))->assertJsonMissing(['revision' => $revision]);
        $this->assertSame([0, 1, 2], $portfolio->projects()->orderBy('display_order')->pluck('display_order')->all());
        $this->assertFalse($first->fresh()->is_featured);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$third->id, $first->id, $second->id])
            ->assertViewHas('featuredProjects', fn ($projects) => $projects->modelKeys() === [$third->id, $second->id]);
    }

    public function test_foreign_duplicate_and_invalid_ids_are_rejected_without_changes(): void
    {
        $owner = $this->portfolio();
        $other = $this->portfolio();
        $mine = $owner->projects()->create(['title' => 'Meu', 'display_order' => 9]);
        $foreign = $other->projects()->create(['title' => 'Alheio', 'display_order' => 20]);
        $this->actingAs($owner->user);
        foreach ([
            [['id' => $foreign->id, 'is_featured' => true]],
            [['id' => $mine->id, 'is_featured' => true], ['id' => $mine->id, 'is_featured' => true]],
            [['id' => $mine->id, 'is_featured' => 'invalid']],
            [['id' => $mine->id, 'is_featured' => true, 'title' => 'Injected']],
        ] as $projects) {
            $this->patchJson(route('admin.projects.organize.update'), ['revision' => $this->revision($owner), 'projects' => $projects])->assertUnprocessable();
            $this->assertSame(9, $mine->fresh()->display_order);
            $this->assertFalse($mine->fresh()->is_featured);
            $this->assertSame(20, $foreign->fresh()->display_order);
        }
    }

    public function test_missing_items_and_stale_revision_do_not_partially_overwrite_existing_data(): void
    {
        $portfolio = $this->portfolio();
        $first = $portfolio->projects()->create(['title' => 'Um', 'display_order' => 4]);
        $revision = $this->revision($portfolio);
        $second = $portfolio->projects()->create(['title' => 'Dois', 'display_order' => 5]);
        $this->actingAs($portfolio->user)->patchJson(route('admin.projects.organize.update'), ['revision' => $revision, 'projects' => [
            ['id' => $first->id, 'is_featured' => true], ['id' => $second->id, 'is_featured' => true],
        ]])->assertStatus(409);
        $this->patchJson(route('admin.projects.organize.update'), ['revision' => $this->revision($portfolio), 'projects' => [
            ['id' => $first->id, 'is_featured' => true],
        ]])->assertStatus(409);
        $this->assertSame(4, $first->fresh()->display_order);
        $this->assertFalse($first->fresh()->is_featured);
        $this->assertSame(5, $second->fresh()->display_order);
    }

    public function test_changed_feature_or_order_invalidates_revision_even_with_same_timestamp(): void
    {
        $portfolio = $this->portfolio();
        $project = $portfolio->projects()->create(['title' => 'Projeto']);
        $revision = $this->revision($portfolio);
        $project->update(['is_featured' => true]);
        $this->actingAs($portfolio->user)->patchJson(route('admin.projects.organize.update'), ['revision' => $revision, 'projects' => [['id' => $project->id, 'is_featured' => false]]])->assertStatus(409);
        $this->assertTrue($project->fresh()->is_featured);
    }

    public function test_empty_list_is_valid_and_guests_cannot_save(): void
    {
        $portfolio = $this->portfolio();
        $data = ['revision' => $this->revision($portfolio), 'projects' => []];
        $this->patchJson(route('admin.projects.organize.update'), $data)->assertUnauthorized();
        $this->actingAs($portfolio->user)->patchJson(route('admin.projects.organize.update'), $data)->assertOk();
    }

    private function portfolio(): Portfolio
    {
        return app(EnsurePortfolio::class)->handle(User::factory()->create());
    }

    private function revision(Portfolio $portfolio): string
    {
        return app(ProjectOrganization::class)->revision($portfolio->projects()->get());
    }
}
