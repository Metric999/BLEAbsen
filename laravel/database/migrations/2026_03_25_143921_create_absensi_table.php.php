<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_kuliah');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
            $table->timestamp('waktu_absensi')->nullable();
            $table->integer('rssi')->nullable();          // sinyal BLE saat absensi
            $table->float('jarak_estimasi')->nullable();  // estimasi jarak (meter)
            $table->string('device_id')->nullable();      // MAC/ID perangkat HP
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->timestamps();
            $table->unique(['jadwal_kuliah', 'mahasiswa_id']); // 1 absensi per pertemuan
        });
    }
    public function down(): void { Schema::dropIfExists('absensi'); }
};