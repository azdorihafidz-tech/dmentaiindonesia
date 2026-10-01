<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 50)->default('public');
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('file_size');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->string('folder', 60)->default('general')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
