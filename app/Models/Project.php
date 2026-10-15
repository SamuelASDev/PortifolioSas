<?php

namespace App\Models;

use App\Services\ProjectCoverStorage;
use App\Services\ProjectDescription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    private ?array $descriptionTextMemo = null;

    protected $fillable = [
        'portfolio_id', 'title', 'description', 'description_format', 'cover_image',
        'link_demo', 'link_github', 'is_featured', 'display_order', 'share_title', 'share_description', 'share_image_path', 'category',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'display_order' => 'integer',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (filled($filters['q'] ?? null)) {
            $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], trim($filters['q']));
            $query->whereRaw("title LIKE ? ESCAPE '!'", ['%'.$term.'%']);
        }
        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $query->where('is_featured', (string) $filters['featured'] === '1');
        }
        if (filled($filters['category'] ?? null)) {
            $query->where('category', $filters['category']);
        }
        if (filled($filters['tag'] ?? null)) {
            $query->whereHas('tags', fn ($tags) => $tags->where('tags.id', $filters['tag'])->whereColumn('tags.portfolio_id', 'projects.portfolio_id'));
        }

        return $query;
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
        return Attribute::get(function () {
            $source = [$this->description, $this->description_format];
            if ($this->descriptionTextMemo === null || $this->descriptionTextMemo[0] !== $source) {
                $this->descriptionTextMemo = [$source, app(ProjectDescription::class)->text(...$source)];
            }

            return $this->descriptionTextMemo[1];
        });
    }
}
