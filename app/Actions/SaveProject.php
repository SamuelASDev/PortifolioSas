<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Models\Project;
use App\Services\ProjectCoverStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaveProject
{
    public function __construct(private ProjectCoverStorage $covers) {}

    public function handle(Portfolio $portfolio, array $attributes, ?Project $project = null, ?UploadedFile $cover = null, bool $removeCover = false): Project
    {
        $newPath = $cover ? $this->covers->store($portfolio->id, $cover) : null;
        $oldPath = null;

        try {
            $saved = DB::transaction(function () use ($portfolio, $attributes, $project, $newPath, $removeCover, &$oldPath) {
                $record = $project
                    ? $portfolio->projects()->lockForUpdate()->findOrFail($project->id)
                    : $portfolio->projects()->make();
                $oldPath = $record->cover_image;
                $record->fill($attributes);

                if ($newPath !== null || $removeCover) {
                    $record->cover_image = $newPath;
                }

                $record->save();

                return $record;
            });
        } catch (Throwable $exception) {
            $this->covers->delete($portfolio->id, $newPath);

            throw $exception;
        }

        if ($oldPath !== $saved->cover_image) {
            $this->covers->delete($portfolio->id, $oldPath);
        }

        return $saved;
    }
}
