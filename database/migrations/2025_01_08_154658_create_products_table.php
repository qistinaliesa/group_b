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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Removed extra space in ' name'
        $table->string('slug')->unique();
        $table->string('short_description')->nullable(); // Removed extra space in 'short _description'
        $table->text('description'); // Fixed mismatched quotes
        $table->decimal('regular_price', 10, 2); // Added precision and scale for decimal
        $table->decimal('sale_price', 10, 2)->nullable(); // Fixed mismatched quotes and added precision
        $table->string('SKU');
        $table->enum('stock_status', ['instock', 'outofstock']); // Fixed mismatched quotes and removed space in 'stock status'
        $table->boolean('featured')->default(false);
        $table->unsignedInteger('quantity')->default(10);
        $table->string('image')->nullable();
        $table->text('images')->nullable(); // Added parentheses for nullable()
        $table->BigInteger('category_id')->unsigned()->nullable(); // Fixed incorrect use of colon (:)
        $table->BigInteger('brand_id')->unsigned()->nullable(); // Fixed incorrect use of colon (:)
        $table->timestamps();
        $table->foreign('category_id')
            ->references('id')
            ->on('categories')
            ->onDelete('cascade');
        $table->foreign('brand_id')
            ->references('id')
            ->on('brands')
            ->onDelete('cascade');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
