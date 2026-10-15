<?php

namespace App\Http\Controllers\Admin;

use App\Actions\UploadProjectImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadProjectMediaRequest;
use App\Models\Project;
use App\Services\VideoEmbed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ProjectMediaController extends Controller
{
    public function store(UploadProjectMediaRequest $request, UploadProjectImage $upload): JsonResponse
    {
        $project = $request->filled('project_id') ? Project::findOrFail($request->validated('project_id')) : null;
        if ($project) {
            Gate::authorize('update', $project);
        }
        $media = $upload->handle($request->user()->portfolio, $request->file('image'), $request->validated('upload_token'), $project);

        return response()->json(['id' => $media->id, 'url' => $media->url], 201);
    }

    public function video(Request $request, VideoEmbed $videos): JsonResponse
    {
        Gate::authorize('create', Project::class);
        $data = $request->validate(['url' => ['required', 'string', 'url:http,https', 'max:2048']]);
        $embed = $videos->resolve($data['url']);
        if (! $embed) {
            throw ValidationException::withMessages(['url' => 'Use um link público do YouTube ou Vimeo.']);
        }

        return response()->json($embed);
    }
}
