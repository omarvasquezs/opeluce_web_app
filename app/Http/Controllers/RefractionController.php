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
                // Removed mock fallback: return empty results so UI is not misled by synthetic data.
                Log::warning('XML folder not accessible: ' . $xmlPath);
                return response()->json($debug ? [
                    'records' => [],
                    'debug' => [
                        'reason' => 'path_not_found',
                        'xml_path' => $xmlPath,
                        'pattern' => null,
                    ]
                ] : []);
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
        
        // Debug: Log the full XML structure for the first file
        static $debugLogged = false;
        if (!$debugLogged) {
            Log::info('XML Debug - First file structure: ' . $xmlFile);
            Log::info('XML Content: ' . $doc->saveXML());
            
            // List all element names in the XML
            $allElements = $xpath->query('//*');
            $elementNames = [];
            foreach ($allElements as $element) {
                $elementNames[] = $element->nodeName;
            }
            Log::info('All XML elements found: ' . implode(', ', array_unique($elementNames)));
            $debugLogged = true;
        }
        
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
        
        // Try more generic XPath queries for refraction data with namespace support
        $sphereQueries = [
            '//nsREF:R/nsREF:List/nsREF:Sphere', '//nsREF:R/nsREF:Median/nsREF:Sphere',
            '//nsREF:R//nsREF:Sphere', '//*[local-name()="R"]/*[local-name()="Sphere"]',
            '//*[local-name()="R"]//*[local-name()="Sphere"]',
            '//OD/Sphere', '//OD/Sph', '//R/Sphere', '//Right/Sphere',
            '//Sphere[@eye="OD"]', '//Sphere[@eye="R"]', '//Sphere[@eye="Right"]',
            '//OD//Sphere', '//R//Sphere', '//Right//Sphere',
            '//*[contains(local-name(), "Sphere") and (ancestor::*[contains(local-name(), "OD")] or ancestor::*[contains(local-name(), "Right")])]'
        ];
        
        $cylinderQueries = [
            '//nsREF:R/nsREF:List/nsREF:Cylinder', '//nsREF:R/nsREF:Median/nsREF:Cylinder',
            '//nsREF:R//nsREF:Cylinder', '//*[local-name()="R"]/*[local-name()="Cylinder"]',
            '//*[local-name()="R"]//*[local-name()="Cylinder"]',
            '//OD/Cylinder', '//OD/Cyl', '//R/Cylinder', '//Right/Cylinder',
            '//Cylinder[@eye="OD"]', '//Cylinder[@eye="R"]', '//Cylinder[@eye="Right"]',
            '//OD//Cylinder', '//R//Cylinder', '//Right//Cylinder',
            '//*[contains(local-name(), "Cylinder") and (ancestor::*[contains(local-name(), "OD")] or ancestor::*[contains(local-name(), "Right")])]'
        ];
        
        $axisQueries = [
            '//nsREF:R/nsREF:List/nsREF:Axis', '//nsREF:R/nsREF:Median/nsREF:Axis',
            '//nsREF:R//nsREF:Axis', '//*[local-name()="R"]/*[local-name()="Axis"]',
            '//*[local-name()="R"]//*[local-name()="Axis"]',
            '//OD/Axis', '//R/Axis', '//Right/Axis',
            '//Axis[@eye="OD"]', '//Axis[@eye="R"]', '//Axis[@eye="Right"]',
            '//OD//Axis', '//R//Axis', '//Right//Axis',
            '//*[contains(local-name(), "Axis") and (ancestor::*[contains(local-name(), "OD")] or ancestor::*[contains(local-name(), "Right")])]'
        ];
        
        // Extract OD (Right Eye) refraction data
        $record['od'] = [
            'esf' => $this->extractFirstValue($xpath, $sphereQueries) ?: '',
            'cil' => $this->extractFirstValue($xpath, $cylinderQueries) ?: '',
            'eje' => $this->extractFirstValue($xpath, $axisQueries) ?: '',
        ];
        
        // Left eye queries with namespace support
        $sphereQueriesOI = [
            '//nsREF:L/nsREF:List/nsREF:Sphere', '//nsREF:L/nsREF:Median/nsREF:Sphere',
            '//nsREF:L//nsREF:Sphere', '//*[local-name()="L"]/*[local-name()="Sphere"]',
            '//*[local-name()="L"]//*[local-name()="Sphere"]',
            '//OI/Sphere', '//OS/Sphere', '//L/Sphere', '//Left/Sphere',
            '//Sphere[@eye="OI"]', '//Sphere[@eye="OS"]', '//Sphere[@eye="L"]', '//Sphere[@eye="Left"]',
            '//OI//Sphere', '//OS//Sphere', '//L//Sphere', '//Left//Sphere',
            '//*[contains(local-name(), "Sphere") and (ancestor::*[contains(local-name(), "OI")] or ancestor::*[contains(local-name(), "OS")] or ancestor::*[contains(local-name(), "Left")])]'
        ];
        
        $cylinderQueriesOI = [
            '//nsREF:L/nsREF:List/nsREF:Cylinder', '//nsREF:L/nsREF:Median/nsREF:Cylinder',
            '//nsREF:L//nsREF:Cylinder', '//*[local-name()="L"]/*[local-name()="Cylinder"]',
            '//*[local-name()="L"]//*[local-name()="Cylinder"]',
            '//OI/Cylinder', '//OS/Cylinder', '//L/Cylinder', '//Left/Cylinder',
            '//Cylinder[@eye="OI"]', '//Cylinder[@eye="OS"]', '//Cylinder[@eye="L"]', '//Cylinder[@eye="Left"]',
            '//OI//Cylinder', '//OS//Cylinder', '//L//Cylinder', '//Left//Cylinder',
            '//*[contains(local-name(), "Cylinder") and (ancestor::*[contains(local-name(), "OI")] or ancestor::*[contains(local-name(), "OS")] or ancestor::*[contains(local-name(), "Left")])]'
        ];
        
        $axisQueriesOI = [
            '//nsREF:L/nsREF:List/nsREF:Axis', '//nsREF:L/nsREF:Median/nsREF:Axis',
            '//nsREF:L//nsREF:Axis', '//*[local-name()="L"]/*[local-name()="Axis"]',
            '//*[local-name()="L"]//*[local-name()="Axis"]',
            '//OI/Axis', '//OS/Axis', '//L/Axis', '//Left/Axis',
            '//Axis[@eye="OI"]', '//Axis[@eye="OS"]', '//Axis[@eye="L"]', '//Axis[@eye="Left"]',
            '//OI//Axis', '//OS//Axis', '//L//Axis', '//Left//Axis',
            '//*[contains(local-name(), "Axis") and (ancestor::*[contains(local-name(), "OI")] or ancestor::*[contains(local-name(), "OS")] or ancestor::*[contains(local-name(), "Left")])]'
        ];
        
        // Extract OI (Left Eye) refraction data
        $record['oi'] = [
            'esf' => $this->extractFirstValue($xpath, $sphereQueriesOI) ?: '',
            'cil' => $this->extractFirstValue($xpath, $cylinderQueriesOI) ?: '',
            'eje' => $this->extractFirstValue($xpath, $axisQueriesOI) ?: '',
        ];
        
        // Extract DIP (Pupillary Distance) with more options and clean it up
        $dipQueries = [
            '//nsREF:PD/nsREF:Distance', '//nsREF:PD', '//*[local-name()="PD"]/*[local-name()="Distance"]',
            '//*[local-name()="PD"]', '//PD', '//PupillaryDistance', '//Pd', '//pupillaryDistance', 
            '//*[contains(local-name(), "PD")]', '//*[contains(local-name(), "Pupillary")]'
        ];
        $dipRaw = $this->extractFirstValue($xpath, $dipQueries) ?: '';
        // Clean up DIP value - it might have multiple values or whitespace
        $dipClean = '';
        if ($dipRaw) {
            // Split by whitespace and take the first valid number
            $dipParts = preg_split('/\s+/', trim($dipRaw));
            foreach ($dipParts as $part) {
                if (is_numeric($part) && $part > 0) {
                    $dipClean = $part;
                    break;
                }
            }
        }
        $record['dip'] = $dipClean;
        
        // Extract keratometry data with namespace support
        $keratometryQueries = [
            'od' => [
                'k1' => [
                    '//nsKM:R/nsKM:List/nsKM:R1/nsKM:Power', '//nsKM:R/nsKM:Median/nsKM:R1/nsKM:Power',
                    '//nsKM:R//nsKM:R1//nsKM:Power', '//*[local-name()="R"]//*[local-name()="R1"]//*[local-name()="Power"]',
                    '//OD/K1','//R/K1','//Right/K1', '//OD//K1', '//R//K1', '//Right//K1',
                    '//*[contains(local-name(), "K1") and (ancestor::*[contains(local-name(), "OD")] or ancestor::*[contains(local-name(), "Right")])]'
                ],
                'k1_axis' => [
                    '//nsKM:R/nsKM:List/nsKM:R1/nsKM:Axis', '//nsKM:R/nsKM:Median/nsKM:R1/nsKM:Axis',
                    '//nsKM:R//nsKM:R1//nsKM:Axis', '//*[local-name()="R"]//*[local-name()="R1"]//*[local-name()="Axis"]',
                    '//OD/K1Axis','//OD/K1_Axis','//R/K1Axis','//Right/K1Axis', '//OD//K1Axis', '//R//K1Axis', '//Right//K1Axis'
                ],
                'k2' => [
                    '//nsKM:R/nsKM:List/nsKM:R2/nsKM:Power', '//nsKM:R/nsKM:Median/nsKM:R2/nsKM:Power',
                    '//nsKM:R//nsKM:R2//nsKM:Power', '//*[local-name()="R"]//*[local-name()="R2"]//*[local-name()="Power"]',
                    '//OD/K2','//R/K2','//Right/K2', '//OD//K2', '//R//K2', '//Right//K2',
                    '//*[contains(local-name(), "K2") and (ancestor::*[contains(local-name(), "OD")] or ancestor::*[contains(local-name(), "Right")])]'
                ],
                'k2_axis' => [
                    '//nsKM:R/nsKM:List/nsKM:R2/nsKM:Axis', '//nsKM:R/nsKM:Median/nsKM:R2/nsKM:Axis',
                    '//nsKM:R//nsKM:R2//nsKM:Axis', '//*[local-name()="R"]//*[local-name()="R2"]//*[local-name()="Axis"]',
                    '//OD/K2Axis','//OD/K2_Axis','//R/K2Axis','//Right/K2Axis', '//OD//K2Axis', '//R//K2Axis', '//Right//K2Axis'
                ],
            ],
            'oi' => [
                'k1' => [
                    '//nsKM:L/nsKM:List/nsKM:R1/nsKM:Power', '//nsKM:L/nsKM:Median/nsKM:R1/nsKM:Power',
                    '//nsKM:L//nsKM:R1//nsKM:Power', '//*[local-name()="L"]//*[local-name()="R1"]//*[local-name()="Power"]',
                    '//OI/K1','//OS/K1','//L/K1','//Left/K1', '//OI//K1', '//OS//K1', '//L//K1', '//Left//K1',
                    '//*[contains(local-name(), "K1") and (ancestor::*[contains(local-name(), "OI")] or ancestor::*[contains(local-name(), "OS")] or ancestor::*[contains(local-name(), "Left")])]'
                ],
                'k1_axis' => [
                    '//nsKM:L/nsKM:List/nsKM:R1/nsKM:Axis', '//nsKM:L/nsKM:Median/nsKM:R1/nsKM:Axis',
                    '//nsKM:L//nsKM:R1//nsKM:Axis', '//*[local-name()="L"]//*[local-name()="R1"]//*[local-name()="Axis"]',
                    '//OI/K1Axis','//OS/K1Axis','//OI/K1_Axis','//OS/K1_Axis','//L/K1Axis','//Left/K1Axis', '//OI//K1Axis', '//OS//K1Axis', '//L//K1Axis', '//Left//K1Axis'
                ],
                'k2' => [
                    '//nsKM:L/nsKM:List/nsKM:R2/nsKM:Power', '//nsKM:L/nsKM:Median/nsKM:R2/nsKM:Power',
                    '//nsKM:L//nsKM:R2//nsKM:Power', '//*[local-name()="L"]//*[local-name()="R2"]//*[local-name()="Power"]',
                    '//OI/K2','//OS/K2','//L/K2','//Left/K2', '//OI//K2', '//OS//K2', '//L//K2', '//Left//K2',
                    '//*[contains(local-name(), "K2") and (ancestor::*[contains(local-name(), "OI")] or ancestor::*[contains(local-name(), "OS")] or ancestor::*[contains(local-name(), "Left")])]'
                ],
                'k2_axis' => [
                    '//nsKM:L/nsKM:List/nsKM:R2/nsKM:Axis', '//nsKM:L/nsKM:Median/nsKM:R2/nsKM:Axis',
                    '//nsKM:L//nsKM:R2//nsKM:Axis', '//*[local-name()="L"]//*[local-name()="R2"]//*[local-name()="Axis"]',
                    '//OI/K2Axis','//OS/K2Axis','//OI/K2_Axis','//OS/K2_Axis','//L/K2Axis','//Left/K2Axis', '//OI//K2Axis', '//OS//K2Axis', '//L//K2Axis', '//Left//K2Axis'
                ],
            ],
        ];
        
        $record['keratometry'] = [
            'od' => [
                'k1' => $this->extractFirstValue($xpath, $keratometryQueries['od']['k1']) ?: '',
                'k1_axis' => $this->extractFirstValue($xpath, $keratometryQueries['od']['k1_axis']) ?: '',
                'k2' => $this->extractFirstValue($xpath, $keratometryQueries['od']['k2']) ?: '',
                'k2_axis' => $this->extractFirstValue($xpath, $keratometryQueries['od']['k2_axis']) ?: '',
            ],
            'oi' => [
                'k1' => $this->extractFirstValue($xpath, $keratometryQueries['oi']['k1']) ?: '',
                'k1_axis' => $this->extractFirstValue($xpath, $keratometryQueries['oi']['k1_axis']) ?: '',
                'k2' => $this->extractFirstValue($xpath, $keratometryQueries['oi']['k2']) ?: '',
                'k2_axis' => $this->extractFirstValue($xpath, $keratometryQueries['oi']['k2_axis']) ?: '',
            ],
        ];
        
        // Debug: Log what we extracted for the first file
        if (basename($xmlFile) === 'M-Serial7128_20250719_231801_TOPCON_KR-800_4771007.xml') {
            Log::info('Parsed data for first file: ' . json_encode($record, JSON_PRETTY_PRINT));
        }
        
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
