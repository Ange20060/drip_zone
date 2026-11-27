<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('warehouses', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->boolean('is_default')->default(false);
        $t->timestamps();
    });

    Schema::create('inventory', function (Blueprint $t) {
        $t->id();
        $t->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
        $t->foreignId('warehouse_id')->constrained();
        $t->unsignedInteger('quantity_on_hand')->default(0);
        $t->unsignedInteger('quantity_reserved')->default(0);
        $t->unsignedInteger('low_stock_threshold')->default(2);
        $t->timestamps();
        $t->unique(['variant_id', 'warehouse_id']);
    });
    DB::statement('ALTER TABLE inventory ADD CONSTRAINT chk_reserved CHECK (quantity_reserved <= quantity_on_hand)');

    // Journal immuable : jamais d'UPDATE, correction par mouvement compensatoire
    Schema::create('inventory_movements', function (Blueprint $t) {
        $t->id();
        $t->foreignId('variant_id')->constrained('product_variants');
        $t->foreignId('warehouse_id')->constrained();
        $t->string('type'); // receipt | sale | adjustment | reversal | return
        $t->integer('quantity');  // signé : + entrée, - sortie
        $t->nullableMorphs('reference'); // bon de réception, commande...
        $t->foreignId('reverses_movement_id')->nullable()->constrained('inventory_movements');
        $t->string('idempotency_key')->nullable()->unique(); // anti double réception
        $t->text('note')->nullable();
        $t->foreignId('created_by')->nullable()->constrained('users');
        $t->timestamp('created_at')->useCurrent();
    });

    Schema::create('stock_reservations', function (Blueprint $t) {
        $t->id();
        $t->foreignId('variant_id')->constrained('product_variants');
        $t->foreignId('warehouse_id')->constrained();
        $t->unsignedBigInteger('order_item_id')->nullable(); // FK ajoutée en Phase 08
        $t->unsignedInteger('quantity');
        $t->string('status')->default('active'); // active | consumed | released | expired
        $t->timestamp('expires_at')->nullable();
        $t->timestamps();
        $t->index(['status', 'expires_at']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_tables');
    }
};
