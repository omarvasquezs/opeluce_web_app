<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LensometerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','host','port','database','username','password_encrypted','schema','table_name','options','enabled'
    ];

    protected $casts = [
        'options' => 'array',
        'enabled' => 'boolean'
    ];
}
