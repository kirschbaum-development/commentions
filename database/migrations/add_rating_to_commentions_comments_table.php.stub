<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('commentions.tables.comments', 'comments'), function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table(config('commentions.tables.comments', 'comments'), function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
