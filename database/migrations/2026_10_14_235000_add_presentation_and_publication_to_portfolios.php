<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('professional_name')->nullable();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('is_published')->default(false);
        });

        DB::table('portfolios')->update([
            'professional_name' => DB::raw('(SELECT name FROM users WHERE users.id = portfolios.user_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn([
                'professional_name', 'headline', 'bio', 'photo_path', 'contact_email',
                'github_url', 'linkedin_url', 'website_url', 'is_published',
            ]);
        });
    }
};
