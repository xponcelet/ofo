<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            // Localisation enrichie
            $table->string('country')->nullable()->index()->after('location');

            // Transport
            $table->enum('transport_mode', [
                'car', 'plane', 'train', 'bus', 'bike', 'walk', 'boat'
            ])->default('car')->after('country');

            // Distances & durées (jusqu’à l’étape suivante)
            $table->decimal('distance_to_next', 8, 2)->nullable()->after('transport_mode'); // en km
            $table->integer('duration_to_next')->nullable()->after('distance_to_next'); // en minutes

            // Nombre de nuits
            $table->unsignedInteger('nights')->default(0)->after('duration_to_next');
        });
    }

    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->dropColumn(['country', 'transport_mode', 'distance_to_next', 'duration_to_next', 'nights']);
        });
    }
};
