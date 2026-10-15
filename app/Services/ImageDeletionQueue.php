<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ImageDeletionQueue
{
    public function delete(string $disk, string $path): void
    {
        try {
            DB::table('pending_image_deletions')->insertOrIgnore([
                'disk' => $disk, 'path' => $path, 'retry_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $id = DB::table('pending_image_deletions')->where('disk', $disk)->where('path', $path)->value('id');
            $this->attempt($id);
        } catch (Throwable $exception) {
            // Uma falha do banco não deve impedir a limpeza de um upload após rollback.
            report($exception);
            try {
                if (Storage::disk($disk)->delete($path)) {
                    return;
                }
            } catch (Throwable $storageException) {
                report($storageException);
            }
            Log::error('Não foi possível excluir nem registrar a imagem para nova tentativa.', ['disk' => $disk, 'path' => $path]);
        }
    }

    public function attempt(int $id): bool
    {
        $record = DB::table('pending_image_deletions')->find($id);
        if (! $record) {
            return true;
        }

        try {
            if (! in_array($record->disk, array_column(config('portfolio'), 'disk'), true)
                || ! preg_match('#^portfolios/[1-9][0-9]*/(?:photos|covers|attachments|sharing)/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$#', $record->path)) {
                throw new RuntimeException('Destino recusado para exclusão de imagem.');
            }
            if (! Storage::disk($record->disk)->delete($record->path)) {
                throw new RuntimeException('O disco não confirmou a exclusão da imagem.');
            }
            DB::table('pending_image_deletions')->where('id', $id)->delete();

            return true;
        } catch (Throwable $exception) {
            DB::table('pending_image_deletions')->where('id', $id)->update([
                'attempts' => $record->attempts + 1,
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
                'retry_at' => now()->addMinutes(min(60, 2 ** min($record->attempts, 6))),
                'updated_at' => now(),
            ]);
            Log::warning('Exclusão de imagem pendente; uma nova tentativa foi agendada.', ['id' => $id, 'disk' => $record->disk, 'path' => $record->path]);

            return false;
        }
    }
}
