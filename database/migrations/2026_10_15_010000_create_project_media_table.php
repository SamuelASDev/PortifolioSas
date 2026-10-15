<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->uuid('upload_token')->nullable()->index();
            $table->string('path')->unique();
            $table->string('mime_type');
            $table->unsignedInteger('size');
            $table->timestamp('attached_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_media');
    }
};
