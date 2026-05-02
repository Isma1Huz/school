<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_tenant', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->jsonb('settings')->default('{}');
            $table->jsonb('permissions_override')->default('{}');
            $table->timestamps();
            $table->primary(['module_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_tenant');
    }
};
