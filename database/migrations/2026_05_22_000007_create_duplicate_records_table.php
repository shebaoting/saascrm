<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('duplicate_records')) {
            Schema::create('duplicate_records', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->index();
                $table->string('target_type', 50)->index();
                $table->unsignedBigInteger('target_id')->nullable()->index();
                $table->string('matched_type', 50)->index();
                $table->unsignedBigInteger('matched_id')->index();
                $table->string('field_name', 100)->index();
                $table->string('field_value', 255)->index();
                $table->string('status', 30)->default('pending')->index();
                $table->json('payload')->nullable();
                $table->foreignId('resolved_by')->nullable()->index();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'target_type', 'status'], 'duplicate_records_target_status_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_records');
    }
};
