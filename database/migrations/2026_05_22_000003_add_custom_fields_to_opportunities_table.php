<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            if (! Schema::hasColumn('opportunities', 'custom_fields')) {
                $table->json('custom_fields')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table): void {
            if (Schema::hasColumn('opportunities', 'custom_fields')) {
                $table->dropColumn('custom_fields');
            }
        });
    }
};
