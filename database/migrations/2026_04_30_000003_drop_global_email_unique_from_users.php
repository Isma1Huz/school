<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the global unique constraint on users.email so that two tenants
 * can have users with the same email address. The per-tenant uniqueness
 * is enforced by the (tenant_id, email) composite index added in
 * extend_users_table. Super-admin / SOC users (tenant_id = NULL) are
 * still constrained via that composite index — most database engines allow
 * multiple rows with NULL in one column of a composite unique key (treating
 * each NULL as distinct), which is the desired behaviour here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
