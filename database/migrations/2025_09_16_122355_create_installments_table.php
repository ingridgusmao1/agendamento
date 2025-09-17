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
        Schema::create('installments', function(Blueprint $t){
        $t->id();
        $t->foreignId('sale_id')->constrained();
        $t->unsignedTinyInteger('number');        // 1..n
        $t->date('due_date');
        $t->decimal('amount',12,2);               // valor original
        $t->decimal('paid_total',12,2)->default(0);
        $t->enum('status',['aberto','parcial','pago','atrasado'])->default('aberto');
        $t->timestamps();
        $t->unique(['sale_id','number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
