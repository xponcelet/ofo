<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->constrained()->cascadeOnDelete(); // étape obligatoire
            $table->string('title', 120);
            $table->text('description')->nullable();

            // Timing
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();

            // Réservations / liens
            $table->string('external_link')->nullable();

            // Coûts
            $table->decimal('cost', 10, 2)->nullable();
            $table->char('currency', 3)->nullable();

            // Catégorie libre (ex: food, visit, transport…)
            $table->string('category')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['step_id', 'start_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('activities');
    }
};
