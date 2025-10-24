<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            // Dates de l'étape (facultatives)
            if (!Schema::hasColumn('steps', 'start_date')) {
                $table->date('start_date')->nullable()->after('location');
            }
            if (!Schema::hasColumn('steps', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }

            // Durée: recommandé par défaut 0 pour que la cascade ne s’arrête pas
            if (!Schema::hasColumn('steps', 'nights')) {
                $table->unsignedInteger('nights')->default(0)->after('end_date');
            } else {
                // si existe déjà, on s'assure du default à 0
                $table->unsignedInteger('nights')->default(0)->change();
            }

            // Ordre d’affichage (si pas encore présent)
            if (!Schema::hasColumn('steps', 'order')) {
                $table->unsignedInteger('order')->after('nights');
            }

            // Index utile pour nos recalculs
            if (!collect(Schema::getColumnListing('steps'))->contains('trip_id')) {
                // rien: on suppose la colonne existe (sinon l’ajouter dans une autre migration dédiée)
            }
            $table->index(['trip_id', 'order'], 'steps_trip_order_idx');
        });
    }

    public function down(): void
    {
        Schema::table('steps', function (Blueprint $table) {
            // On ne supprime que ce qu’on a créé — optionnel.
            if (Schema::hasIndex('steps', 'steps_trip_order_idx')) {
                $table->dropIndex('steps_trip_order_idx');
            }
            // Par prudence en prod, on ne drop pas les colonnes (à toi de voir).
            // $table->dropColumn(['start_date', 'end_date', 'nights', 'order']);
        });
    }
};
