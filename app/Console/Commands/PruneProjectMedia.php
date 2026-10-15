<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\ProjectMedia;
use App\Services\ProjectAttachmentStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneProjectMedia extends Command
{
    protected $signature = 'project-media:prune';

    protected $description = 'Remove uploads temporários abandonados, sem apagar anexos salvos';

    public function handle(ProjectAttachmentStorage $storage): int
    {
        $cutoff = now()->subHours(config('portfolio.attachments.pending_hours'));
        $count = 0;
        ProjectMedia::whereNull('attached_at')->where('created_at', '<=', $cutoff)->chunkById(100, function ($batch) use ($storage, $cutoff, &$count) {
            foreach ($batch as $candidate) {
                $deleted = DB::transaction(function () use ($candidate, $cutoff) {
                    Portfolio::query()->lockForUpdate()->find($candidate->portfolio_id);
                    $record = ProjectMedia::whereNull('attached_at')->where('created_at', '<=', $cutoff)->lockForUpdate()->find($candidate->id);
                    $record?->delete();

                    return $record;
                });
                if ($deleted) {
                    $storage->delete($deleted->portfolio_id, $deleted->path);
                    $count++;
                }
            }
        });
        $this->info($count.' upload(s) temporário(s) removido(s).');

        return self::SUCCESS;
    }
}
