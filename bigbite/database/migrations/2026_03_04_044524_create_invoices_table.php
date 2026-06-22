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
        Schema::create('invoices', function (Blueprint $table) {

            // Primary Key
            $table->id();

            // Invoice Date
            $table->date('date');

            // Invoice Number
            $table->string('invoice_no')->unique();

            // Firm ID (Foreign Key)
            $table->unsignedBigInteger('firm_id');

            // Salesperson ID (Foreign Key)
            $table->unsignedBigInteger('salesperson_id');

            // Invoice Amount
            $table->decimal('amount', 10, 2);

            // Laravel timestamps
            $table->timestamps();

            // Soft Delete Column
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('firm_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('salesperson_id')->references('id')->on('salespersons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};