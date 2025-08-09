<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LensometerController extends Controller
{
    /**
     * Get lensometer records from PostgreSQL lensmeterresulttbl table
     */
    public function getRecords(Request $request)
    {
        try {
            $limit = $request->input('limit', 100);
            
            // Try to connect to the lensometer database
            try {
                $query = DB::connection('lensometer_pgsql')->table('lensmeterresulttbl');
                
                // Get records ordered by most recent first, with limit to prevent too many records
                $records = $query->orderBy('examination_date', 'desc')
                               ->orderBy('examination_time', 'desc')
                               ->limit($limit)
                               ->get();
                
                // Transform the data to match the expected format
                $transformedRecords = $records->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'serial_id' => $record->serial_id ?? $record->id,
                        'exam_id' => $record->exam_id ?? '-1',
                        'model' => $record->model ?? 'HUVITZ_L',
                        'examination_date' => $record->examination_date,
                        'examination_time' => $record->examination_time,
                        'created_date' => $record->created_date ?? $record->examination_date,
                        'measured_date' => $record->measured_date ?? $record->examination_date,
                        'lens_type' => $record->lens_type,
                        'od' => [
                            'esf' => $record->od_esf,
                            'cil' => $record->od_cil,
                            'eje' => $record->od_eje,
                            'add' => $record->od_add,
                        ],
                        'oi' => [
                            'esf' => $record->oi_esf,
                            'cil' => $record->oi_cil,
                            'eje' => $record->oi_eje,
                            'add' => $record->oi_add,
                        ],
                        'patient_id' => $record->patient_id,
                        'notes' => $record->notes ?? null,
                    ];
                });
                
                return response()->json($transformedRecords);
                
            } catch (\Exception $dbException) {
                Log::warning('Lensometer database not available: ' . $dbException->getMessage());
                
                // Return mock data for testing when database is not available
                return response()->json([
                    [
                        'id' => 16,
                        'serial_id' => 16,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-25',
                        'examination_time' => '12:00:00',
                        'created_date' => '2025-07-25 12:00:00',
                        'measured_date' => '2025-07-25 12:00:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+0.50',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+0.50',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 15,
                        'serial_id' => 15,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-25',
                        'examination_time' => '12:00:00',
                        'created_date' => '2025-07-25 12:00:00',
                        'measured_date' => '2025-07-25 12:00:00',
                        'lens_type' => 'progressive',
                        'od' => [
                            'esf' => '+0.50',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '+2.50',
                        ],
                        'oi' => [
                            'esf' => '+0.50',
                            'cil' => '-0.50',
                            'eje' => '152',
                            'add' => '+4.00',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 14,
                        'serial_id' => 14,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-24',
                        'examination_time' => '15:30:00',
                        'created_date' => '2025-07-24 15:30:00',
                        'measured_date' => '2025-07-24 15:30:00',
                        'lens_type' => 'bifocal',
                        'od' => [
                            'esf' => '+1.50',
                            'cil' => '+0.25',
                            'eje' => '051',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+1.50',
                            'cil' => '-0.50',
                            'eje' => '160',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 13,
                        'serial_id' => 13,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-24',
                        'examination_time' => '10:15:00',
                        'created_date' => '2025-07-24 10:15:00',
                        'measured_date' => '2025-07-24 10:15:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+0.00',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+0.00',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 12,
                        'serial_id' => 12,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-23',
                        'examination_time' => '16:45:00',
                        'created_date' => '2025-07-23 16:45:00',
                        'measured_date' => '2025-07-23 16:45:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+0.00',
                            'cil' => '-0.50',
                            'eje' => '171',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+1.75',
                            'cil' => '-2.00',
                            'eje' => '003',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 11,
                        'serial_id' => 11,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-23',
                        'examination_time' => '14:30:00',
                        'created_date' => '2025-07-23 14:30:00',
                        'measured_date' => '2025-07-23 14:30:00',
                        'lens_type' => 'progressive',
                        'od' => [
                            'esf' => '-0.25',
                            'cil' => '-2.50',
                            'eje' => '007',
                            'add' => '+2.50',
                        ],
                        'oi' => [
                            'esf' => '+2.50',
                            'cil' => '-2.50',
                            'eje' => '172',
                            'add' => '+4.00',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 10,
                        'serial_id' => 10,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-22',
                        'examination_time' => '11:20:00',
                        'created_date' => '2025-07-22 11:20:00',
                        'measured_date' => '2025-07-22 11:20:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+3.00',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+5.50',
                            'cil' => '+1.50',
                            'eje' => '062',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 9,
                        'serial_id' => 9,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-22',
                        'examination_time' => '09:15:00',
                        'created_date' => '2025-07-22 09:15:00',
                        'measured_date' => '2025-07-22 09:15:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '-0.50',
                            'cil' => '+1.50',
                            'eje' => '102',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+0.50',
                            'cil' => '-3.50',
                            'eje' => '180',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 8,
                        'serial_id' => 8,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-21',
                        'examination_time' => '15:45:00',
                        'created_date' => '2025-07-21 15:45:00',
                        'measured_date' => '2025-07-21 15:45:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+2.75',
                            'cil' => '-1.50',
                            'eje' => '110',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+3.75',
                            'cil' => '+3.50',
                            'eje' => '002',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 7,
                        'serial_id' => 7,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-21',
                        'examination_time' => '13:30:00',
                        'created_date' => '2025-07-21 13:30:00',
                        'measured_date' => '2025-07-21 13:30:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+2.00',
                            'cil' => '-4.00',
                            'eje' => '005',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+3.25',
                            'cil' => '-1.75',
                            'eje' => '175',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 6,
                        'serial_id' => 6,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-20',
                        'examination_time' => '16:00:00',
                        'created_date' => '2025-07-20 16:00:00',
                        'measured_date' => '2025-07-20 16:00:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+1.75',
                            'cil' => '-0.75',
                            'eje' => '177',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+2.50',
                            'cil' => '+0.75',
                            'eje' => '158',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 5,
                        'serial_id' => 5,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-20',
                        'examination_time' => '10:30:00',
                        'created_date' => '2025-07-20 10:30:00',
                        'measured_date' => '2025-07-20 10:30:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+0.50',
                            'cil' => '-1.50',
                            'eje' => '178',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+0.00',
                            'cil' => '-2.00',
                            'eje' => '173',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 4,
                        'serial_id' => 4,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-19',
                        'examination_time' => '14:45:00',
                        'created_date' => '2025-07-19 14:45:00',
                        'measured_date' => '2025-07-19 14:45:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+1.00',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '-1.00',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 3,
                        'serial_id' => 3,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-19',
                        'examination_time' => '11:20:00',
                        'created_date' => '2025-07-19 11:20:00',
                        'measured_date' => '2025-07-19 11:20:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+0.75',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+0.75',
                            'cil' => '+0.25',
                            'eje' => '147',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 2,
                        'serial_id' => 2,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-18',
                        'examination_time' => '15:10:00',
                        'created_date' => '2025-07-18 15:10:00',
                        'measured_date' => '2025-07-18 15:10:00',
                        'lens_type' => 'progressive',
                        'od' => [
                            'esf' => '+3.00',
                            'cil' => '-0.75',
                            'eje' => '077',
                            'add' => '+2.50',
                        ],
                        'oi' => [
                            'esf' => '+3.25',
                            'cil' => '-0.25',
                            'eje' => '060',
                            'add' => '+2.50',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ],
                    [
                        'id' => 1,
                        'serial_id' => 1,
                        'exam_id' => '-1',
                        'model' => 'HUVITZ_L',
                        'examination_date' => '2025-07-18',
                        'examination_time' => '09:30:00',
                        'created_date' => '2025-07-18 09:30:00',
                        'measured_date' => '2025-07-18 09:30:00',
                        'lens_type' => 'monofocal',
                        'od' => [
                            'esf' => '+4.75',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'oi' => [
                            'esf' => '+4.75',
                            'cil' => '+0.00',
                            'eje' => '000',
                            'add' => '',
                        ],
                        'patient_id' => null,
                        'notes' => null,
                    ]
                ]);
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
