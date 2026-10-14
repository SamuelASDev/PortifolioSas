<?php

namespace App\Models;

use App\Services\ProjectCoverStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'title', 'description', 'cover_image',
        'link_demo', 'link_github', 'is_featured', 'display_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'display_order' => 'integer',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => app(ProjectCoverStorage::class)->url($this->cover_image));
    }
}
