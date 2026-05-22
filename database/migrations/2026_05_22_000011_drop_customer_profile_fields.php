<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['registered_address', 'website', 'industry', 'company_size', 'annual_revenue'] as $column) {
            if (! Schema::hasColumn('customers', $column)) {
                continue;
            }

            Schema::table('customers', function (Blueprint $table) use ($column): void {
                $table->dropColumn($column);
            });
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'registered_address')) {
                $table->string('registered_address', 500)->nullable();
            }

            if (! Schema::hasColumn('customers', 'website')) {
                $table->string('website')->nullable();
            }

            if (! Schema::hasColumn('customers', 'industry')) {
                $table->string('industry', 100)->nullable();
            }

            if (! Schema::hasColumn('customers', 'company_size')) {
                $table->string('company_size', 50)->nullable();
            }

            if (! Schema::hasColumn('customers', 'annual_revenue')) {
                $table->decimal('annual_revenue', 15, 2)->nullable();
            }
        });
    }
};
