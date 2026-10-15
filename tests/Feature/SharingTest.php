<?php

namespace Tests\Feature;

use App\Actions\EnsurePortfolio;
use App\Actions\SaveSharing;
use App\Models\Portfolio;
use App\Models\User;
use App\Services\SharingImageStorage;
use App\Support\SharingMetadata;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SharingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_portfolio_metadata_is_customizable_without_changing_presentation_or_publication(): void
    {
        $portfolio = $this->portfolio();
        $this->actingAs($portfolio->user)->patch(route('admin.portfolio.sharing.update'), [
            'share_title' => 'Meu cartão personalizado', 'share_description' => 'Resumo para compartilhar.',
            'share_image' => $this->image()->size(10240), 'is_published' => true, 'professional_name' => 'Injected',
        ])->assertSessionHasNoErrors();
        $portfolio->refresh();
        $this->assertFalse($portfolio->is_published);
        $this->assertNotSame('Injected', $portfolio->professional_name);
        Storage::disk('public')->assertExists($portfolio->share_image_path);
        $this->get(route('portfolio.show', $portfolio->slug))->assertNotFound();
        $portfolio->update(['is_published' => true]);
        $metadata = app(SharingMetadata::class)->forPortfolio($portfolio);
        $this->get(route('portfolio.show', $portfolio->slug))->assertOk()
            ->assertSee('<meta property="og:title" content="Meu cartão personalizado">', false)
            ->assertSee('<meta name="twitter:description" content="Resumo para compartilhar.">', false)
            ->assertSee('<meta property="og:image" content="'.$metadata['image'].'">', false)
            ->assertSee('summary_large_image')->assertSee($portfolio->professional_name);
    }

    public function test_project_metadata_is_independent_and_html_is_escaped_in_head(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->update(['is_published' => true, 'share_title' => 'Cartão do portfólio']);
        $project = $portfolio->projects()->create(['title' => 'Título visível', 'description' => 'Descrição visível']);
        $this->actingAs($portfolio->user)->patch(route('admin.projects.sharing.update', $project), [
            'share_title' => 'Meu projeto "especial" <script>alert(1)</script>', 'share_description' => 'Resumo exclusivo', 'share_image' => $this->image(),
        ])->assertSessionHasNoErrors();
        $this->assertSame('Cartão do portfólio', $portfolio->fresh()->share_title);
        $project->refresh();
        $this->get(route('portfolio.projects.show', [$portfolio->slug, $project->id]))->assertOk()
            ->assertSee($project->share_title)->assertSee('Resumo exclusivo')
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('Título visível');
        $this->get(route('admin.projects.edit', $project))->assertOk()->assertSee('Compartilhamento');
    }

    public function test_fallbacks_use_project_cover_then_portfolio_share_image_then_photo(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->update(['photo_path' => 'photo.png', 'bio' => 'Bio do portfólio', 'share_image_path' => 'portfolio-share.png']);
        $project = $portfolio->projects()->create(['title' => 'Projeto', 'description_format' => 'html', 'description' => '<p>Resumo <strong>formatado</strong></p>', 'cover_image' => 'cover.png']);
        $metadata = app(SharingMetadata::class);
        $this->assertSame(asset('storage/cover.png'), $metadata->forProject($portfolio, $project)['image']);
        $this->assertSame('Resumo formatado', $metadata->forProject($portfolio, $project)['description']);
        $project->cover_image = null;
        $this->assertSame(asset('storage/portfolio-share.png'), $metadata->forProject($portfolio, $project)['image']);
        $portfolio->share_image_path = null;
        $this->assertSame(asset('storage/photo.png'), $metadata->forProject($portfolio, $project)['image']);
        $portfolio->photo_path = null;
        $this->assertNull($metadata->forPortfolio($portfolio)['image']);
    }

    public function test_canonical_urls_do_not_include_pagination_query_or_private_preview_path(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->update(['is_published' => true]);
        $canonical = route('portfolio.show', $portfolio->slug);
        $this->get($canonical.'?page=2&tracking=ignored')->assertOk()
            ->assertSee('<link rel="canonical" href="'.$canonical.'">', false)
            ->assertSee('<meta property="og:url" content="'.$canonical.'">', false)
            ->assertSee('<meta name="twitter:card" content="summary">', false);
        $this->actingAs($portfolio->user)->get(route('admin.portfolio.preview'))->assertOk()
            ->assertSee('<link rel="canonical" href="'.$canonical.'">', false)->assertSee('noindex, nofollow');
    }

    public function test_images_are_replaced_and_removed_after_saving_and_fields_can_restore_defaults(): void
    {
        $portfolio = $this->portfolio();
        $route = route('admin.portfolio.sharing.update');
        $this->actingAs($portfolio->user)->patch($route, ['share_title' => 'Custom', 'share_image' => $this->image()])->assertSessionHasNoErrors();
        $old = $portfolio->fresh()->share_image_path;
        $this->patch($route, ['share_image' => $this->image()])->assertSessionHasNoErrors();
        $new = $portfolio->fresh()->share_image_path;
        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);
        $this->patch($route, ['share_title' => '', 'share_description' => '', 'remove_share_image' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($portfolio->fresh()->share_image_path);
        $this->assertNull($portfolio->fresh()->share_title);
        Storage::disk('public')->assertMissing($new);
    }

    public function test_foreign_projects_and_injected_paths_cannot_modify_other_accounts_or_files(): void
    {
        $owner = $this->portfolio();
        $other = $this->portfolio();
        $foreign = $other->projects()->create(['title' => 'Alheio']);
        app(SaveSharing::class)->handle($other, [], $foreign, $this->image());
        $path = $foreign->fresh()->share_image_path;
        $this->actingAs($owner->user)->patch(route('admin.projects.sharing.update', $foreign), ['remove_share_image' => '1', 'share_image' => $this->image()])->assertForbidden();
        $this->patch(route('admin.portfolio.sharing.update'), ['id' => $other->id, 'user_id' => $other->user_id, 'share_image_path' => $path, 'share_title' => 'Meu cartão'])->assertSessionHasNoErrors();
        $this->assertNull($owner->fresh()->share_image_path);
        $this->assertNull($other->fresh()->share_title);
        $this->assertSame($path, $foreign->fresh()->share_image_path);
        app(SharingImageStorage::class)->delete($owner->id, $path);
        app(SharingImageStorage::class)->delete($other->id, 'portfolios/'.$other->id.'/sharing/../../outside.png');
        Storage::disk('public')->assertExists($path);
    }

    public function test_invalid_uploads_and_oversized_text_are_rejected_before_any_changes(): void
    {
        $portfolio = $this->portfolio();
        $this->actingAs($portfolio->user);
        foreach ([
            ['share_title' => str_repeat('x', 101)], ['share_description' => str_repeat('x', 301)],
            ['share_image' => UploadedFile::fake()->create('fake.svg', 1, 'image/svg+xml')],
            ['share_image' => UploadedFile::fake()->create('large.png', 10241, 'image/png')],
            ['share_image' => $this->image(), 'remove_share_image' => true],
        ] as $data) {
            $this->patch(route('admin.portfolio.sharing.update'), $data)->assertSessionHasErrorsIn('sharing');
            $this->assertNull($portfolio->fresh()->share_image_path);
            $this->assertNull($portfolio->fresh()->share_title);
            $this->assertCount(0, Storage::disk('public')->allFiles());
        }
    }

    public function test_failed_scoped_database_lookup_removes_new_upload_and_preserves_existing_files(): void
    {
        $owner = $this->portfolio();
        $other = $this->portfolio();
        $project = $other->projects()->create(['title' => 'Outro']);
        app(SaveSharing::class)->handle($other, [], $project, $this->image());
        $old = $project->fresh()->share_image_path;
        try {
            app(SaveSharing::class)->handle($owner, [], $project, $this->image());
            $this->fail('O projeto de outra conta deveria ser recusado.');
        } catch (ModelNotFoundException) {
            Storage::disk('public')->assertExists($old);
            $this->assertCount(1, Storage::disk('public')->allFiles());
        }
    }

    public function test_deleting_project_and_account_removes_only_their_sharing_images(): void
    {
        $owner = $this->portfolio();
        $other = $this->portfolio();
        $project = $owner->projects()->create(['title' => 'Meu']);
        app(SaveSharing::class)->handle($owner, [], image: $this->image());
        app(SaveSharing::class)->handle($owner, [], $project, $this->image());
        app(SaveSharing::class)->handle($other, [], image: $this->image());
        $portfolioPath = $owner->fresh()->share_image_path;
        $projectPath = $project->fresh()->share_image_path;
        $otherPath = $other->fresh()->share_image_path;
        $this->actingAs($owner->user)->delete(route('admin.projects.destroy', $project))->assertRedirect();
        Storage::disk('public')->assertMissing($projectPath);
        Storage::disk('public')->assertExists([$portfolioPath, $otherPath]);
        $second = $owner->projects()->create(['title' => 'Outro meu']);
        app(SaveSharing::class)->handle($owner, [], $second, $this->image());
        $secondPath = $second->fresh()->share_image_path;
        $this->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');
        Storage::disk('public')->assertMissing([$portfolioPath, $secondPath]);
        Storage::disk('public')->assertExists($otherPath);
    }

    public function test_guests_cannot_change_sharing_settings(): void
    {
        $portfolio = $this->portfolio();
        $project = $portfolio->projects()->create(['title' => 'Projeto']);
        $this->patchJson(route('admin.portfolio.sharing.update'), ['share_title' => 'No'])->assertUnauthorized();
        $this->patchJson(route('admin.projects.sharing.update', $project), ['share_title' => 'No'])->assertUnauthorized();
    }

    public function test_public_portfolio_and_project_offer_sharing_their_canonical_links(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->update(['is_published' => true, 'share_title' => 'Meu cartão salvo']);
        $project = $portfolio->projects()->create(['title' => 'Projeto', 'share_title' => 'Cartão do projeto']);
        foreach ([route('portfolio.show', $portfolio->slug), route('portfolio.projects.show', [$portfolio->slug, $project->id])] as $url) {
            $response = $this->get($url.'?tracking=ignored')->assertOk()
                ->assertSee('data-share-link', false)->assertSee('value="'.$url.'"', false);
            if ($url === route('portfolio.show', $portfolio->slug)) {
                $response->assertDontSee('data-copy-link', false)->assertSee('pf-link pf-share-link', false);
            } else {
                $response->assertDontSee('data-copy-link', false)->assertSee('pf-share-actions-inline', false);
            }
        }
        $this->actingAs($portfolio->user)->get(route('admin.portfolio.edit'))->assertOk()
            ->assertSee('data-share-actions', false)->assertSee('Meu cartão salvo');
        $this->get(route('admin.projects.edit', $project))->assertOk()
            ->assertSee('data-share-actions', false)->assertSee('Cartão do projeto');
    }

    public function test_drafts_and_private_previews_do_not_offer_sharing_controls(): void
    {
        $portfolio = $this->portfolio();
        $project = $portfolio->projects()->create(['title' => 'Projeto em rascunho']);
        $this->actingAs($portfolio->user)->get(route('admin.portfolio.edit'))->assertOk()
            ->assertDontSee('data-share-actions', false)->assertSee('Publique o portfólio para compartilhar seus links públicos.');
        $this->get(route('admin.projects.edit', $project))->assertOk()->assertDontSee('data-share-actions', false);
        $this->get(route('admin.portfolio.preview'))->assertOk()->assertDontSee('data-share-actions', false);
        $this->get(route('admin.portfolio.preview.project', $project))->assertOk()->assertDontSee('data-share-actions', false);
        $portfolio->update(['is_published' => true]);
        $this->get(route('admin.portfolio.preview'))->assertOk()->assertDontSee('data-share-actions', false);
    }

    private function portfolio(): Portfolio
    {
        return app(EnsurePortfolio::class)->handle(User::factory()->create());
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('share.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8V8AAAAASUVORK5CYII='));
    }
}
