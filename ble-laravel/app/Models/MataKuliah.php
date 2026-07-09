<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    protected $table = 'mata_kuliahs';
    protected $primaryKey = 'id_matkul';

    protected $fillable = [
        'id_matkul',
        'nama_matkul',
        'sks',
        'semester',
    ];

    /** Jadwal yang menggunakan mata kuliah ini. */
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_matkul', 'id_matkul');
    }

    /**
     * Ruangan yang dipakai oleh mata kuliah ini (M-M via pivot).
     * Pivot table: matakuliah_ruangan
     */
    public function ruangans()
    {
        return $this->belongsToMany(
            Ruangan::class,
            'matakuliah_ruangan',   // pivot table
            'id_matkul',            // FK di pivot → matakuliah
            'id_ruangan'            // FK di pivot → ruangan
        );
    }
}