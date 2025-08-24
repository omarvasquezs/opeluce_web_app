<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LensometerSetting;

class LensometerController extends Controller
{
    /**
     * Get lensometer records from PostgreSQL lensmeterresulttbl table
     */
    public function getRecords(Request $request)
    {
        try {
            $limit = $request->input('limit', 100);

            // Load settings
            $setting = LensometerSetting::where('enabled', true)->orderBy('id')->first();
            if (!$setting) {
                return response()->json([]);
            }
            $allowMock = (bool)($setting->options['allow_mock'] ?? false);
            
            // Try to connect to the lensometer database
            try {
                // Use dynamic connection override if settings specify host/database etc.
                // We'll build a temporary connection array when custom host provided.
                if ($setting->host || $setting->database) {
                    $config = config('database.connections.lensometer_pgsql');
                    $config['host'] = $setting->host ?: $config['host'];
                    if ($setting->port) $config['port'] = $setting->port;
                    $config['database'] = $setting->database ?: $config['database'];
                    $config['username'] = $setting->username ?: $config['username'];
                    if ($setting->password_encrypted) {
                        // For now treat stored value as plain (future: decrypt)
                        $config['password'] = $setting->password_encrypted;
                    }
                    // Create runtime connection name
                    $runtime = 'lensometer_runtime';
                    config(["database.connections.$runtime" => $config]);
                    $connectionName = $runtime;
                } else {
                    $connectionName = 'lensometer_pgsql';
                }

                $table = $setting->table_name ?: 'lensmeterresulttbl';
                $conn = DB::connection($connectionName);
                $query = $conn->table($table);

                $debug = $request->boolean('debug');

                // Discover available columns to avoid silent exceptions that return []
                $columns = [];
                try { $columns = $conn->getSchemaBuilder()->getColumnListing($table); } catch (\Throwable $e) {
                    if ($debug) {
                        return response()->json([ 'records' => [], 'debug' => ['error' => 'schema_list_failed', 'message' => $e->getMessage(), 'table' => $table] ]);
                    }
                    return response()->json([]);
                }

                $orderDateCol = null; $orderTimeCol = null;
                $candidateDate = ['examination_date','exam_date','measured_date','created_date','date'];
                $candidateTime = ['examination_time','exam_time','measured_time','created_time','time'];
                foreach ($candidateDate as $c) if (in_array($c, $columns)) { $orderDateCol = $c; break; }
                foreach ($candidateTime as $c) if (in_array($c, $columns)) { $orderTimeCol = $c; break; }

                if ($orderDateCol) {
                    $query->orderBy($orderDateCol, 'desc');
                }
                if ($orderTimeCol) {
                    $query->orderBy($orderTimeCol, 'desc');
                }

                $records = $query->limit($limit)->get();
                
                // Transform the data to match the expected format
                $transformedRecords = $records->map(function ($record) use ($columns) {
                    $r = (array)$record;
                    $get = function($keys, $default=null) use ($r) {
                        foreach ((array)$keys as $k) {
                            if (array_key_exists($k, $r) && $r[$k] !== null) return $r[$k];
                        }
                        return $default;
                    };

                    // Prefer the actual column names from your DB, then fall back to previous heuristics
                    $id = $get(['serial_id','id','result_id','sequence_number'], null);
                    if ($id === null) $id = uniqid('lm_');

                    $model = $get(['model_name','model','device_model'], 'HUVITZ_L');
                    $created = $get(['create_time','created_date','created_at','create_time'], null);
                    $measured = $get(['measure_time','measured_date','measured_at','measure_time'], $created);

                    // Sphere (esf)
                    $od_esf = $get(['sph_r','sph_right','sph_right_eye','od_sph','od_sphere'], null);
                    $oi_esf = $get(['sph_l','sph_left','sph_left_eye','oi_sph','oi_sphere'], null);
                    // Cylinder
                    $od_cil = $get(['cyl_r','cyl_right','od_cyl','od_cylinder'], null);
                    $oi_cil = $get(['cyl_l','cyl_left','oi_cyl','oi_cylinder'], null);
                    // Axis
                    $od_eje = $get(['axis_r','axis_right','od_axis'], null);
                    $oi_eje = $get(['axis_l','axis_left','oi_axis'], null);
                    // Add
                    $od_add = $get(['add_1_r','add1_r','add_1_right','add_1','od_add'], null);
                    $oi_add = $get(['add_1_l','add1_l','add_1_left','add_1_l','oi_add'], null);

                    return [
                        'id' => $id,
                        'serial_id' => $get(['serial_id','sequence_number','id'], $id),
                        'exam_id' => $get(['exam_id','examid','examination_id'], '-1'),
                        'model' => $model,
                        'examination_date' => $created ? (string)$created : date('Y-m-d'),
                        'examination_time' => $measured ? (string)$measured : date('H:i:s'),
                        'created_date' => $created,
                        'measured_date' => $measured,
                        'lens_type' => $get(['lens_type','lens_category'], ''),
                        'od' => [
                            'esf' => $od_esf,
                            'cil' => $od_cil,
                            'eje' => $od_eje,
                            'add' => $od_add,
                        ],
                        'oi' => [
                            'esf' => $oi_esf,
                            'cil' => $oi_cil,
                            'eje' => $oi_eje,
                            'add' => $oi_add,
                        ],
                        'patient_id' => $get(['patient_id','patientid','id_patient'], null),
                        'notes' => $get(['notes','note','comment','comments'], null),
                    ];
                });
                
                if ($debug) {
                    return response()->json([
                        'records' => $transformedRecords,
                        'debug' => [
                            'table' => $table,
                            'columns' => $columns,
                            'order_date' => $orderDateCol,
                            'order_time' => $orderTimeCol,
                            'count' => count($transformedRecords),
                            'raw_first_row_keys' => isset($records[0]) ? array_keys((array)$records[0]) : [],
                        ]
                    ]);
                }
                return response()->json($transformedRecords);
                
            } catch (\Exception $dbException) {
                Log::warning('Lensometer database query error: ' . $dbException->getMessage());
                if ($request->boolean('debug')) {
                    return response()->json([
                        'records' => [],
                        'debug' => [
                            'error' => 'query_failed',
                            'message' => $dbException->getMessage(),
                            'table' => $setting->table_name ?: 'lensmeterresulttbl'
                        ]
                    ]);
                }
                return response()->json([]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error fetching lensometer records: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al consultar registros de lensometría',
                'message' => $e->getMessage(),
                'debug' => [
                    'connection' => 'lensometer_pgsql',
                    'table' => 'lensmeterresulttbl'
                ]
            ], 500);
        }
    }
}
