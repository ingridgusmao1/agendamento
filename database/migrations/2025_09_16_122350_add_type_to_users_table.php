<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            // login por código
            if (!Schema::hasColumn('users', 'code')) {
                $t->string('code')->unique()->after('name');
            }

            // redifine 'type' de forma estável (drop + create)
            if (Schema::hasColumn('users', 'type')) {
                $t->dropColumn('type');
            }
        });

        Schema::table('users', function (Blueprint $t) {
            $t->enum('type', ['admin','vendedor','cobrador','vendedor_cobrador'])
              ->default('vendedor')
              ->after('password');
        });
    }

    public function down(): void
    {
        // reverte para o estado anterior: remove 'code' e volta enum antigo
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users', 'code')) {
                $t->dropUnique(['code']);
                $t->dropColumn('code');
            }
            if (Schema::hasColumn('users', 'type')) {
                $t->dropColumn('type');
            }
        });

        Schema::table('users', function (Blueprint $t) {
            // enum original sem 'admin'
            $t->enum('type', ['vendedor','cobrador','vendedor_cobrador'])
              ->default('vendedor')
              ->after('password');
        });
    }
};
