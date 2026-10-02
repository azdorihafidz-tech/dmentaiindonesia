<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('caption')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('status', 20)->default('inactive');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
