<?php

namespace App\Http\Controllers;

use App\Models\RefractometerSetting;
use App\Models\LensometerSetting;
use Illuminate\Http\Request;

class DeviceIntegrationSettingsController extends Controller
{
    public function index()
    {
        return response()->json([
            'refractometer' => RefractometerSetting::orderBy('id')->get(),
            'lensometer' => LensometerSetting::orderBy('id')->get(),
        ]);
    }

    public function upsertRefractometer(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:refractometer_settings,id',
            'name' => 'required|string|max:100',
            'unc_path' => 'nullable|string|max:255',
            'local_mount_path' => 'nullable|string|max:255',
            'alternate_path' => 'nullable|string|max:255',
            'poll_interval_seconds' => 'nullable|integer|min:5|max:3600',
            'file_pattern' => 'nullable|string|max:120',
            'history_limit' => 'nullable|integer|min:10|max:100000',
            'options' => 'nullable|array',
            'enabled' => 'boolean'
        ]);

        $setting = RefractometerSetting::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        return response()->json($setting, 201);
    }

    public function upsertLensometer(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|exists:lensometer_settings,id',
            'name' => 'required|string|max:100',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'database' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password_encrypted' => 'nullable|string|max:255',
            'schema' => 'nullable|string|max:255',
            'table_name' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'enabled' => 'boolean'
        ]);

        $setting = LensometerSetting::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        return response()->json($setting, 201);
    }
}
