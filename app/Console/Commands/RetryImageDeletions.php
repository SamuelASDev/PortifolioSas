<?php

namespace App\Console\Commands;

use App\Services\ImageDeletionQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetryImageDeletions extends Command
{
    protected $signature = 'images:retry-deletions';

    protected $description = 'Tenta novamente exclusões de imagens que falharam no armazenamento';

    public function handle(ImageDeletionQueue $queue): int
    {
        $failed = 0;
        DB::table('pending_image_deletions')->where('retry_at', '<=', now())->orderBy('id')
            ->chunkById(100, function ($records) use ($queue, &$failed) {
                foreach ($records as $record) {
                    if (! $queue->attempt($record->id)) {
                        $failed++;
                    }
                }
            });
        $this->info($failed.' exclusão(ões) ainda pendente(s) nesta execução.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
