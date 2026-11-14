<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->text('about')->nullable();
            $table->text('contact_intro')->nullable();
            $table->string('location', 120)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('instagram_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', fn (Blueprint $table) => $table->dropColumn(['about', 'contact_intro', 'location', 'whatsapp', 'instagram_url']));
    }
};
