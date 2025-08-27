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
    Schema::table('leads', function (Blueprint $table) {
        $table->foreignId('manager_id')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('leads', function (Blueprint $table) {
        $table->dropForeign(['manager_id']);
        $table->dropColumn('manager_id');
    });
}

};
