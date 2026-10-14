<?php

namespace App\Actions;

use App\Models\Portfolio;
use App\Services\PortfolioPhotoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SavePortfolio
{
    public function __construct(private PortfolioPhotoStorage $photos) {}

    public function handle(Portfolio $portfolio, array $attributes, ?UploadedFile $photo = null, bool $removePhoto = false): Portfolio
    {
        $newPath = $photo ? $this->photos->store($portfolio->id, $photo) : null;
        $oldPath = null;

        try {
            $saved = DB::transaction(function () use ($portfolio, $attributes, $newPath, $removePhoto, &$oldPath) {
                $record = Portfolio::where('user_id', $portfolio->user_id)->lockForUpdate()->findOrFail($portfolio->id);
                $oldPath = $record->photo_path;
                $record->fill($attributes);

                if ($record->is_published && blank($record->professional_name)) {
                    throw ValidationException::withMessages(['professional_name' => 'Um portfólio publicado precisa de um nome profissional.']);
                }

                if ($newPath !== null || $removePhoto) {
                    $record->photo_path = $newPath;
                }

                $record->save();

                return $record;
            });
        } catch (Throwable $exception) {
            $this->photos->delete($portfolio->id, $newPath);

            throw $exception;
        }

        if ($oldPath !== $saved->photo_path) {
            $this->photos->delete($portfolio->id, $oldPath);
        }

        return $saved;
    }
}
