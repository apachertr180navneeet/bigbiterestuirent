<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salespersons', function (Blueprint $table) {
            $table->string('salesperson_code')
                  ->unique()
                  ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('salespersons', function (Blueprint $table) {
            $table->dropUnique(['salesperson_code']);
            $table->dropColumn('salesperson_code');
        });
    }
};