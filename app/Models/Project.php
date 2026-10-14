<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id', 'title', 'description', 'cover_image', 
        'link_demo', 'link_github', 'is_featured', 'display_order'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }
}