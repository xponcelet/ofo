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
        Schema::table('trip_users', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('departure_date');
            $table->text('notes')->nullable()->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('trip_users', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'notes']);
        });
    }

};
