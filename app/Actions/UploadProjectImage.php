<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Services\ProjectAttachmentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UploadProjectImage
{
    public function __construct(private ProjectAttachmentStorage $storage) {}

    public function handle(Portfolio $portfolio, UploadedFile $image, string $token, ?Project $project): ProjectMedia
    {
        $path = null;
        try {
            return DB::transaction(function () use ($portfolio, $image, $token, $project, &$path) {
                Portfolio::query()->lockForUpdate()->findOrFail($portfolio->id);
                if ($project) {
                    $portfolio->projects()->lockForUpdate()->findOrFail($project->id);
                }
                $all = ProjectMedia::where('portfolio_id', $portfolio->id);
                $batch = (clone $all)->where(function ($query) use ($token, $project) {
                    $query->where('upload_token', $token);
                    if ($project) {
                        $query->orWhere('project_id', $project->id);
                    }
                });
                if ($batch->count() >= config('portfolio.attachments.max_per_project')
                    || $all->count() >= config('portfolio.attachments.max_portfolio_files')
                    || $all->sum('size') + $image->getSize() > config('portfolio.attachments.max_portfolio_bytes')) {
                    throw ValidationException::withMessages(['image' => 'O limite de imagens foi atingido. Remova anexos que não utiliza e salve o projeto.']);
                }
                $path = $this->storage->store($portfolio->id, $image);

                return ProjectMedia::create([
                    'portfolio_id' => $portfolio->id, 'project_id' => $project?->id, 'upload_token' => $token,
                    'path' => $path, 'mime_type' => $image->getMimeType(), 'size' => $image->getSize(),
                ]);
            });
        } catch (Throwable $exception) {
            $this->storage->delete($portfolio->id, $path);
            throw $exception;
        }
    }
}
