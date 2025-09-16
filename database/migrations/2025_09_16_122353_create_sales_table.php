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
        Schema::create('sales', function(Blueprint $t){
        $t->id();
        $t->string('number')->unique();         // nº da nota
        $t->foreignId('customer_id')->constrained();
        $t->foreignId('seller_id')->constrained('users');
        $t->decimal('total',12,2);
        $t->unsignedTinyInteger('installments_qty');
        $t->unsignedTinyInteger('due_day');                 // dia habitual
        $t->unsignedTinyInteger('rescheduled_day')->nullable(); // dia remarcado, se houver
        $t->date('charge_start_date');                      // início da cobrança
        $t->string('delivery_text');                        // "final do mês", etc.
        $t->decimal('gps_lat',10,7)->nullable();
        $t->decimal('gps_lng',10,7)->nullable();
        $t->text('collection_note')->nullable();            // observação de cobrança
        $t->enum('status',['aberto','fechado','atrasado'])->default('aberto');
        $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
