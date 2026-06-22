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
        Schema::create('salespersons', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('mobile', 15)->unique();
            $table->string('email')->unique();
            $table->string('password');

            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('alternative_phone', 15)->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes(); // ✅ Soft delete column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salespersons');
    }
};
