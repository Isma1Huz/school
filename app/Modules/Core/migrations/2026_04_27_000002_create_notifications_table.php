<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('type');
            $table->jsonb('data')->default('{}');
            $table->enum('channel', ['in_app', 'sms', 'email', 'push'])->default('in_app');
            $table->enum('status', ['pending', 'sent', 'failed', 'read'])->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(
                ['tenant_id', 'notifiable_type', 'notifiable_id', 'read_at'],
                'notif_tenant_notifiable_read_index'
            );
            $table->index(
                ['tenant_id', 'status', 'channel'],
                'notif_tenant_status_channel_index'
            );
            $table->index(
                ['tenant_id', 'scheduled_at'],
                'notif_tenant_scheduled_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
