<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Models\Project;
use App\Models\ProjectMedia;
use App\Services\ProjectAttachmentStorage;
use App\Services\ProjectCoverStorage;
use App\Services\ProjectDescription;
use App\Services\ProjectMediaManager;
use App\Support\ProjectTags;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaveProject
{
    public function __construct(
        private ProjectCoverStorage $covers,
        private ProjectDescription $descriptions,
        private ProjectMediaManager $media,
        private ProjectAttachmentStorage $attachments,
        private ProjectTags $tags,
    ) {}

    public function handle(Portfolio $portfolio, array $attributes, ?Project $project = null, ?UploadedFile $cover = null, bool $removeCover = false): Project
    {
        $token = $attributes['media_token'] ?? null;
        $changesTags = array_key_exists('tags', $attributes);
        $tagText = $attributes['tags'] ?? null;
        unset($attributes['tags']);
        unset($attributes['media_token']);
        $changesDescription = array_key_exists('description', $attributes);
        if (! $changesDescription) {
            unset($attributes['description_format']);
        }
        $newPath = $cover ? $this->covers->store($portfolio->id, $cover) : null;
        $oldPath = null;
        $removed = collect();

        try {
            $saved = DB::transaction(function () use ($portfolio, $attributes, $project, $newPath, $removeCover, $token, $changesDescription, $changesTags, $tagText, &$oldPath, &$removed) {
                Portfolio::query()->lockForUpdate()->findOrFail($portfolio->id);
                $record = $project
                    ? $portfolio->projects()->lockForUpdate()->findOrFail($project->id)
                    : $portfolio->projects()->make();
                $oldPath = $record->cover_image;
                $data = $attributes;
                $ids = [];
                if ($changesDescription) {
                    $data['description_format'] ??= 'text';
                    if ($data['description_format'] === 'html') {
                        $available = $this->media->available($portfolio, $project, $token);
                        $clean = $this->descriptions->sanitize($data['description'], $available);
                        $data['description'] = $this->descriptions->hasContent($clean) ? $clean : null;
                        $ids = array_values(array_intersect($this->descriptions->mediaIds($clean), $available->modelKeys()));
                        if (count($ids) > config('portfolio.attachments.max_per_project')) {
                            throw ValidationException::withMessages(['description' => 'O projeto ultrapassou o limite de imagens. Remova algumas antes de salvar.']);
                        }
                    }
                }
                $record->fill($data);
                if ($newPath !== null || $removeCover) {
                    $record->cover_image = $newPath;
                }
                $record->save();
                if ($changesTags) {
                    $this->tags->sync($portfolio, $record, $tagText);
                }

                if ($changesDescription) {
                    if ($ids) {
                        ProjectMedia::where('portfolio_id', $portfolio->id)->whereIn('id', $ids)
                            ->update(['project_id' => $record->id, 'attached_at' => now(), 'upload_token' => null]);
                    }
                    $removed = ProjectMedia::where('portfolio_id', $portfolio->id)
                        ->where(function ($query) use ($record, $token) {
                            $query->where(fn ($attached) => $attached->where('project_id', $record->id)->whereNotNull('attached_at'));
                            if ($token) {
                                $query->orWhere(fn ($pending) => $pending->whereNull('attached_at')->where('upload_token', $token)
                                    ->where(fn ($owner) => $owner->whereNull('project_id')->orWhere('project_id', $record->id)));
                            }
                        })->whereNotIn('id', $ids)->get();
                    ProjectMedia::whereIn('id', $removed->modelKeys())->delete();
                }

                return $record;
            });
        } catch (Throwable $exception) {
            $this->covers->delete($portfolio->id, $newPath);
            throw $exception;
        }

        if ($oldPath !== $saved->cover_image) {
            $this->covers->delete($portfolio->id, $oldPath);
        }
        foreach ($removed as $media) {
            $this->attachments->delete($portfolio->id, $media->path);
        }

        return $saved;
    }
}
