<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            if (! Schema::hasColumn('roles', 'custom_department_ids')) {
                $table->json('custom_department_ids')->nullable()->after('data_scope');
            }

            if (! Schema::hasColumn('roles', 'custom_user_ids')) {
                $table->json('custom_user_ids')->nullable()->after('custom_department_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            if (Schema::hasColumn('roles', 'custom_user_ids')) {
                $table->dropColumn('custom_user_ids');
            }

            if (Schema::hasColumn('roles', 'custom_department_ids')) {
                $table->dropColumn('custom_department_ids');
            }
        });
    }
};
