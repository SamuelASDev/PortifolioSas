<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Services\ProjectDescription;
use Mockery;
use Tests\TestCase;

class ProjectSummaryCacheTest extends TestCase
{
    public function test_summary_is_reused_but_description_and_format_changes_invalidate_it(): void
    {
        $service = Mockery::mock(ProjectDescription::class);
        $service->shouldReceive('text')->once()->with('<p>Primeiro</p>', 'html')->andReturn('Primeiro');
        $service->shouldReceive('text')->once()->with('<p>Segundo</p>', 'html')->andReturn('Segundo');
        $service->shouldReceive('text')->once()->with('<p>Segundo</p>', 'text')->andReturn('<p>Segundo</p>');
        $this->app->instance(ProjectDescription::class, $service);
        $project = new Project(['description' => '<p>Primeiro</p>', 'description_format' => 'html']);
        $this->assertSame('Primeiro', $project->description_text);
        $this->assertSame('Primeiro', $project->description_text);
        $project->description = '<p>Segundo</p>';
        $this->assertSame('Segundo', $project->description_text);
        $project->description_format = 'text';
        $this->assertSame('<p>Segundo</p>', $project->description_text);
    }
}
