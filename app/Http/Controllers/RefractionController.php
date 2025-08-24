<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\RefractometerSetting;
use DOMDocument;
use DOMXPath;
use Carbon\Carbon;

class RefractionController extends Controller
{
    // Default Windows UNC path (can be overridden by REFRACTION_XML_PATH in .env)
    private $sharedFolderPath = '//127.0.0.1/kr';
    
    /**
     * Get refraction records from XML files in shared folder
     */
    public function getRecords(Request $request)
    {
        try {
            $limit = $request->input('limit', 100);

            // Load settings (first enabled row or named)
            $setting = RefractometerSetting::where('enabled', true)
                        ->orderBy('id')
                        ->first();

            if (!$setting) {
                // No enabled settings -> return empty list (no mock)
                return response()->json([]);
            }

            // Resolve path precedence: local_mount_path > unc_path > alternate_path > fallback getXmlPath()
            $xmlPath = $setting->local_mount_path
                ?: $setting->unc_path
                ?: $setting->alternate_path
                ?: $this->getXmlPath();
            
            // For development/testing, create mock data if folder doesn't exist
            $allowMock = (bool)($setting->options['allow_mock'] ?? false);
            $debug = $request->boolean('debug');
            if (!is_dir($xmlPath)) {
                Log::warning('XML folder not accessible: ' . $xmlPath);
                if (!$allowMock) {
                    return response()->json($debug ? [
                        'records' => [],
                        'debug' => [
                            'reason' => 'path_not_found',
                            'xml_path' => $xmlPath,
                            'pattern' => null,
                        ]
                    ] : []);
                }
                // Return mock data for testing (include mock flag for UI) only if allowed
                $mock = [
                    [
                        'id' => 'mock-1',
                        'mock' => true,
                        'filename' => 'M-Serial7_2025-07-25_23-18-01.xml',
                        'examination_date' => '2025-07-25',
                        'examination_time' => '23:18:01',
                        'patient_id' => '',
                        'od' => [
                            'esf' => '0.50',
                            'cil' => '0.0',
                            'eje' => '0',
                        ],
                        'oi' => [
                            'esf' => '0.50',
                            'cil' => '-0.25',
                            'eje' => '25',
                        ],
                        'dip' => '65.50',
                        'keratometry' => [
                            'od' => [
                                'k1' => '41.75',
                                'k1_axis' => '170',
                                'k2' => '42.25',
                                'k2_axis' => '80',
                            ],
                            'oi' => [
                                'k1' => '42.00',
                                'k1_axis' => '15',
                                'k2' => '42.75',
                                'k2_axis' => '105',
                            ],
                        ],
                    ],
                    [
                        'id' => 'mock-2',
                        'mock' => true,
                        'filename' => 'M-Serial7_2025-07-25_23-19-45.xml',
                        'examination_date' => '2025-07-25',
                        'examination_time' => '23:19:45',
                        'patient_id' => '',
                        'od' => [
                            'esf' => '0.75',
                            'cil' => '-0.25',
                            'eje' => '170',
                        ],
                        'oi' => [
                            'esf' => '0.50',
                            'cil' => '-0.25',
                            'eje' => '175',
                        ],
                        'dip' => '65.00',
                        'keratometry' => [
                            'od' => [
                                'k1' => '42.00',
                                'k1_axis' => '170',
                                'k2' => '42.75',
                                'k2_axis' => '80',
                            ],
                            'oi' => [
                                'k1' => '42.25',
                                'k1_axis' => '10',
                                'k2' => '43.25',
                                'k2_axis' => '100',
                            ],
                        ],
                    ]
                ];
                return response()->json($debug ? ['records' => $mock, 'debug' => ['reason' => 'mock_used_path_missing','xml_path' => $xmlPath]] : $mock);
            }
            
            $records = [];
            
            // Look for XML files in the directory
            $pattern = rtrim($xmlPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ($setting->file_pattern ?: '*.xml');
            $xmlFiles = glob($pattern);
            if ($debug) {
                Log::info('Refraction debug', ['path' => $xmlPath, 'pattern' => $pattern, 'file_count' => count($xmlFiles)]);
            }
            
            if (empty($xmlFiles)) {
                return response()->json($debug ? [ 'records' => [], 'debug' => [ 'reason' => 'no_files','xml_path' => $xmlPath,'pattern' => $pattern ] ] : []);
            }
            
            // Limit the number of files to process to prevent memory issues
            $xmlFiles = array_slice($xmlFiles, 0, $limit);
            
            foreach ($xmlFiles as $xmlFile) {
                try {
                    $record = $this->parseXmlFile($xmlFile);
                    
                    if ($record) {
                        $records[] = $record;
                    }
                } catch (\Exception $e) {
                    Log::warning('Error parsing XML file: ' . $xmlFile . ' - ' . $e->getMessage());
                    continue;
                }
            }
            
            // Sort by date descending
            usort($records, function ($a, $b) {
                return strtotime($b['examination_date']) - strtotime($a['examination_date']);
            });
            
            return response()->json($debug ? [ 'records' => $records, 'debug' => [ 'xml_path' => $xmlPath, 'pattern' => $pattern, 'file_count' => count($xmlFiles) ] ] : $records);
            
        } catch (\Exception $e) {
            Log::error('Error fetching refraction records: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al consultar registros de refracción',
                'message' => $e->getMessage(),
                'debug' => [
                    'path_attempted' => $this->getXmlPath(),
                    'php_os' => PHP_OS,
                ]
            ], 500);
        }
    }
    
