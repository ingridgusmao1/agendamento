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
        Schema::create('customers', function(Blueprint $t){
        $t->id();
        $t->string('name');
        $t->string('street'); $t->string('number',20);
        $t->string('district'); $t->string('city');
        $t->string('reference_point')->nullable();
        $t->string('rg',30)->nullable()->index();
        $t->string('cpf',14)->nullable()->index();
        $t->string('phone')->nullable();
        $t->string('other_contact')->nullable();
        $t->decimal('lat',10,7)->nullable();
        $t->decimal('lng',10,7)->nullable();
        $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
