<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            if (! Schema::hasColumn('quotes', 'source_quote_id')) {
                $table->foreignId('source_quote_id')->nullable()->after('version')->index();
            }

            if (! Schema::hasColumn('quotes', 'superseded_at')) {
                $table->timestamp('superseded_at')->nullable()->after('accepted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table): void {
            if (Schema::hasColumn('quotes', 'superseded_at')) {
                $table->dropColumn('superseded_at');
            }

            if (Schema::hasColumn('quotes', 'source_quote_id')) {
                $table->dropColumn('source_quote_id');
            }
        });
    }
};
