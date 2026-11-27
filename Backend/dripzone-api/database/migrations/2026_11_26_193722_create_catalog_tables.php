<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('users', fn (Blueprint $t) => $t->string('role')->default('owner'));

    Schema::create('brands', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->string('slug')->unique();
        $t->boolean('is_own')->default(false); // marque propriétaire Drip Zone
        $t->string('logo_path')->nullable();
        $t->timestamps();
    });

    Schema::create('categories', function (Blueprint $t) {
        $t->id();
        $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
        $t->string('name');
        $t->string('slug')->unique();
        $t->unsignedSmallInteger('position')->default(0);
        $t->timestamps();
    });

    Schema::create('products', function (Blueprint $t) {
        $t->id();
        $t->foreignId('brand_id')->constrained();
        $t->foreignId('category_id')->constrained();
        $t->string('name');
        $t->string('slug')->unique();
        $t->text('description')->nullable();
        $t->string('availability_mode')->default('in_stock'); // in_stock | pre_order | hybrid
        $t->boolean('is_featured')->default(false);
        $t->boolean('is_active')->default(true);
        $t->timestamp('published_at')->nullable();
        $t->timestamps();
        $t->softDeletes();
        $t->index(['is_active', 'published_at']);
    });

    Schema::create('product_variants', function (Blueprint $t) {
        $t->id();
        $t->foreignId('product_id')->constrained()->cascadeOnDelete();
        $t->string('sku')->unique();
        $t->string('size')->nullable();
        $t->string('color')->nullable();
        $t->unsignedBigInteger('price_xof');
        $t->unsignedBigInteger('cost_xof')->nullable();
        $t->boolean('is_preorderable')->default(false);
        $t->unsignedSmallInteger('preorder_lead_days')->nullable();
        $t->boolean('is_active')->default(true);
        $t->timestamps();
        $t->unique(['product_id', 'size', 'color']);
    });

    Schema::create('product_images', function (Blueprint $t) {
        $t->id();
        $t->foreignId('product_id')->constrained()->cascadeOnDelete();
        $t->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
        $t->string('path');
        $t->string('alt')->nullable();
        $t->unsignedSmallInteger('position')->default(0);
        $t->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_tables');
    }
};
