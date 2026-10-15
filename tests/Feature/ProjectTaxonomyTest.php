<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Actions\SaveProject;
use App\Models\Portfolio;
use App\Models\User;
use App\Support\ProjectTags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    public function test_tags_are_normalized_deduplicated_and_scoped_to_portfolio(): void
    {
        $portfolio = $this->portfolio();
        $this->actingAs($portfolio->user)->post(route('admin.projects.store'), $this->data(['category' => 'dashboard', 'tags' => ' Laravel, laravel, PHP 8, C++, C#, .NET, MySQL ']))->assertSessionHasNoErrors();
        $project = $portfolio->projects()->sole();
        $this->assertSame('dashboard', $project->category);
        $this->assertSame(6, $project->tags()->count());
        $this->assertSame(1, $portfolio->tags()->where('key', 'laravel')->count());
        $other = $this->portfolio();
        app(SaveProject::class)->handle($other, $this->data(['tags' => 'Laravel']));
        $this->assertNotSame($portfolio->tags()->where('key', 'laravel')->first()->id, $other->tags()->first()->id);
        $this->assertSame('php', app(ProjectTags::class)->icon('PHP 8'));
        $this->assertSame('tag', app(ProjectTags::class)->icon('Minha stack'));
    }

    public function test_tags_can_be_preserved_cleared_and_reused_without_deleting_another_projects_tags(): void
    {
        $portfolio = $this->portfolio();
        $first = app(SaveProject::class)->handle($portfolio, $this->data(['tags' => 'Laravel, MySQL']));
        $second = app(SaveProject::class)->handle($portfolio, $this->data(['tags' => 'Laravel']));
        $this->actingAs($portfolio->user)->put(route('admin.projects.update', $first), $this->data())->assertSessionHasNoErrors();
        $this->assertSame(2, $first->tags()->count());
        $this->put(route('admin.projects.update', $first), $this->data(['tags' => '']))->assertSessionHasNoErrors();
        $this->assertSame(0, $first->tags()->count());
        $this->assertSame(['Laravel'], $second->fresh()->tags->pluck('name')->all());
        $this->assertSame(1, $portfolio->tags()->count());
        $this->delete(route('admin.projects.destroy', $second))->assertRedirect();
        $this->assertSame(0, $portfolio->tags()->count());
    }

    public function test_invalid_categories_and_tag_limits_are_rejected_without_partial_writes(): void
    {
        $portfolio = $this->portfolio();
        $this->actingAs($portfolio->user);
        foreach ([['category' => 'invalid'], ['tags' => '<script>alert(1)</script>'], ['tags' => str_repeat('a', 31)], ['tags' => 'a,b,c,d,e,f,g,h,i'], ['tags' => ['Laravel']]] as $invalid) {
            $this->post(route('admin.projects.store'), $this->data($invalid))->assertSessionHasErrors();
            $this->assertSame(0, $portfolio->projects()->count());
            $this->assertSame(0, $portfolio->tags()->count());
        }
    }

    public function test_another_account_cannot_edit_tags_or_assign_arbitrary_tag_ids(): void
    {
        $owner = $this->portfolio();
        $other = $this->portfolio();
        $foreign = app(SaveProject::class)->handle($other, $this->data(['tags' => 'PrivateTag']));
        $this->actingAs($owner->user)->put(route('admin.projects.update', $foreign), $this->data(['tags' => 'Changed']))->assertForbidden();
        $this->post(route('admin.projects.store'), $this->data(['tag_ids' => $foreign->tags->modelKeys()]))->assertSessionHasNoErrors();
        $this->assertSame(0, $owner->projects()->sole()->tags()->count());
        $this->assertSame('PrivateTag', $foreign->fresh()->tags->first()->name);
    }

    public function test_admin_combines_search_category_tag_and_feature_filters_with_owned_options(): void
    {
        $owner = $this->portfolio();
        $wanted = app(SaveProject::class)->handle($owner, $this->data(['title' => 'Anime dashboard', 'category' => 'dashboard', 'tags' => 'Laravel', 'is_featured' => true]));
        app(SaveProject::class)->handle($owner, $this->data(['title' => 'Anime site', 'category' => 'website', 'tags' => 'Laravel']));
        $other = $this->portfolio();
        app(SaveProject::class)->handle($other, $this->data(['title' => 'Alheio', 'tags' => 'PrivateTag']));
        $filters = ['q' => 'Anime', 'category' => 'dashboard', 'tag' => $wanted->tags->first()->id, 'featured' => '1'];
        $this->actingAs($owner->user)->get(route('admin.projects.index', $filters))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$wanted->id])
            ->assertDontSee('PrivateTag')->assertDontSee('Anime site');
        $this->get(route('admin.projects.index', ['featured' => '0']))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->count() === 1 && ! $projects->first()->is_featured);
    }

    public function test_public_and_private_preview_filters_are_scoped_and_keep_carousel_independent(): void
    {
        $owner = $this->portfolio();
        $owner->update(['is_published' => true, 'show_featured_carousel' => true]);
        $featured = app(SaveProject::class)->handle($owner, $this->data(['title' => 'Destaque', 'tags' => 'PHP 8', 'is_featured' => true]));
        $wanted = app(SaveProject::class)->handle($owner, $this->data(['title' => 'Tracker', 'category' => 'application', 'tags' => 'Laravel']));
        $other = $this->portfolio();
        $foreign = app(SaveProject::class)->handle($other, $this->data(['tags' => 'PrivateTag']));
        $url = route('portfolio.show', $owner->slug);
        $this->get($url.'?tag='.$wanted->tags->first()->id)->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$wanted->id])
            ->assertViewHas('featuredProjects', fn ($projects) => $projects->modelKeys() === [$featured->id])
            ->assertDontSee('PrivateTag')->assertSee('Laravel')->assertSee('Aplicação web');
        $this->get($url.'?tag='.$foreign->tags->first()->id)->assertOk()->assertSee('Nenhum projeto encontrado com esses filtros.')
            ->assertViewHas('projects', fn ($projects) => $projects->isEmpty());
        $owner->update(['is_published' => false]);
        $this->get($url)->assertNotFound();
        $this->actingAs($owner->user)->get(route('admin.portfolio.preview', ['category' => 'application']))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$wanted->id]);
        $this->get(route('admin.portfolio.preview.project', $wanted))->assertOk()->assertSee('Laravel')->assertSee('Categoria e tecnologias');
    }

    public function test_search_treats_wildcards_and_sql_as_literal_text(): void
    {
        $owner = $this->portfolio();
        $literal = app(SaveProject::class)->handle($owner, $this->data(['title' => '100%_pronto!']));
        app(SaveProject::class)->handle($owner, $this->data(['title' => '100XYpronto!']));
        $this->actingAs($owner->user)->get(route('admin.projects.index', ['q' => '%_']))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->modelKeys() === [$literal->id]);
        $this->get(route('admin.projects.index', ['q' => "' OR 1=1 --"]))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->isEmpty());
        $this->getJson(route('admin.projects.index', ['q' => ['invalid']]))->assertUnprocessable();
    }

    public function test_pagination_keeps_filters_and_returns_all_matches_across_pages(): void
    {
        $owner = $this->portfolio();
        $owner->update(['is_published' => true]);
        for ($i = 0; $i < 14; $i++) {
            app(SaveProject::class)->handle($owner, $this->data(['title' => 'Tracker '.$i, 'tags' => 'Laravel', 'category' => 'application']));
        }
        $filters = ['q' => 'Tracker', 'featured' => '0', 'category' => 'application', 'tag' => $owner->tags()->first()->id];
        $this->actingAs($owner->user)->get(route('admin.projects.index', $filters))->assertOk()
            ->assertViewHas('projects', function ($projects) use ($filters) {
                parse_str(parse_url($projects->nextPageUrl(), PHP_URL_QUERY), $query);

                return $projects->total() === 14 && $query['q'] === $filters['q'] && $query['featured'] === '0' && (int) $query['tag'] === $filters['tag'];
            });
        $this->get(route('portfolio.show', ['slug' => $owner->slug] + $filters + ['page' => 2]))->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->count() === 2 && $projects->total() === 14);
    }

    public function test_legacy_projects_remain_uncategorized_and_tags_cascade_when_account_is_deleted(): void
    {
        $owner = $this->portfolio();
        $legacy = app(SaveProject::class)->handle($owner, $this->data());
        $this->assertNull($legacy->category);
        $this->assertCount(0, $legacy->tags);
        app(SaveProject::class)->handle($owner, $this->data(['tags' => 'Laravel']));
        $other = $this->portfolio();
        app(SaveProject::class)->handle($other, $this->data(['tags' => 'Laravel']));
        $this->actingAs($owner->user)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');
        $this->assertDatabaseMissing('tags', ['portfolio_id' => $owner->id]);
        $this->assertSame(1, $other->tags()->count());
        $this->assertDatabaseCount('project_tag', 1);
    }

    private function portfolio(): Portfolio
    {
        return app(EnsurePortfolio::class)->handle(User::factory()->create());
    }

    private function data(array $overrides = []): array
    {
        return array_replace(['title' => 'Projeto', 'display_order' => 0, 'is_featured' => false], $overrides);
    }
}
