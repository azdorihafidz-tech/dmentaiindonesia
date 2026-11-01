<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->char('country_code', 2)->nullable()->after('visitor_hash');
            $table->string('country_name', 120)->nullable()->after('country_code');
            $table->index(['country_code', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->dropIndex(['country_code', 'viewed_at']);
            $table->dropColumn(['country_code', 'country_name']);
        });
    }
};