    /**
     * Get the XML path based on the operating system
     */
    private function getXmlPath()
    {
        // Allow override via environment variable
        $override = env('REFRACTION_XML_PATH');
        if ($override) {
            return $override;
        }

        // Check if we're on Windows and can access the UNC path directly
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return str_replace('/', '\\', $this->sharedFolderPath);
        }

        // On Linux, try a conventional mount path (configure host to mount share at /mnt/kr)
        $localPath = '/mnt/kr';
        if (is_dir($localPath)) {
            return $localPath;
        }

        // Fallback to original UNC (will fail and trigger mock)
        return $this->sharedFolderPath;
    }
    
    /**
     * Parse an XML file and extract refraction data
     */
    private function parseXmlFile($xmlFile)
    {
        $doc = new DOMDocument();
        
        // Suppress warnings for malformed XML
        $oldSetting = libxml_use_internal_errors(true);
        
        if (!$doc->load($xmlFile)) {
            libxml_use_internal_errors($oldSetting);
            return null;
        }
        
        libxml_use_internal_errors($oldSetting);
        
        $xpath = new DOMXPath($doc);
        
        // Extract basic information
        // Fallback sequences for date/time tags used by different manufacturers
        $dateCandidates = ['//Date','//ExamDate','//DATE','//MeasurementDate'];
        $timeCandidates = ['//Time','//ExamTime','//TIME','//MeasurementTime'];
        $examDate = null; $examTime = null;
        foreach ($dateCandidates as $q) { if ($examDate = $this->extractXmlValue($xpath, $q)) break; }
        foreach ($timeCandidates as $q) { if ($examTime = $this->extractXmlValue($xpath, $q)) break; }
        // Derive from filename if still missing (expecting _YYYYMMDD_HHMMSS_ pattern)
        if (!$examDate || !$examTime) {
            if (preg_match('/_(\d{8})_(\d{6})_/', basename($xmlFile), $m)) {
                $dateStr = $m[1]; // YYYYMMDD
                $timeStr = $m[2]; // HHMMSS
                $examDate = $examDate ?: substr($dateStr,0,4).' - '.substr($dateStr,4,2).' - '.substr($dateStr,6,2);
                $examDate = str_replace(' ','',$examDate);
                $examDate = str_replace('-','',$examDate) ? substr($dateStr,0,4).'-'.substr($dateStr,4,2).'-'.substr($dateStr,6,2) : $examDate; // ensure proper formatting
                $examTime = $examTime ?: substr($timeStr,0,2).':'.substr($timeStr,2,2).':'.substr($timeStr,4,2);
            }
        }
        $record = [
            'id' => basename($xmlFile, '.xml'),
            'filename' => basename($xmlFile),
            'examination_date' => $examDate ?: date('Y-m-d'),
            'examination_time' => $examTime ?: date('H:i:s'),
            'patient_id' => $this->extractXmlValue($xpath, '//PatientID') ?: '',
        ];
        
        // Extract OD (Right Eye) refraction data
        $record['od'] = [
            'esf' => $this->extractFirstValue($xpath, ['//OD/Sphere','//OD/Sph','//R/Sphere','//Right/Sphere']) ?: '',
            'cil' => $this->extractFirstValue($xpath, ['//OD/Cylinder','//OD/Cyl','//R/Cylinder','//Right/Cylinder']) ?: '',
            'eje' => $this->extractFirstValue($xpath, ['//OD/Axis','//R/Axis','//Right/Axis']) ?: '',
        ];
        
        // Extract OI (Left Eye) refraction data
        $record['oi'] = [
            'esf' => $this->extractFirstValue($xpath, ['//OI/Sphere','//OS/Sphere','//L/Sphere','//Left/Sphere']) ?: '',
            'cil' => $this->extractFirstValue($xpath, ['//OI/Cylinder','//OS/Cylinder','//L/Cylinder','//Left/Cylinder']) ?: '',
            'eje' => $this->extractFirstValue($xpath, ['//OI/Axis','//OS/Axis','//L/Axis','//Left/Axis']) ?: '',
        ];
        
        // Extract DIP (Pupillary Distance)
        $record['dip'] = $this->extractXmlValue($xpath, '//PD') ?: $this->extractXmlValue($xpath, '//PupillaryDistance') ?: '';
        
        // Extract keratometry data if available
        // Extract keratometry data (include axis values when present). Different devices use varied tag names.
        $record['keratometry'] = [
            'od' => [
                'k1' => $this->extractFirstValue($xpath, ['//OD/K1','//R/K1','//Right/K1']) ?: '',
                'k1_axis' => $this->extractFirstValue($xpath, ['//OD/K1Axis','//OD/K1_Axis','//R/K1Axis','//Right/K1Axis']) ?: '',
                'k2' => $this->extractFirstValue($xpath, ['//OD/K2','//R/K2','//Right/K2']) ?: '',
                'k2_axis' => $this->extractFirstValue($xpath, ['//OD/K2Axis','//OD/K2_Axis','//R/K2Axis','//Right/K2Axis']) ?: '',
            ],
            'oi' => [
                'k1' => $this->extractFirstValue($xpath, ['//OI/K1','//OS/K1','//L/K1','//Left/K1']) ?: '',
                'k1_axis' => $this->extractFirstValue($xpath, ['//OI/K1Axis','//OS/K1Axis','//OI/K1_Axis','//OS/K1_Axis','//L/K1Axis','//Left/K1Axis']) ?: '',
                'k2' => $this->extractFirstValue($xpath, ['//OI/K2','//OS/K2','//L/K2','//Left/K2']) ?: '',
                'k2_axis' => $this->extractFirstValue($xpath, ['//OI/K2Axis','//OS/K2Axis','//OI/K2_Axis','//OS/K2_Axis','//L/K2Axis','//Left/K2Axis']) ?: '',
            ],
        ];
        
        return $record;
    }
    
    /**
     * Extract value from XML using XPath
     */
    private function extractXmlValue($xpath, $query)
    {
        $nodes = $xpath->query($query);
        
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->nodeValue);
        }
        
        return null;
    }

    private function extractFirstValue($xpath, array $queries)
    {
        foreach ($queries as $q) {
            $val = $this->extractXmlValue($xpath, $q);
            if ($val !== null && $val !== '') return $val;
        }
        return null;
    }
}
