<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->text('message');
            $table->jsonb('recipients')->default('[]');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->string('provider', 50);
            $table->decimal('cost', 10, 4)->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'partial'])->default('pending');
            $table->string('provider_batch_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'sms_batches_tenant_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_batches');
    }
};
