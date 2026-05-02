<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone', 50)->nullable()->after('email_verified_at');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('user_type', 50)->default('member')->after('avatar');
            $table->jsonb('preferences')->default('{}')->after('user_type');
            $table->jsonb('settings')->default('{}')->after('preferences');
            $table->boolean('is_active')->default(true)->after('settings');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->softDeletes();

            // Per-tenant email uniqueness (tenant_id + email)
            $table->unique(['tenant_id', 'email'], 'users_tenant_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_tenant_email_unique');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'tenant_id',
                'phone',
                'avatar',
                'user_type',
                'preferences',
                'settings',
                'is_active',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
