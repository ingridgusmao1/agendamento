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
        Schema::create('sale_items', function(Blueprint $t){
        $t->id();
        $t->foreignId('sale_id')->constrained();
        $t->foreignId('product_id')->constrained();
        $t->unsignedInteger('qty');
        $t->decimal('unit_price',12,2);
        $t->json('attributes')->nullable();  // modelo/cor/tamanho/obs
        $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
