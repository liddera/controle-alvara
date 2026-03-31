<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('alvara_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('trigger_type', 32)->default('manual');
            $table->string('channel', 32);
            $table->string('destination_name')->nullable();
            $table->string('destination_email')->nullable();
            $table->string('destination_phone', 32)->nullable();
            $table->string('current_status', 32)->default('enviando');
            $table->unsignedTinyInteger('status_rank')->default(0);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->json('summary_payload')->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'channel']);
            $table->index(['owner_id', 'current_status']);
            $table->index(['alvara_id', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_dispatches');
    }
};
