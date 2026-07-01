<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('reminder_type'); // before_due, after_due
            $table->integer('days_offset');
            $table->string('channel'); // email, whatsapp, both
            $table->timestamp('sent_at');
            $table->string('status')->default('sent'); // sent, failed, skipped
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Ensure we don't send the same reminder twice
            $table->unique(['invoice_id', 'reminder_type', 'days_offset']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
    }
};
