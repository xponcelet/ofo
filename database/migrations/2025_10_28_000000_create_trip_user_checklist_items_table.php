<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trip_user_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_user_id')->constrained('trip_users')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('checklist_items')->cascadeOnDelete();
            $table->boolean('is_checked')->default(false);
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['trip_user_id', 'checklist_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_user_checklist_items');
    }
};
