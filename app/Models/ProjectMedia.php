<?php

namespace App\Models;

use App\Services\ProjectAttachmentStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMedia extends Model
{
    use HasUuids;

    protected $table = 'project_media';

    protected $fillable = ['portfolio_id', 'project_id', 'upload_token', 'path', 'mime_type', 'size', 'attached_at'];

    protected function casts(): array
    {
        return ['attached_at' => 'datetime', 'size' => 'integer'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => app(ProjectAttachmentStorage::class)->url($this->path));
    }
}
