<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('plans', 'max_imports_daily')) {
                $table->unsignedInteger('max_imports_daily')->nullable()->after('max_automation_rules');
            }

            if (! Schema::hasColumn('plans', 'max_exports_daily')) {
                $table->unsignedInteger('max_exports_daily')->nullable()->after('max_imports_daily');
            }
        });

        Schema::table('leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('leads', 'lead_number')) {
                $table->string('lead_number', 100)->nullable()->after('tenant_id');
                $table->unique(['tenant_id', 'lead_number'], 'leads_tenant_number_unique');
            }
        });

        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'customer_number')) {
                $table->string('customer_number', 100)->nullable()->after('tenant_id');
                $table->unique(['tenant_id', 'customer_number'], 'customers_tenant_number_unique');
            }
        });

        if (! Schema::hasTable('business_number_rules')) {
            Schema::create('business_number_rules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->index();
                $table->string('module', 50);
                $table->string('name')->nullable();
                $table->string('prefix', 50)->nullable();
                $table->string('pattern', 200)->default('{PREFIX}{YYYY}{MM}{DD}{SEQ}');
                $table->string('suffix', 50)->nullable();
                $table->unsignedSmallInteger('sequence_length')->default(4);
                $table->string('reset_period', 20)->default('daily');
                $table->unsignedInteger('current_sequence')->default(0);
                $table->string('last_sequence_key', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'module'], 'business_number_rules_tenant_module_unique');
            });
        }

        if (! Schema::hasTable('field_histories')) {
            Schema::create('field_histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->index();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->string('field', 100);
                $table->json('old_value')->nullable();
                $table->json('new_value')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['model_type', 'model_id', 'created_at'], 'field_histories_model_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('field_histories');
        Schema::dropIfExists('business_number_rules');

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropUnique('customers_tenant_number_unique');
            $table->dropColumn('customer_number');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropUnique('leads_tenant_number_unique');
            $table->dropColumn('lead_number');
        });

        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn(['max_imports_daily', 'max_exports_daily']);
        });
    }
};
