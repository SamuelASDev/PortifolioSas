<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Models\Project;
use App\Services\SharingImageStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaveSharing
{
    public function __construct(private SharingImageStorage $images) {}

    public function handle(Portfolio $portfolio, array $attributes, ?Project $project = null, ?UploadedFile $image = null, bool $removeImage = false): void
    {
        $newPath = $image ? $this->images->store($portfolio->id, $image) : null;
        $oldPath = null;
        try {
            DB::transaction(function () use ($portfolio, $attributes, $project, $newPath, $removeImage, &$oldPath) {
                $owner = Portfolio::where('user_id', $portfolio->user_id)->lockForUpdate()->findOrFail($portfolio->id);
                $record = $project ? $owner->projects()->lockForUpdate()->findOrFail($project->id) : $owner;
                $oldPath = $record->share_image_path;
                $record->share_title = $attributes['share_title'] ?? null;
                $record->share_description = $attributes['share_description'] ?? null;
                if ($newPath !== null || $removeImage) {
                    $record->share_image_path = $newPath;
                }
                $record->save();
            });
        } catch (Throwable $exception) {
            $this->images->delete($portfolio->id, $newPath);
            throw $exception;
        }
        if (($newPath !== null || $removeImage) && $oldPath !== $newPath) {
            $this->images->delete($portfolio->id, $oldPath);
        }
    }
}
