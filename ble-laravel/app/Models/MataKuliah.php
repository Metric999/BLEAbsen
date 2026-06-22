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

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_matkul', 'id_matkul');
    }
}