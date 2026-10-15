<?php

namespace Tests\Feature;

use App\Services\ProjectCoverStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ImageDeletionRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_deletion_survives_and_is_retried_only_when_due(): void
    {
        $disk = Storage::fake('public');
        $path = 'portfolios/123/covers/example.png';
        $disk->put($path, 'image');
        $manager = Storage::getFacadeRoot();
        $unavailable = Mockery::mock();
        $unavailable->shouldReceive('delete')->once()->with($path)->andReturn(false);
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($unavailable);
        app(ProjectCoverStorage::class)->delete(123, $path);
        Storage::swap($manager);

        $this->assertDatabaseHas('pending_image_deletions', ['path' => $path, 'attempts' => 1]);
        $this->artisan('images:retry-deletions')->assertSuccessful();
        $disk->assertExists($path);
        $this->travel(2)->minutes();
        $this->artisan('images:retry-deletions')->assertSuccessful();
        $disk->assertMissing($path);
        $this->assertDatabaseCount('pending_image_deletions', 0);
    }

    public function test_deletion_queue_cannot_delete_another_collection_or_escape_the_portfolio(): void
    {
        $disk = Storage::fake('public');
        $path = 'portfolios/456/covers/keep.png';
        $disk->put($path, 'image');
        app(ProjectCoverStorage::class)->delete(123, $path);
        $disk->assertExists($path);
        $this->assertDatabaseCount('pending_image_deletions', 0);
        DB::table('pending_image_deletions')->insert([
            'disk' => 'public', 'path' => '../private/keep.png', 'retry_at' => now(),
        ]);
        $this->artisan('images:retry-deletions')->assertFailed();
        $this->assertDatabaseHas('pending_image_deletions', ['path' => '../private/keep.png', 'attempts' => 1]);
        $disk->assertExists($path);
    }

    public function test_missing_file_is_an_idempotent_success(): void
    {
        Storage::fake('public');
        app(ProjectCoverStorage::class)->delete(123, 'portfolios/123/covers/missing.png');
        $this->assertDatabaseCount('pending_image_deletions', 0);
    }

    public function test_database_outage_does_not_prevent_cleanup_after_a_failed_save(): void
    {
        $disk = Storage::fake('public');
        $path = 'portfolios/123/covers/rollback.png';
        $disk->put($path, 'image');
        $manager = DB::getFacadeRoot();
        DB::shouldReceive('table')->once()->with('pending_image_deletions')->andThrow(new \RuntimeException('Banco indisponível'));
        try {
            app(ProjectCoverStorage::class)->delete(123, $path);
        } finally {
            DB::swap($manager);
        }
        $disk->assertMissing($path);
    }
}
