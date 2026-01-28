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
        Schema::create('subscription_connectivities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            // TODO: Add ODP relation later when ODP module is ready
            // $table->foreignId('odp_id')->nullable(); 

            // Relies on Router/Server defined at Branch level for credential source?
            // Actually, we need to know WHICH router this user is on.
            // Let's add router_id here. BUT 'routers' table is creating in separate migration?
            // Yes, routers table exists.
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();

            $table->string('ip_address')->nullable();
            $table->string('ip_type')->default('dynamic'); // dynamic, static

            $table->string('pppoe_user')->nullable();
            $table->text('pppoe_secret')->nullable()->comment('Encrypted');

            $table->string('ont_sn')->nullable()->comment('Modem Serial Number');
            $table->string('router_model')->nullable();
            $table->integer('vlan_id')->nullable();

            $table->string('signal_rx')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_connectivities');
    }
};
