<?php

namespace App\Models;

use App\Services\ProjectCoverStorage;
use App\Services\ProjectDescription;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'title', 'description', 'description_format', 'cover_image',
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

    protected function descriptionHtml(): Attribute
    {
        return Attribute::get(fn () => app(ProjectDescription::class)->html(
            $this->description, $this->description_format,
            $this->description_format === 'html' ? $this->attachments->whereNotNull('attached_at') : [],
        ));
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectMedia::class);
    }

    protected function descriptionText(): Attribute
    {
        return Attribute::get(fn () => app(ProjectDescription::class)->text($this->description, $this->description_format));
    }
}
