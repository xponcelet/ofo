<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('checklist_items', 'is_checked')) {
            Schema::table('checklist_items', function (Blueprint $table) {
                $table->dropColumn('is_checked');
            });
        }
    }
    public function down(): void {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->boolean('is_checked')->default(false);
        });
    }
};
