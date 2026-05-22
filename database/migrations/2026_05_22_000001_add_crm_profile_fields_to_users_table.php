<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 50)->nullable()->unique()->after('email');
            $table->string('avatar', 500)->nullable()->after('password');
            $table->string('gender', 20)->nullable()->after('avatar');
            $table->string('position', 100)->nullable()->after('gender');
            $table->string('telephone', 50)->nullable()->after('position');
            $table->string('locale', 20)->default('zh_CN')->after('telephone');
            $table->boolean('status')->default(true)->after('locale');
            $table->boolean('is_platform_admin')->default(false)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('is_platform_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'avatar',
                'gender',
                'position',
                'telephone',
                'locale',
                'status',
                'is_platform_admin',
                'last_login_at',
            ]);
        });
    }
};
