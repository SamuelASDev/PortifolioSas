<?php

namespace Tests\Feature\Admin;

use App\Actions\EnsurePortfolio;
use App\Actions\SaveProject;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectCoverStorage;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectCoverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_owner_can_upload_a_cover_with_a_generated_name_and_view_it(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.projects.store'), $this->data([
            'cover_image' => $this->image('original-name.png'),
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.projects.index'));

        $project = Project::query()->sole();
        $this->assertStringStartsWith('portfolios/'.$project->portfolio_id.'/covers/', $project->cover_image);
        $this->assertStringNotContainsString('original-name', $project->cover_image);
        Storage::disk('public')->assertExists($project->cover_image);
        $this->get(route('admin.projects.index'))->assertOk()->assertSee($project->cover_url);
        $this->get(route('admin.projects.edit', $project))->assertOk()
            ->assertSee($project->cover_url)->assertSee('multipart/form-data');
    }

    public function test_replacement_deletes_only_the_previous_cover(): void
    {
        [$owner, $project] = $this->projectWithCover();
        [, $other] = $this->projectWithCover();
        $old = $project->cover_image;

        // Simula o POST multipart com method spoofing enviado pelo formulário.
        $this->actingAs($owner)->post(route('admin.projects.update', $project), $this->data([
            '_method' => 'PUT', 'cover_image' => $this->image('new.png'),
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.projects.index'));

        $new = $project->fresh()->cover_image;
        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists([$new, $other->cover_image]);
    }

    public function test_update_without_upload_keeps_existing_cover(): void
    {
        [$owner, $project] = $this->projectWithCover();
        $path = $project->cover_image;

        $this->actingAs($owner)->put(route('admin.projects.update', $project), $this->data())
            ->assertSessionHasNoErrors();

        $this->assertSame($path, $project->fresh()->cover_image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_owner_can_remove_cover_without_deleting_project(): void
    {
        [$owner, $project] = $this->projectWithCover();
        $old = $project->cover_image;

        $this->actingAs($owner)->put(route('admin.projects.update', $project), $this->data(['remove_cover' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertNull($project->fresh()->cover_image);
        $this->assertNull($project->fresh()->cover_url);
        Storage::disk('public')->assertMissing($old);
        $this->assertModelExists($project);
    }

    public function test_deleting_project_deletes_its_cover_and_preserves_other_accounts(): void
    {
        [$owner, $project] = $this->projectWithCover();
        [, $other] = $this->projectWithCover();

        $this->actingAs($owner)->delete(route('admin.projects.destroy', $project))->assertRedirect(route('admin.projects.index'));

        $this->assertModelMissing($project);
        Storage::disk('public')->assertMissing($project->cover_image);
        Storage::disk('public')->assertExists($other->cover_image);
    }

    public function test_another_user_cannot_replace_remove_or_delete_a_cover(): void
    {
        [, $project] = $this->projectWithCover();
        $path = $project->cover_image;
        $this->actingAs(User::factory()->create());

        $this->put(route('admin.projects.update', $project), $this->data(['cover_image' => $this->image()]))->assertForbidden();
        $this->put(route('admin.projects.update', $project), $this->data(['remove_cover' => '1']))->assertForbidden();
        $this->delete(route('admin.projects.destroy', $project))->assertForbidden();

        $this->assertSame($path, $project->fresh()->cover_image);
        Storage::disk('public')->assertExists($path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    #[DataProvider('invalidCovers')]
    public function test_invalid_uploads_leave_the_existing_file_and_database_unchanged(string $kind): void
    {
        [$owner, $project] = $this->projectWithCover();
        $path = $project->cover_image;
        $upload = match ($kind) {
            'oversized' => $this->image()->size(2049),
            'svg' => UploadedFile::fake()->createWithContent('cover.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            'spoofed' => UploadedFile::fake()->createWithContent('cover.png', '<?php echo "not an image";'),
            'path' => 'portfolios/999/covers/stolen.png',
            'dimensions' => $this->imageWithDimensions(6001, 1),
        };

        $this->actingAs($owner)->post(route('admin.projects.store'), $this->data(['cover_image' => $upload]))
            ->assertSessionHasErrors('cover_image');
        $this->put(route('admin.projects.update', $project), $this->data(['cover_image' => $upload]))
            ->assertSessionHasErrors('cover_image');

        $this->assertSame($path, $project->fresh()->cover_image);
        Storage::disk('public')->assertExists($path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('projects', 1);
    }

    public static function invalidCovers(): array
    {
        return [['oversized'], ['svg'], ['spoofed'], ['path'], ['dimensions']];
    }

    public function test_upload_and_removal_cannot_be_requested_together(): void
    {
        [$owner, $project] = $this->projectWithCover();
        $this->actingAs($owner)->put(route('admin.projects.update', $project), $this->data([
            'cover_image' => $this->image(), 'remove_cover' => '1',
        ]))->assertSessionHasErrors('cover_image');

        Storage::disk('public')->assertExists($project->cover_image);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_failed_database_save_cleans_up_new_file_and_keeps_old_cover(): void
    {
        [$owner, $project] = $this->projectWithCover();
        $path = $project->cover_image;

        try {
            app(SaveProject::class)->handle($owner->portfolio, ['title' => null], $project, $this->image());
            $this->fail('O banco deveria rejeitar o título nulo.');
        } catch (QueryException) {
            $this->assertSame($path, $project->fresh()->cover_image);
            Storage::disk('public')->assertExists($path);
            $this->assertCount(1, Storage::disk('public')->allFiles());
        }
    }

    public function test_storage_failure_does_not_modify_the_project(): void
    {
        [$owner, $project] = $this->projectWithCover();
        $path = $project->cover_image;
        $this->mock(ProjectCoverStorage::class)->shouldReceive('store')->once()->andThrow(new \RuntimeException('Disco indisponível'));

        $this->actingAs($owner)->put(route('admin.projects.update', $project), $this->data(['cover_image' => $this->image()]))
            ->assertStatus(500);

        $this->assertSame($path, $project->fresh()->cover_image);
        Storage::disk('public')->assertExists($path);
    }

    public function test_storage_service_refuses_to_delete_other_portfolio_paths_or_traversal(): void
    {
        [, $project] = $this->projectWithCover();
        $storage = app(ProjectCoverStorage::class);
        $storage->delete($project->portfolio_id + 1, $project->cover_image);
        $storage->delete($project->portfolio_id, 'portfolios/'.$project->portfolio_id.'/covers/../../other.png');

        Storage::disk('public')->assertExists($project->cover_image);
    }

    public function test_deleting_account_removes_all_its_covers_but_preserves_other_accounts(): void
    {
        [$owner, $project] = $this->projectWithCover();
        $second = app(SaveProject::class)->handle($owner->portfolio, $this->data(), cover: $this->image());
        [, $other] = $this->projectWithCover();

        $this->actingAs($owner)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');

        Storage::disk('public')->assertMissing([$project->cover_image, $second->cover_image]);
        Storage::disk('public')->assertExists($other->cover_image);
        $this->assertModelMissing($project);
        $this->assertModelMissing($second);
        $this->assertModelExists($other);
    }

    public function test_cover_url_uses_the_application_origin_including_port(): void
    {
        [, $project] = $this->projectWithCover();
        URL::forceRootUrl('http://portfolio.test:8000');

        $this->assertSame('http://portfolio.test:8000/storage/'.$project->cover_image, $project->cover_url);
    }

    private function projectWithCover(): array
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $owner->setRelation('portfolio', $portfolio);
        $project = app(SaveProject::class)->handle($portfolio, $this->data(), cover: $this->image());

        return [$owner, $project];
    }

    private function image(string $name = 'cover.png'): UploadedFile
    {
        // PNG real de 1 × 1: não depende da extensão GD no XAMPP.
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8V8AAAAASUVORK5CYII='));
    }

    private function imageWithDimensions(int $width, int $height): UploadedFile
    {
        $chunk = fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $png = "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress(str_repeat("\0", ($width * 3 + 1) * $height)))
            .$chunk('IEND', '');

        return UploadedFile::fake()->createWithContent('wide.png', $png);
    }

    private function data(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Projeto com capa', 'description' => 'Texto simples',
            'is_featured' => '0', 'display_order' => 0,
        ], $overrides);
    }
}
