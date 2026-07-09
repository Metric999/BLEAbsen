<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabel mata_kuliahs
        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id('id_matkul');
            $table->string('nama_matkul');
            $table->unsignedTinyInteger('sks')->default(2);
            $table->unsignedTinyInteger('semester')->default(1);
            $table->timestamps();
        });

        // Pivot: Mata Kuliah ↔ Ruangan (Many-to-Many)
        // Satu matkul bisa dipakai di beberapa ruangan (teori & praktikum)
        Schema::create('matakuliah_ruangan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_matkul');
            $table->string('id_ruangan', 20);

            $table->foreign('id_matkul')
                  ->references('id_matkul')
                  ->on('mata_kuliahs')
                  ->cascadeOnDelete();

            $table->foreign('id_ruangan')
                  ->references('id_ruangan')
                  ->on('ruangans')
                  ->cascadeOnDelete();

            // Satu matkul tidak boleh dikaitkan ke ruangan yang sama lebih dari sekali
            $table->unique(['id_matkul', 'id_ruangan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matakuliah_ruangan');
        Schema::dropIfExists('mata_kuliahs');
    }
};