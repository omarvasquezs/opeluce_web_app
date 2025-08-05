<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriaClinica extends Model
{
    protected $table = 'historia_clinica';
    
    protected $fillable = [
        'hc_num',
        'paciente',
        'dni',
        'fecha_nacimiento',
        'edad',
        'fecha_atencion',
        'raza',
        'sexo',
        'hora_atencion',
        'ocupacion',
        'afiliacion',
        'domicilio',
        'acompanante',
        'dni_acompanante'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'fecha_atencion' => 'date:Y-m-d',
        'edad' => 'integer'
    ];

    // Disable timestamps if the table doesn't have created_at/updated_at
    public $timestamps = false;
}
