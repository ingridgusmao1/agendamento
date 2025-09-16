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
        Schema::create('products', function(Blueprint $t){
        $t->id();
        $t->string('name');
        $t->string('model')->nullable();
        $t->string('color')->nullable();
        $t->string('size')->nullable();
        $t->json('complements')->nullable();
        $t->text('notes')->nullable();
        $t->decimal('price',12,2);
        $t->string('photo_path')->nullable();
        $t->timestamps();
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
