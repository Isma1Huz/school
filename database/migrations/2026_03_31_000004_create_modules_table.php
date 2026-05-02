<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 100)->unique();
            $table->string('icon', 100)->nullable();
            $table->text('description')->nullable();
            $table->jsonb('allowed_tenant_types')->default('["school"]');
            $table->jsonb('dependencies')->default('[]');
            $table->jsonb('default_permissions')->default('[]');
            $table->jsonb('settings_schema')->default('{}');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_core')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_globally_disabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
