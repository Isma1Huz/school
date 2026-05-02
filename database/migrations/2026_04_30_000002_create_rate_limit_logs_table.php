<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_limit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address');
            $table->string('endpoint');
            $table->string('method', 10);
            $table->enum('rate_limit_type', ['token_bucket', 'sliding_window']);
            $table->unsignedInteger('requests_count');
            $table->unsignedInteger('limit_value');
            $table->timestamp('blocked_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'blocked_at']);
            $table->index(['ip_address', 'blocked_at']);

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_limit_logs');
    }
};
