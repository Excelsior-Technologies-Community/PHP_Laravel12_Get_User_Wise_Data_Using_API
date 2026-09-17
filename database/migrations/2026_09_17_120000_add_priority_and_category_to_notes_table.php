<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('priority', 20)->default('medium')->after('description'); // high, medium, low
            $table->string('category', 50)->default('General')->after('priority'); // Work, Personal, Urgent, Ideas, General
            $table->string('color', 20)->default('#38bdf8')->after('category');
            $table->boolean('is_pinned')->default(false)->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['priority', 'category', 'color', 'is_pinned']);
        });
    }
};
