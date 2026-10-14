<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProjectCoverStorage
{
    public function url(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $disk = config('portfolio.covers.disk');

        return config('filesystems.disks.'.$disk.'.driver') === 'local'
            ? asset('storage/'.$path)
            : Storage::disk($disk)->url($path);
    }

    public function store(int $portfolioId, UploadedFile $file): string
    {
        $path = $file->store($this->directory($portfolioId), config('portfolio.covers.disk'));

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Não foi possível armazenar a capa do projeto.');
        }

        return $path;
    }

    public function delete(int $portfolioId, ?string $path): void
    {
        if ($path === null) {
            return;
        }

        // Nunca apaga arquivos fora da pasta de capas do proprietário.
        if (dirname($path) !== $this->directory($portfolioId)
            || ! preg_match('/^[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$/', basename($path))) {
            Log::warning('Caminho de capa recusado para exclusão.', ['portfolio_id' => $portfolioId, 'path' => $path]);

            return;
        }

        try {
            if (Storage::disk(config('portfolio.covers.disk'))->delete($path)) {
                return;
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        // A operação no banco já foi concluída; registra falhas para limpeza posterior.
        Log::warning('Não foi possível excluir uma capa do disco.', ['portfolio_id' => $portfolioId, 'path' => $path]);
    }

    private function directory(int $portfolioId): string
    {
        return 'portfolios/'.$portfolioId.'/covers';
    }
}
