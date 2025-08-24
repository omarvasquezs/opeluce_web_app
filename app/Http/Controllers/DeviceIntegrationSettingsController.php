<?php

namespace App\Http\Controllers;

use App\Models\RefractometerSetting;
use App\Models\LensometerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function testLensometer(Request $request)
    {
    $t0 = microtime(true);
        // Allow either using posted params or existing saved setting
        $payload = $request->all();
        if (!isset($payload['host']) && isset($payload['id'])) {
            $saved = LensometerSetting::find($payload['id']);
            if ($saved) {
                $payload = $saved->toArray();
            }
        }
        // Basic validation (not strict; we want partial diagnostics)
        $host = $payload['host'] ?? null;
        $database = $payload['database'] ?? null;
        $username = $payload['username'] ?? null;
        $password = $payload['password_encrypted'] ?? null;
        $port = $payload['port'] ?? 5432;
        $schema = $payload['schema'] ?? 'public';
        $table = $payload['table_name'] ?? 'lensmeterresulttbl';

        if (!$host || !$database || !$username) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan host, database o usuario para probar.'
            ], 422);
        }

        try {
            $base = config('database.connections.lensometer_pgsql');
            $base['host'] = $host;
            $base['port'] = $port;
            $base['database'] = $database;
            $base['username'] = $username;
            if ($password !== null) {
                $base['password'] = $password; // future: decrypt
            }
            if ($schema) {
                $base['search_path'] = $schema;
            }
            $runtime = 'lensometer_test_' . uniqid();
            config(["database.connections.$runtime" => $base]);

            $conn = DB::connection($runtime);
            // Quick connect test
            $conn->select('SELECT 1');

            // Check table existence
            $exists = false;
            try {
                $exists = $conn->getSchemaBuilder()->hasTable($table);
            } catch (\Throwable $e) {
                // fallback manual query
                $exists = (bool) $conn->select("SELECT to_regclass(? ) AS reg", [($schema ? $schema.'.' : '').$table])[0]->reg;
            }

            $rowCount = null;
            if ($exists) {
                try {
                    $rowCount = $conn->table($table)->count();
                } catch (\Throwable $e) {
                    $rowCount = 'error: '.$e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Conexión exitosa',
                'table_exists' => $exists,
                'row_count' => $rowCount,
                'host' => $host,
                'database' => $database,
                'schema' => $schema,
                'table' => $table,
                'port' => $port,
                'elapsed_ms' => round((microtime(true) - $t0) * 1000, 1),
            ]);
        } catch (\Throwable $e) {
            // Dev note: Return 200 with success=false so frontend can surface detailed message
            $msg = $e->getMessage();
            $type = 'unknown';
            if (stripos($msg, 'timeout') !== false) $type = 'timeout';
            elseif (stripos($msg, 'password authentication failed') !== false) $type = 'auth';
            elseif (stripos($msg, 'could not translate host name') !== false) $type = 'dns';
            elseif (stripos($msg, 'connection refused') !== false) $type = 'refused';
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión',
                'error' => $msg,
                'error_type' => $type,
                'host' => $host ?? null,
                'database' => $database ?? null,
                'port' => $port ?? null,
                'elapsed_ms' => round((microtime(true) - $t0) * 1000, 1),
            ]);
        }
    }

    public function testRefractometer(Request $request)
    {
        $payload = $request->all();
        if (!isset($payload['local_mount_path']) && isset($payload['id'])) {
            $saved = RefractometerSetting::find($payload['id']);
            if ($saved) {
                $payload = $saved->toArray();
            }
        }
        $local = $payload['local_mount_path'] ?? null;
        $unc = $payload['unc_path'] ?? null;
        $alt = $payload['alternate_path'] ?? null;
        $pattern = $payload['file_pattern'] ?? '*.xml';
        $chosen = $local ?: ($unc ?: $alt);
        if (!$chosen) {
            return response()->json([
                'success' => false,
                'message' => 'No hay ruta configurada (local, UNC o alterna)'
            ]);
        }
        $path = rtrim($chosen, '/');
        try {
            if (!is_dir($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La ruta no es un directorio o no existe',
                    'path' => $path
                ]);
            }
            $globPattern = $path . '/' . $pattern;
            $files = glob($globPattern, GLOB_NOSORT);
            $count = $files ? count($files) : 0;
            $latest = null;
            if ($count) {
                // Get most recently modified file
                usort($files, function($a,$b){ return filemtime($b) <=> filemtime($a); });
                $latest = basename($files[0]);
            }
            return response()->json([
                'success' => $count > 0,
                'message' => $count > 0 ? 'Archivos encontrados' : 'Directorio accesible pero sin archivos que coincidan',
                'path' => $path,
                'pattern' => $pattern,
                'file_count' => $count,
                'latest_file' => $latest,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accediendo a la ruta',
                'error' => $e->getMessage(),
                'path' => $path,
            ]);
        }
    }
}
