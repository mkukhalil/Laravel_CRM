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
    Schema::create('clients', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->string('company_name')->nullable();
        $table->text('address')->nullable();
        $table->enum('status', ['Active', 'Inactive', 'Prospect'])->default('Active');
        $table->foreignId('assigned_to')->constrained('users'); // Assigned agent/manager
        $table->foreignId('created_by')->constrained('users');  // Who created the client
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
