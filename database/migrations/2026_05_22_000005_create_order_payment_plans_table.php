<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_payment_plans')) {
            Schema::create('order_payment_plans', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->index();
                $table->foreignId('order_id')->index();
                $table->date('plan_date')->index();
                $table->decimal('plan_amount', 12, 2)->default(0);
                $table->decimal('received_amount', 12, 2)->default(0);
                $table->string('status', 30)->default('pending')->index();
                $table->string('notes', 1000)->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'order_id', 'plan_date'], 'order_payment_plans_order_date_index');
            });
        }

        Schema::table('payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('payments', 'payment_plan_id')) {
                $table->foreignId('payment_plan_id')->nullable()->after('order_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'payment_plan_id')) {
                $table->dropColumn('payment_plan_id');
            }
        });

        Schema::dropIfExists('order_payment_plans');
    }
};
