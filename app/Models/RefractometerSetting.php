<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefractometerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'unc_path', 'local_mount_path', 'alternate_path', 'poll_interval_seconds',
        'file_pattern', 'history_limit', 'options', 'enabled'
    ];

    protected $casts = [
        'options' => 'array',
        'enabled' => 'boolean'
    ];
}
