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
        Schema::create('receipts', function (Blueprint $table) {

            // Primary ID
            $table->id();

            // Receipt Date
            $table->date('date')->nullable();

            // Receipt Number
            $table->string('receipt_no')->unique();

            // Firm ID (Foreign key if firms table exists)
            $table->unsignedBigInteger('firm_id')->nullable();

            // Invoice / Bill ID
            $table->unsignedBigInteger('invoice_id')->nullable();

            // Invoice Amount
            $table->decimal('amount', 10, 2)->default(0);

            // Amount Given By Customer
            $table->decimal('given_amount', 10, 2)->default(0);

            // Discount
            $table->decimal('discount', 10, 2)->default(0);

            // Final Amount After Discount
            $table->decimal('final_amount', 10, 2)->default(0);

            // Sales Person
            $table->string('sales_person')->nullable();

            // Payment Mode (Cash / UPI / Bank / Card)
            $table->string('mode')->nullable();

            // Soft Delete
            $table->softDeletes();

            // Created At & Updated At
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
