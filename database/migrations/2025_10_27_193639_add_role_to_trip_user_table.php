<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trip_users', function (Blueprint $table) {
            // ✅ Ajoute la colonne "role" (propriétaire, collaborateur, viewer, etc.)
            if (!Schema::hasColumn('trip_users', 'role')) {
                $table->string('role', 50)->default('owner')->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trip_users', function (Blueprint $table) {
            // Rollback : supprimer la colonne "role"
            if (Schema::hasColumn('trip_users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
