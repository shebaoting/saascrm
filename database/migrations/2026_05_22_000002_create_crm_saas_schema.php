<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->string('short_name', 100)->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->string('country_code', 10)->default('CN');
            $table->string('timezone', 64)->default('Asia/Shanghai');
            $table->string('currency', 10)->default('CNY');
            $table->string('contact_name', 100)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('status', 30)->default('trial')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 100)->unique();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_leads')->nullable();
            $table->unsignedInteger('max_customers')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();
            $table->unsignedInteger('max_custom_fields')->nullable();
            $table->unsignedInteger('max_automation_rules')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->foreignId('plan_id')->index();
            $table->string('status', 30)->default('trialing')->index();
            $table->string('billing_cycle', 20)->default('manual');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_user', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->index();
            $table->foreignId('user_id')->index();
            $table->string('member_name', 100)->nullable();
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id'], 'tenant_user_unique');
        });

        Schema::create('tenant_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->json('role_ids')->nullable();
            $table->json('department_ids')->nullable();
            $table->string('token', 100)->unique();
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('invited_by')->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: true, audit: false);
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->index();
            $table->integer('sort_order')->default(0);
            $table->unique(['tenant_id', 'parent_id', 'name'], 'departments_tenant_parent_name_unique');
        });

        Schema::create('department_user', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->index();
            $table->foreignId('department_id')->index();
            $table->foreignId('user_id')->index();
            $table->boolean('is_leader')->default(false);
            $table->boolean('main_department')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'department_id', 'user_id'], 'department_user_unique');
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('label')->nullable();
            $table->string('group', 100)->nullable()->index();
            $table->timestamps();
            $table->unique(['name', 'guard_name'], 'permissions_name_guard_unique');
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('data_scope', 50)->default('all');
            $table->timestamps();
            $table->unique(['tenant_id', 'name', 'guard_name'], 'roles_tenant_name_guard_unique');
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->foreignId('permission_id')->index();
            $table->foreignId('role_id')->index();
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_primary');
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->index();
            $table->foreignId('role_id')->index();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_type', 'model_id'], 'model_has_roles_model_index');
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_primary');
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'key'], 'settings_tenant_key_unique');
        });

        Schema::create('areas', function (Blueprint $table): void {
            $table->id();
            $table->string('pid', 20)->index();
            $table->integer('deep')->default(0);
            $table->string('name');
            $table->string('pinyin_prefix', 50)->nullable();
            $table->string('pinyin')->nullable();
            $table->string('ext_id', 50)->nullable()->index();
            $table->string('ext_name')->nullable();
        });

        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->string('company_name')->nullable()->index();
            $table->string('contact_name', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('wechat_id', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('area_id', 20)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('source', 100)->nullable()->index();
            $table->json('tags')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->string('qualification_status', 30)->nullable();
            $table->integer('score')->default(0);
            $table->foreignId('owner_user_id')->nullable()->index();
            $table->timestamp('pool_entered_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('next_activity_at')->nullable()->index();
            $table->foreignId('converted_customer_id')->nullable()->index();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by')->nullable()->index();
            $table->string('lost_reason')->nullable();
            $table->json('custom_fields')->nullable();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'owner_user_id']);
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'email']);
        });

        Schema::create('lead_score_rules', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->string('name');
            $table->string('field', 100);
            $table->string('operator', 50);
            $table->json('value')->nullable();
            $table->integer('score')->default(0);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('assignment_rules', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->string('name');
            $table->string('target_type', 30)->default('lead');
            $table->string('method', 30)->default('round_robin');
            $table->foreignId('department_id')->nullable()->index();
            $table->json('user_ids')->nullable();
            $table->unsignedInteger('max_per_user_daily')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('assignment_rule_conditions', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->foreignId('assignment_rule_id')->index();
            $table->string('field', 100);
            $table->string('operator', 50);
            $table->json('value')->nullable();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->string('name')->index();
            $table->string('short_name', 100)->nullable();
            $table->string('customer_type', 30)->default('company');
            $table->string('lifecycle_stage', 30)->default('new')->index();
            $table->foreignId('owner_user_id')->nullable()->index();
            $table->string('source', 100)->nullable();
            $table->json('tags')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('area_id', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->timestamp('pool_entered_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('next_activity_at')->nullable()->index();
            $table->timestamp('first_order_at')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->json('custom_fields')->nullable();
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'owner_user_id']);
            $table->index(['tenant_id', 'lifecycle_stage']);
        });

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->foreignId('customer_id')->index();
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('wechat_id', 100)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('avatar', 500)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->json('custom_fields')->nullable();
            $table->index(['tenant_id', 'customer_id']);
        });

        Schema::create('customer_users', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->index();
            $table->foreignId('customer_id')->index();
            $table->foreignId('user_id')->index();
            $table->string('role', 30)->default('assistant');
            $table->foreignId('assigned_by')->nullable()->index();
            $table->timestamp('assigned_at');
            $table->unique(['tenant_id', 'customer_id', 'user_id', 'role'], 'customer_users_unique');
        });

        Schema::create('customer_transfer_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->string('target_type', 30);
            $table->unsignedBigInteger('target_id');
            $table->foreignId('from_user_id')->nullable()->index();
            $table->foreignId('to_user_id')->nullable()->index();
            $table->string('reason')->nullable();
            $table->foreignId('operated_by')->index();
            $table->timestamp('created_at')->nullable();
            $table->index(['tenant_id', 'target_type', 'target_id'], 'customer_transfer_target_index');
        });

        Schema::create('customer_pool_rules', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->string('target_type', 30);
            $table->string('name');
            $table->unsignedInteger('inactive_days')->default(30);
            $table->unsignedInteger('protect_days')->default(7);
            $table->unsignedInteger('max_claim_daily')->nullable();
            $table->json('department_ids')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('customer_pool_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->string('target_type', 30);
            $table->unsignedBigInteger('target_id');
            $table->string('action', 30);
            $table->foreignId('from_user_id')->nullable()->index();
            $table->foreignId('to_user_id')->nullable()->index();
            $table->string('reason')->nullable();
            $table->foreignId('operated_by')->nullable()->index();
            $table->timestamp('created_at')->nullable();
            $table->index(['tenant_id', 'target_type', 'target_id'], 'customer_pool_target_index');
        });

        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->foreignId('lead_id')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->index();
            $table->foreignId('contact_id')->nullable()->index();
            $table->foreignId('opportunity_id')->nullable()->index();
            $table->string('type', 30)->default('note')->index();
            $table->string('direction', 30)->nullable();
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamp('next_follow_at')->nullable()->index();
            $table->foreignId('owner_user_id')->index();
            $table->index(['tenant_id', 'customer_id', 'occurred_at']);
            $table->index(['tenant_id', 'lead_id', 'occurred_at']);
        });

        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->foreignId('lead_id')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->index();
            $table->foreignId('contact_id')->nullable()->index();
            $table->foreignId('opportunity_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('due_at')->nullable()->index();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('creator_id')->index();
            $table->foreignId('assignee_id')->index();
            $table->string('status', 30)->default('not_started')->index();
            $table->string('priority', 20)->default('normal')->index();
        });

        Schema::create('pipelines', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->string('name');
            $table->string('description', 1000)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
        });

        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->foreignId('pipeline_id')->index();
            $table->string('name');
            $table->unsignedInteger('probability')->default(0);
            $table->string('stage_type', 30)->default('open')->index();
            $table->json('required_fields')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('opportunities', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->foreignId('customer_id')->index();
            $table->foreignId('contact_id')->nullable()->index();
            $table->foreignId('pipeline_id')->index();
            $table->foreignId('pipeline_stage_id')->index();
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('closed_amount', 12, 2)->nullable();
            $table->unsignedInteger('probability')->default(0);
            $table->string('forecast_category', 30)->default('pipeline')->index();
            $table->date('expected_close_date')->nullable()->index();
            $table->foreignId('responsible_user_id')->nullable()->index();
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->string('lost_remarks', 1000)->nullable();
            $table->string('invalid_reason')->nullable();
            $table->string('invalid_remarks', 1000)->nullable();
            $table->string('notes', 1000)->nullable();
        });

        Schema::create('opportunity_stage_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->foreignId('opportunity_id')->index();
            $table->foreignId('from_stage_id')->nullable()->index();
            $table->foreignId('to_stage_id')->index();
            $table->foreignId('changed_by')->index();
            $table->timestamp('changed_at')->index();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('notes', 1000)->nullable();
        });

        Schema::create('sales_targets', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->string('target_type', 30)->default('user')->index();
            $table->unsignedBigInteger('target_id')->nullable()->index();
            $table->string('period_type', 20)->default('month');
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->decimal('target_amount', 12, 2)->default(0);
            $table->decimal('target_payment_amount', 12, 2)->default(0);
            $table->unsignedInteger('target_customer_count')->default(0);
        });

        Schema::create('product_groups', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->string('name');
            $table->integer('sort_order')->default(0);
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->string('name');
            $table->foreignId('group_id')->nullable()->index();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->longText('details')->nullable();
            $table->boolean('is_on_sale')->default(true);
            $table->text('internal_notes')->nullable();
        });

        Schema::create('product_skus', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->foreignId('product_id')->index();
            $table->string('sku_code', 100);
            $table->json('specifications')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unique(['tenant_id', 'sku_code'], 'product_skus_tenant_code_unique');
        });

        Schema::create('price_books', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->string('name');
            $table->string('code', 100);
            $table->string('currency', 10)->default('CNY');
            $table->string('customer_level', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unique(['tenant_id', 'code'], 'price_books_tenant_code_unique');
        });

        Schema::create('price_book_items', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->foreignId('price_book_id')->index();
            $table->foreignId('product_sku_id')->index();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('min_price', 10, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
        });

        Schema::create('quotes', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->string('quote_number', 100);
            $table->unsignedInteger('version')->default(1);
            $table->string('title');
            $table->foreignId('customer_id')->index();
            $table->foreignId('contact_id')->nullable()->index();
            $table->foreignId('opportunity_id')->nullable()->index();
            $table->foreignId('user_id')->index();
            $table->foreignId('price_book_id')->nullable()->index();
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('total_profit', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('profit_margin', 6, 2)->default(0);
            $table->string('status', 50)->default('draft')->index();
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('pdf_path', 1000)->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->unique(['tenant_id', 'quote_number'], 'quotes_tenant_number_unique');
        });

        Schema::create('quote_items', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->foreignId('quote_id')->index();
            $table->foreignId('product_id')->index();
            $table->foreignId('product_sku_id')->index();
            $table->string('product_name');
            $table->string('sku_code', 100)->nullable();
            $table->json('specifications')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('list_price', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
        });

        Schema::create('quote_approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->foreignId('quote_id')->index();
            $table->foreignId('requested_by')->index();
            $table->foreignId('approver_id')->nullable()->index();
            $table->string('status', 30)->default('pending')->index();
            $table->string('reason', 1000)->nullable();
            $table->string('approval_comment', 1000)->nullable();
            $table->timestamp('requested_at')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->string('order_number', 100);
            $table->foreignId('customer_id')->index();
            $table->foreignId('contact_id')->nullable()->index();
            $table->foreignId('opportunity_id')->nullable()->index();
            $table->foreignId('quote_id')->nullable()->index();
            $table->foreignId('employee_id')->nullable()->index();
            $table->decimal('subtotal_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('gross_profit', 12, 2)->default(0);
            $table->string('order_source', 30)->default('sales_entry');
            $table->string('order_status', 30)->default('draft')->index();
            $table->string('payment_status', 30)->default('unpaid')->index();
            $table->timestamp('ordered_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->unique(['tenant_id', 'order_number'], 'orders_tenant_number_unique');
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->foreignId('order_id')->index();
            $table->foreignId('product_id')->index();
            $table->foreignId('product_sku_id')->nullable()->index();
            $table->string('product_name');
            $table->string('sku_code', 100)->nullable();
            $table->json('specifications')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('subtotal_amount', 12, 2)->default(0);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->foreignId('order_id')->index();
            $table->date('plan_date')->nullable()->index();
            $table->timestamp('received_at')->nullable()->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status', 30)->default('pending')->index();
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_no', 100)->nullable();
            $table->string('notes', 1000)->nullable();
        });

        Schema::create('order_expenses', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table);
            $table->foreignId('order_id')->index();
            $table->date('expense_date')->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('category', 100)->nullable();
            $table->string('reason', 1000);
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
        });

        Schema::create('kb_categories', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->index();
            $table->integer('sort_order')->default(0);
        });

        Schema::create('kb_articles', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->string('title');
            $table->longText('content');
            $table->foreignId('kb_category_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('published_at')->nullable();
        });

        Schema::create('custom_fields', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->string('model_type', 50);
            $table->string('group_name', 100)->nullable();
            $table->string('type', 50);
            $table->string('identifier', 100);
            $table->string('name');
            $table->integer('sort_order')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_list_visible')->default(false);
            $table->boolean('is_show_in_tracking')->default(false);
            $table->json('data')->nullable();
            $table->unique(['tenant_id', 'model_type', 'identifier'], 'custom_fields_unique');
        });

        Schema::create('custom_field_layouts', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->string('model_type', 50);
            $table->foreignId('role_id')->nullable()->index();
            $table->json('layout');
        });

        Schema::create('attachments', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, audit: false);
            $table->string('path', 1000);
            $table->string('disk', 50)->default('local');
            $table->foreignId('user_id')->index();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('extension', 30)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->index(['tenant_id', 'model_type', 'model_id'], 'attachments_tenant_model_index');
        });

        Schema::create('imports', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->timestamp('completed_at')->nullable();
            $table->string('file_name');
            $table->string('file_path', 1000);
            $table->string('importer');
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->foreignId('user_id')->index();
        });

        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->timestamp('completed_at')->nullable();
            $table->string('file_disk', 50)->default('local');
            $table->string('file_name')->nullable();
            $table->string('exporter');
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('successful_rows')->default(0);
            $table->foreignId('user_id')->index();
        });

        Schema::create('failed_import_rows', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->foreignId('import_id')->index();
            $table->json('data');
            $table->text('validation_error')->nullable();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_index');
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('action', 100)->index();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 1000)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['model_type', 'model_id'], 'audit_logs_model_index');
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->foreignId('tenant_id')->nullable()->index();
            $table->foreignId('permission_id')->index();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_type', 'model_id'], 'model_has_permissions_model_index');
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_primary');
        });

        Schema::create('automation_rules', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false);
            $table->string('name');
            $table->string('trigger_type', 100)->index();
            $table->string('target_type', 50)->index();
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
        });

        Schema::create('automation_actions', function (Blueprint $table): void {
            $table->id();
            $this->tenantBase($table, softDeletes: false, audit: false);
            $table->foreignId('automation_rule_id')->index();
            $table->string('action_type', 100);
            $table->json('payload')->nullable();
            $table->integer('sort_order')->default(0);
        });

        Schema::create('merge_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->index();
            $table->string('model_type', 50);
            $table->unsignedBigInteger('source_id')->index();
            $table->unsignedBigInteger('target_id')->index();
            $table->json('merged_fields')->nullable();
            $table->json('merged_relations')->nullable();
            $table->foreignId('merged_by')->index();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        foreach ([
            'merge_histories',
            'automation_actions',
            'automation_rules',
            'model_has_permissions',
            'audit_logs',
            'notifications',
            'failed_import_rows',
            'exports',
            'imports',
            'attachments',
            'custom_field_layouts',
            'custom_fields',
            'kb_articles',
            'kb_categories',
            'order_expenses',
            'payments',
            'order_items',
            'orders',
            'quote_approval_requests',
            'quote_items',
            'quotes',
            'price_book_items',
            'price_books',
            'product_skus',
            'products',
            'product_groups',
            'sales_targets',
            'opportunity_stage_histories',
            'opportunities',
            'pipeline_stages',
            'pipelines',
            'tasks',
            'activities',
            'customer_pool_histories',
            'customer_pool_rules',
            'customer_transfer_histories',
            'customer_users',
            'contacts',
            'customers',
            'assignment_rule_conditions',
            'assignment_rules',
            'lead_score_rules',
            'leads',
            'areas',
            'settings',
            'model_has_roles',
            'role_has_permissions',
            'roles',
            'permissions',
            'department_user',
            'departments',
            'tenant_invitations',
            'tenant_user',
            'tenant_subscriptions',
            'plans',
            'tenants',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function tenantBase(Blueprint $table, bool $softDeletes = true, bool $audit = true): void
    {
        $table->foreignId('tenant_id')->index();

        if ($audit) {
            $table->foreignId('created_by')->nullable()->index();
            $table->foreignId('updated_by')->nullable()->index();
        }

        $table->timestamps();

        if ($softDeletes) {
            $table->softDeletes();
        }
    }
};
