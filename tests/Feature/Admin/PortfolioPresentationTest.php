<?php

namespace Tests\Feature\Admin;

use App\Actions\EnsurePortfolio;
use App\Actions\SavePortfolio;
use App\Models\User;
use App\Services\PortfolioPhotoStorage;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PortfolioPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_owner_can_edit_presentation_and_contacts_without_publishing(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->patch(route('admin.portfolio.update'), $this->data())
            ->assertSessionHasNoErrors();
        $portfolio = $owner->portfolio()->sole();

        $this->assertSame('Nome profissional', $portfolio->professional_name);
        $this->assertSame('Desenvolvedor PHP', $portfolio->headline);
        $this->assertSame('Minha biografia.', $portfolio->bio);
        $this->assertSame('publico@example.com', $portfolio->contact_email);
        $this->assertSame('https://github.com/example', $portfolio->github_url);
        $this->assertSame('https://linkedin.com/in/example', $portfolio->linkedin_url);
        $this->assertSame('https://example.com', $portfolio->website_url);
        $this->assertFalse($portfolio->is_published);
        $this->get(route('admin.portfolio.edit'))->assertOk()->assertSee('Nome profissional')->assertSee('Rascunho');
    }

    public function test_publication_endpoint_changes_only_authenticated_users_portfolio(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $other = app(EnsurePortfolio::class)->handle(User::factory()->create());

        $this->actingAs($owner)->patch(route('admin.portfolio.publication.update'), [
            'is_published' => '1', 'portfolio_id' => $other->id, 'user_id' => $other->user_id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.portfolio.edit'));

        $this->assertTrue($portfolio->fresh()->is_published);
        $this->assertFalse($other->fresh()->is_published);
        $this->get(route('admin.portfolio.edit'))->assertOk()->assertSee('Despublicar portfólio')
            ->assertSee(route('portfolio.show', $portfolio->slug));
    }

    public function test_profile_update_ignores_publication_owner_and_raw_photo_path_fields(): void
    {
        $owner = User::factory()->create();
        $other = app(EnsurePortfolio::class)->handle(User::factory()->create());

        $this->actingAs($owner)->patch(route('admin.portfolio.update'), $this->data([
            'is_published' => '1', 'user_id' => $other->user_id, 'id' => $other->id,
            'photo_path' => 'portfolios/'.$other->id.'/photos/stolen.png',
        ]))->assertSessionHasNoErrors();

        $portfolio = $owner->portfolio()->sole();
        $this->assertSame($owner->id, $portfolio->user_id);
        $this->assertFalse($portfolio->is_published);
        $this->assertNull($portfolio->photo_path);
        $this->assertNotSame('meu-portfolio', $other->fresh()->slug);
    }

    public function test_publishing_requires_a_saved_professional_name(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $portfolio->update(['professional_name' => null]);

        $this->actingAs($owner)->patch(route('admin.portfolio.publication.update'), ['is_published' => '1'])
            ->assertSessionHasErrors('is_published');
        $this->assertFalse($portfolio->fresh()->is_published);
    }

    public function test_published_portfolio_cannot_have_its_name_cleared(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $portfolio->update(['is_published' => true]);

        $this->actingAs($owner)->patch(route('admin.portfolio.update'), $this->data(['professional_name' => '']))
            ->assertSessionHasErrors('professional_name');
        $this->assertSame($owner->name, $portfolio->fresh()->professional_name);
        $this->assertTrue($portfolio->fresh()->is_published);
    }

    public function test_guests_and_invalid_publication_requests_cannot_publish(): void
    {
        $this->patch(route('admin.portfolio.publication.update'), ['is_published' => '1'])->assertRedirect('/login');
        $this->actingAs(User::factory()->create())
            ->patch(route('admin.portfolio.publication.update'), ['is_published' => 'yes'])->assertSessionHasErrors('is_published');
        $this->patch(route('admin.portfolio.publication.update'), [])->assertSessionHasErrors('is_published');
    }

    #[DataProvider('invalidPresentation')]
    public function test_invalid_presentation_is_rejected(array $input, string $field): void
    {
        $this->actingAs(User::factory()->create())
            ->patch(route('admin.portfolio.update'), $this->data($input))->assertSessionHasErrors($field);
    }

    public static function invalidPresentation(): array
    {
        return [
            [['professional_name' => str_repeat('a', 256)], 'professional_name'],
            [['headline' => str_repeat('a', 161)], 'headline'],
            [['bio' => str_repeat('a', 1001)], 'bio'],
            [['contact_email' => 'email-invalido'], 'contact_email'],
            [['github_url' => 'javascript:alert(1)'], 'github_url'],
            [['linkedin_url' => 'ftp://example.com'], 'linkedin_url'],
            [['website_url' => '/relative-url'], 'website_url'],
            [['remove_photo' => 'yes'], 'remove_photo'],
        ];
    }

    public function test_photo_upload_replacement_removal_and_generated_paths(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('admin.portfolio.update'), $this->data([
            '_method' => 'PATCH', 'photo' => $this->image(),
        ]))->assertSessionHasNoErrors();
        $portfolio = $owner->portfolio()->sole();
        $first = $portfolio->photo_path;
        $this->assertStringStartsWith('portfolios/'.$portfolio->id.'/photos/', $first);
        $this->assertStringNotContainsString('original', $first);
        Storage::disk('public')->assertExists($first);
        $this->get(route('admin.portfolio.edit'))->assertOk()->assertSee($portfolio->photo_url);

        $this->patch(route('admin.portfolio.update'), $this->data())->assertSessionHasNoErrors();
        $this->assertSame($first, $portfolio->fresh()->photo_path);
        $this->patch(route('admin.portfolio.update'), $this->data(['photo' => $this->image()]))->assertSessionHasNoErrors();
        $second = $portfolio->fresh()->photo_path;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->patch(route('admin.portfolio.update'), $this->data(['remove_photo' => '1']))->assertSessionHasNoErrors();
        $this->assertNull($portfolio->fresh()->photo_path);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_invalid_photos_do_not_change_existing_file_or_profile(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(EnsurePortfolio::class)->handle($owner);
        $portfolio = app(SavePortfolio::class)->handle($portfolio, $this->data(), $this->image());
        $path = $portfolio->photo_path;

        $uploads = [
            $this->image()->size(2049),
            UploadedFile::fake()->createWithContent('photo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            UploadedFile::fake()->createWithContent('photo.png', 'not an image'),
            'portfolios/999/photos/stolen.png',
        ];
        foreach ($uploads as $upload) {
            $this->actingAs($owner)->patch(route('admin.portfolio.update'), $this->data(['photo' => $upload]))
                ->assertSessionHasErrors('photo');
            $this->assertSame($path, $portfolio->fresh()->photo_path);
        }
        $this->patch(route('admin.portfolio.update'), $this->data(['photo' => $this->image(), 'remove_photo' => '1']))
            ->assertSessionHasErrors('photo');
        Storage::disk('public')->assertExists($path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_photo_operations_do_not_modify_another_accounts_files(): void
    {
        $first = User::factory()->create();
        $firstPortfolio = app(SavePortfolio::class)->handle(app(EnsurePortfolio::class)->handle($first), [], $this->image());
        $second = User::factory()->create();
        $secondPortfolio = app(SavePortfolio::class)->handle(app(EnsurePortfolio::class)->handle($second), [], $this->image());

        $this->actingAs($first)->patch(route('admin.portfolio.update'), $this->data([
            'id' => $secondPortfolio->id, 'user_id' => $second->id, 'remove_photo' => '1',
        ]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($firstPortfolio->photo_path);
        Storage::disk('public')->assertExists($secondPortfolio->photo_path);
        $this->assertSame($secondPortfolio->photo_path, $secondPortfolio->fresh()->photo_path);

        app(PortfolioPhotoStorage::class)->delete($firstPortfolio->id, $secondPortfolio->photo_path);
        Storage::disk('public')->assertExists($secondPortfolio->photo_path);
    }

    public function test_failed_save_cleans_new_photo_and_keeps_previous_one(): void
    {
        $owner = User::factory()->create();
        $portfolio = app(SavePortfolio::class)->handle(app(EnsurePortfolio::class)->handle($owner), [], $this->image());
        $path = $portfolio->photo_path;

        try {
            app(SavePortfolio::class)->handle($portfolio, ['slug' => null], $this->image());
            $this->fail('O banco deveria rejeitar o slug nulo.');
        } catch (QueryException) {
            $this->assertSame($path, $portfolio->fresh()->photo_path);
            Storage::disk('public')->assertExists($path);
            $this->assertCount(1, Storage::disk('public')->allFiles());
        }
    }

    public function test_account_deletion_removes_photo_and_preserves_other_accounts_photo(): void
    {
        $first = User::factory()->create();
        $firstPortfolio = app(SavePortfolio::class)->handle(app(EnsurePortfolio::class)->handle($first), [], $this->image());
        $secondPortfolio = app(SavePortfolio::class)->handle(app(EnsurePortfolio::class)->handle(User::factory()->create()), [], $this->image());

        $this->actingAs($first)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/');
        Storage::disk('public')->assertMissing($firstPortfolio->photo_path);
        Storage::disk('public')->assertExists($secondPortfolio->photo_path);
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('original.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8V8AAAAASUVORK5CYII='));
    }

    private function data(array $overrides = []): array
    {
        return array_replace([
            'slug' => 'meu-portfolio', 'theme' => 'dark', 'primary_color' => '#123456',
            'professional_name' => 'Nome profissional', 'headline' => 'Desenvolvedor PHP',
            'bio' => 'Minha biografia.', 'contact_email' => 'publico@example.com',
            'github_url' => 'https://github.com/example', 'linkedin_url' => 'https://linkedin.com/in/example',
            'website_url' => 'https://example.com',
        ], $overrides);
    }
}
