<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->decimal('discount_percent',8,2)->nullable()->after('amount');
            $table->decimal('discount_amount',10,2)->nullable()->after('discount_percent');
            $table->decimal('payable_amount',10,2)->nullable()->after('discount_amount');

        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {

            $table->dropColumn([
                'discount_percent',
                'discount_amount',
                'payable_amount'
            ]);

        });
    }
};
