<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Models\Project;
use App\Services\ProjectCoverStorage;
use App\Services\ProjectDescription;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaveProject
{
    public function __construct(private ProjectCoverStorage $covers, private ProjectDescription $descriptions) {}

    public function handle(Portfolio $portfolio, array $attributes, ?Project $project = null, ?UploadedFile $cover = null, bool $removeCover = false): Project
    {
        if (array_key_exists('description', $attributes)) {
            $attributes['description_format'] ??= 'text';

            if ($attributes['description_format'] === 'html') {
                $clean = $this->descriptions->sanitize($attributes['description']);
                $attributes['description'] = $this->descriptions->text($clean, 'html') === '' ? null : $clean;
            }
        } else {
            unset($attributes['description_format']);
        }

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
