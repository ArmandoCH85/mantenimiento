<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anio',
        'numero_motor',
        'numero_chasis',
        'color',
        'tipo_vehiculo',
        'fecha_compra',
        'propietario_actual',
        'kilometraje_actual',
    ];

    protected $dates = ['deleted_at'];

    public function soatRecords()
    {
        return $this->hasMany(SoatRecord::class);
    }

    public function revisionTecnicaRecords()
    {
        return $this->hasMany(RevisionTecnicaRecord::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}