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
        Schema::create('subscription_topology_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_topology_id')->constrained('subscription_topologies')->onDelete('cascade');
            $table->json('topology_data');
            $table->integer('version');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('change_summary', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subscription_topology_id', 'version'], 'topo_history_topo_version_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_topology_histories');
    }
};
