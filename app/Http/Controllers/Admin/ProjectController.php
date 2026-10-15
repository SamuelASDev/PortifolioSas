<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DeleteProject;
use App\Actions\SaveProject;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProjectRequest;
use App\Http\Requests\FilterProjectsRequest;
use App\Models\Project;
use App\Support\ProjectFilterOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(FilterProjectsRequest $request, ProjectFilterOptions $options): View
    {
        Gate::authorize('viewAny', Project::class);
        $portfolio = $request->user()->portfolio;
        $filters = $request->validated();
        $projects = $portfolio->projects()->with('tags')->filter($filters)
            ->orderBy('display_order')->orderBy('id')->paginate(10)->appends($filters);
        $projectCount = $portfolio->projects()->count();

        return view('admin.projects.index', compact('projects', 'filters', 'projectCount') + $options->forPortfolio($portfolio));
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return view('admin.projects.create', ['project' => new Project(['display_order' => 0, 'is_featured' => false])]);
    }

    public function store(SaveProjectRequest $request, SaveProject $saveProject): RedirectResponse
    {
        $saveProject->handle(
            $request->user()->portfolio,
            $request->safe()->except(['cover_image', 'remove_cover']),
            cover: $request->file('cover_image'),
        );

        return to_route('admin.projects.index')->with('status', 'Projeto criado com sucesso!');
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('admin.projects.edit', compact('project'));
    }

    public function update(SaveProjectRequest $request, Project $project, SaveProject $saveProject): RedirectResponse
    {
        $saveProject->handle(
            $request->user()->portfolio,
            $request->safe()->except(['cover_image', 'remove_cover']),
            $project,
            $request->file('cover_image'),
            $request->boolean('remove_cover'),
        );

        return to_route('admin.projects.index')->with('status', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project, DeleteProject $deleteProject): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $deleteProject->handle($project);

        return to_route('admin.projects.index')->with('status', 'Projeto excluído com sucesso!');
    }
}
