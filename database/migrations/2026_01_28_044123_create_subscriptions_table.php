<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->string('subscription_code')->unique();
            $table->string('status')->default('pending'); // pending, active, suspended, terminated

            $table->date('installed_at')->nullable();
            $table->integer('billing_cycle_day')->nullable()->comment('Day of month for billing');
            $table->date('next_billing_date')->nullable();

            $table->date('terminated_at')->nullable();
            $table->text('termination_reason')->nullable();

            $table->decimal('price_at_subscription', 15, 2)->nullable()->comment('Price lock if different from current package price');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
