<?php

namespace App\Models;

use App\Services\PortfolioPhotoStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'slug', 'theme', 'primary_color', 'professional_name', 'headline',
        'bio', 'photo_path', 'contact_email', 'github_url', 'linkedin_url', 'website_url',
        'is_published', 'show_featured_carousel', 'appearance',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'show_featured_carousel' => 'boolean', 'appearance' => 'array'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => app(PortfolioPhotoStorage::class)->url($this->photo_path));
    }

    protected function gmailComposeUrl(): Attribute
    {
        return Attribute::get(fn () => $this->contact_email
            ? 'https://mail.google.com/mail/?'.http_build_query([
                'view' => 'cm', 'fs' => '1', 'to' => $this->contact_email,
            ], '', '&', PHP_QUERY_RFC3986)
            : null);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
