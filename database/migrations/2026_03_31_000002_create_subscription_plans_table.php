<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 100)->unique();
            $table->text('description')->nullable();
            $table->jsonb('applicable_tenant_types')->default('["school"]');
            $table->decimal('price_monthly', 15, 2)->nullable();
            $table->decimal('price_yearly', 15, 2)->nullable();
            $table->integer('max_users')->default(0);
            $table->integer('max_storage_mb')->default(0);
            $table->jsonb('features')->default('[]');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
