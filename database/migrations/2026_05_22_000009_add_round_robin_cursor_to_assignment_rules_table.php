<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_rules', function (Blueprint $table): void {
            if (! Schema::hasColumn('assignment_rules', 'round_robin_cursor')) {
                $table->foreignId('round_robin_cursor')->nullable()->after('user_ids')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignment_rules', function (Blueprint $table): void {
            if (Schema::hasColumn('assignment_rules', 'round_robin_cursor')) {
                $table->dropColumn('round_robin_cursor');
            }
        });
    }
};
