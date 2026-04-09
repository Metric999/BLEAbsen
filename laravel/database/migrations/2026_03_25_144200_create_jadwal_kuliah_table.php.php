<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('jadwal_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('jam', 10)->unique();
            $table->string('jam', 10)->unique();
            $table->string('matkul');
            $table->string('dosen', 10);
            $table->string('ruangan');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('jadwal_kuliah'); }
};