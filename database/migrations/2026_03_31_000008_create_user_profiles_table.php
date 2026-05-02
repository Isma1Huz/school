<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('profileable_type');
            $table->unsignedBigInteger('profileable_id');
            $table->jsonb('data')->default('{}');
            $table->timestamps();

            $table->unique(['user_id', 'profileable_type', 'profileable_id'], 'user_profiles_unique');
            $table->index(['profileable_type', 'profileable_id'], 'user_profiles_morphable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
