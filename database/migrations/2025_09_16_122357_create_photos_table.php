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
        Schema::create('photos', function(Blueprint $t){
        $t->id();
        $t->foreignId('sale_id')->constrained();
        $t->enum('kind',['cliente','lugar'])->index();   // cliente, local de entrega
        $t->string('path');
        $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
