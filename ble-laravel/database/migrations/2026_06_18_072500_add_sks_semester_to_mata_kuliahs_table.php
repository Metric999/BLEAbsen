<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->unsignedTinyInteger('sks')->default(2)->after('nama_matkul');
            $table->unsignedTinyInteger('semester')->default(1)->after('sks');
        });
    }

    public function down(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->dropColumn(['sks', 'semester']);
        });
    }
};
