<?php

namespace Tests\Feature\Admin;

use App\Actions\SaveProject;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_staged_upload_is_attached_on_create_and_rendered_between_paragraphs(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $this->assertNull($media->project_id);
        $this->assertNull($media->attached_at);
        Storage::disk('public')->assertExists($media->path);

        $this->post(route('admin.projects.store'), $this->data($token, '<p>Antes</p>'.$this->imageHtml($media).'<p>Depois</p>'))
            ->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $this->assertSame($project->id, $media->fresh()->project_id);
        $this->assertNotNull($media->fresh()->attached_at);
        $project->portfolio->update(['is_published' => true]);
        $this->get(route('portfolio.projects.show', [$project->portfolio->slug, $project->id]))->assertOk()
            ->assertSeeInOrder(['Antes', $media->url, 'Depois'], false)->assertSee('Legenda da imagem');
        $this->get(route('admin.projects.edit', $project))->assertOk()->assertSee($media->id);
    }

    public function test_image_only_and_video_only_descriptions_are_not_discarded(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $this->post(route('admin.projects.store'), $this->data($token, '<img src="ignored" data-media-id="'.$media->id.'" alt="Exemplo">'))->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $this->assertNotNull($project->description);
        $project->portfolio->update(['is_published' => true]);
        $this->get(route('portfolio.projects.show', [$project->portfolio->slug, $project->id]))->assertOk()->assertSee($media->url);

        $this->put(route('admin.projects.update', $project), $this->data((string) Str::uuid(), '<iframe src="https://vimeo.com/123456789"></iframe>'))
            ->assertSessionHasNoErrors();
        $this->assertStringContainsString('https://player.vimeo.com/video/123456789', $project->fresh()->description);
        $this->get(route('portfolio.projects.show', [$project->portfolio->slug, $project->id]))->assertOk()->assertSee('https://player.vimeo.com/video/123456789');
    }

    public function test_removing_an_image_only_deletes_the_file_after_save_and_preserves_other_tab_uploads(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($media)))->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $pending = $this->upload($owner, (string) Str::uuid(), $project);
        Storage::disk('public')->assertExists([$media->path, $pending->path]);

        $this->put(route('admin.projects.update', $project), $this->data((string) Str::uuid(), '<p>Sem imagem</p>'))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($media->path);
        Storage::disk('public')->assertExists($pending->path);
        $this->assertModelMissing($media);
        $this->assertModelExists($pending);
    }

    public function test_validation_error_preserves_temporary_uploads_and_old_editor_input(): void
    {
        $token = (string) Str::uuid();
        $media = $this->upload(User::factory()->create(), $token);
        $data = $this->data($token, $this->imageHtml($media));
        $data['title'] = '';
        $this->from(route('admin.projects.create'))->post(route('admin.projects.store'), $data)->assertSessionHasErrors('title');
        $this->get(route('admin.projects.create'))->assertOk()->assertSee($media->id);
        $this->assertNull($media->fresh()->attached_at);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_another_account_cannot_upload_to_or_reuse_images_from_a_foreign_project(): void
    {
        $first = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($first, $token);
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($media)))->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $second = User::factory()->create();
        $this->actingAs($second)->postJson(route('admin.project-media.store'), [
            'image' => $this->image(), 'upload_token' => (string) Str::uuid(), 'project_id' => $project->id,
        ])->assertForbidden();
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($media)))->assertSessionHasNoErrors();
        $other = $second->portfolio->projects()->sole();
        $this->assertStringNotContainsString($media->id, $other->description ?? '');
        Storage::disk('public')->assertExists($media->path);
        $this->assertSame($project->id, $media->fresh()->project_id);
        $this->assertDatabaseCount('project_media', 1);
    }

    public function test_image_from_another_project_of_the_same_owner_is_not_reused(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($media)))->assertSessionHasNoErrors();
        $this->post(route('admin.projects.store'), $this->data((string) Str::uuid(), $this->imageHtml($media)))->assertSessionHasNoErrors();
        $this->assertNull(Project::orderByDesc('id')->first()->description);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_only_registered_images_are_rendered_and_client_src_is_ignored(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $html = '<img data-media-id="'.$media->id.'" src="https://evil.example/tracker.png" onerror="alert(1)" alt="Imagem">'
            .'<img src="https://evil.example/other.png"><iframe src="https://evil.example/player"></iframe>';
        $this->post(route('admin.projects.store'), $this->data($token, $html))->assertSessionHasNoErrors();
        $description = Project::query()->sole()->description;
        $this->assertStringContainsString($media->url, $description);
        $this->assertStringNotContainsString('evil.example', $description);
        $this->assertStringNotContainsString('onerror', $description);
    }

    public function test_invalid_files_tokens_and_quota_are_rejected_before_storage(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        foreach ([$this->image()->size(2049), UploadedFile::fake()->createWithContent('bad.png', 'not an image'),
            UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')] as $image) {
            $this->postJson(route('admin.project-media.store'), ['image' => $image, 'upload_token' => (string) Str::uuid()])->assertUnprocessable()->assertJsonValidationErrors('image');
        }
        $this->postJson(route('admin.project-media.store'), ['image' => $this->image(), 'upload_token' => 'invalid'])->assertUnprocessable();
        config(['portfolio.attachments.max_per_project' => 0]);
        $this->postJson(route('admin.project-media.store'), ['image' => $this->image(), 'upload_token' => (string) Str::uuid()])->assertUnprocessable();
        $this->assertCount(0, Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('project_media', 0);
    }

    public function test_project_and_account_deletion_remove_attached_and_staged_files(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($media)))->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $pending = $this->upload($owner, (string) Str::uuid(), $project);
        $other = $this->upload(User::factory()->create(), (string) Str::uuid());
        $this->actingAs($owner)->delete(route('admin.projects.destroy', $project))->assertRedirect();
        Storage::disk('public')->assertMissing([$media->path, $pending->path]);
        Storage::disk('public')->assertExists($other->path);
        $loose = $this->upload($owner, (string) Str::uuid());
        $this->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');
        Storage::disk('public')->assertMissing($loose->path);
        Storage::disk('public')->assertExists($other->path);
    }

    public function test_pruning_only_removes_expired_unattached_uploads(): void
    {
        $owner = User::factory()->create();
        $expired = $this->upload($owner, (string) Str::uuid());
        $expired->forceFill(['created_at' => now()->subHours(25)])->save();
        $recent = $this->upload($owner, (string) Str::uuid());
        $token = (string) Str::uuid();
        $attached = $this->upload($owner, $token);
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($attached)))->assertSessionHasNoErrors();
        $attached->forceFill(['created_at' => now()->subHours(30)])->save();

        $this->artisan('project-media:prune')->assertSuccessful();
        $this->assertModelMissing($expired);
        Storage::disk('public')->assertMissing($expired->path);
        Storage::disk('public')->assertExists([$recent->path, $attached->path]);
    }

    public function test_video_endpoint_and_guest_access(): void
    {
        $this->postJson(route('admin.project-media.store'))->assertUnauthorized();
        $this->postJson(route('admin.project-media.video'), ['url' => 'https://youtu.be/abcdefghijk'])->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson(route('admin.project-media.video'), ['url' => 'https://youtu.be/abcdefghijk'])
            ->assertOk()->assertJsonPath('src', 'https://www.youtube-nocookie.com/embed/abcdefghijk');
        $this->postJson(route('admin.project-media.video'), ['url' => 'https://evil.example/player'])->assertUnprocessable();
    }

    public function test_code_samples_cannot_reassign_media_from_another_project(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($media)))->assertSessionHasNoErrors();
        $firstProjectId = $media->fresh()->project_id;
        $sample = '<pre><code>data-media-id="'.$media->id.'"</code></pre>';
        $this->post(route('admin.projects.store'), $this->data((string) Str::uuid(), $sample))->assertSessionHasNoErrors();
        $this->assertSame($firstProjectId, $media->fresh()->project_id);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_failed_database_save_keeps_existing_and_pending_images(): void
    {
        $owner = User::factory()->create();
        $token = (string) Str::uuid();
        $media = $this->upload($owner, $token);
        $this->post(route('admin.projects.store'), $this->data($token, $this->imageHtml($media)))->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $nextToken = (string) Str::uuid();
        $pending = $this->upload($owner, $nextToken, $project);
        $attributes = $this->data($nextToken, $this->imageHtml($pending));
        $attributes['title'] = null;
        try {
            app(SaveProject::class)->handle($owner->portfolio, $attributes, $project);
            $this->fail('O banco deveria rejeitar o título nulo.');
        } catch (QueryException) {
            $this->assertStringContainsString($media->id, $project->fresh()->description);
            $this->assertNull($pending->fresh()->attached_at);
            Storage::disk('public')->assertExists([$media->path, $pending->path]);
        }
    }

    public function test_saved_image_limit_cannot_be_bypassed_by_uploading_a_new_batch(): void
    {
        $owner = User::factory()->create();
        $firstToken = (string) Str::uuid();
        $first = $this->upload($owner, $firstToken);
        $this->post(route('admin.projects.store'), $this->data($firstToken, $this->imageHtml($first)))->assertSessionHasNoErrors();
        $project = Project::query()->sole();
        $secondToken = (string) Str::uuid();
        $second = $this->upload($owner, $secondToken);
        config(['portfolio.attachments.max_per_project' => 1]);
        $this->put(route('admin.projects.update', $project), $this->data($secondToken, $this->imageHtml($first).$this->imageHtml($second)))
            ->assertSessionHasErrors('description');
        $this->assertNull($second->fresh()->attached_at);
        Storage::disk('public')->assertExists([$first->path, $second->path]);
    }

    private function upload(User $owner, string $token, ?Project $project = null): ProjectMedia
    {
        $response = $this->actingAs($owner)->postJson(route('admin.project-media.store'), [
            'image' => $this->image(), 'upload_token' => $token, 'project_id' => $project?->id,
        ])->assertCreated();

        return ProjectMedia::findOrFail($response->json('id'));
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('image.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8V8AAAAASUVORK5CYII='));
    }

    private function imageHtml(ProjectMedia $media): string
    {
        return '<figure><img src="'.$media->url.'" data-media-id="'.$media->id.'" alt="Imagem de exemplo"><figcaption>Legenda da imagem</figcaption></figure>';
    }

    private function data(string $token, string $description): array
    {
        return ['title' => 'Projeto com mídia', 'description' => $description, 'description_format' => 'html',
            'media_token' => $token, 'display_order' => 0, 'is_featured' => '0'];
    }
}
