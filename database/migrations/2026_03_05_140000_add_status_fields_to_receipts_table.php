<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->enum('manager_status', ['pending', 'accpet', 'rejected'])
                ->default('pending')
                ->after('mode');
            $table->enum('status', ['pending', 'accpet', 'rejected'])
                ->default('pending')
                ->after('manager_status');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn(['manager_status', 'status']);
        });
    }
};

