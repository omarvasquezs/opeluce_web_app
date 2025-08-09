<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;
use Carbon\Carbon;

class RefractionController extends Controller
{
    private $sharedFolderPath = '//127.0.0.1/kr'; // Windows UNC path
    
    /**
     * Get refraction records from XML files in shared folder
     */
    public function getRecords(Request $request)
    {
        try {
            $limit = $request->input('limit', 100);
            
            // Convert UNC path to local filesystem path if on Windows
            $xmlPath = $this->getXmlPath();
            
            // For development/testing, create mock data if folder doesn't exist
            if (!is_dir($xmlPath)) {
                Log::warning('XML folder not accessible: ' . $xmlPath);
                
                // Return mock data for testing
                return response()->json([
                    [
                        'id' => 'mock-1',
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
                    ]
                ]);
            }
            
            $records = [];
            
            // Look for XML files in the directory
            $xmlFiles = glob($xmlPath . '/*.xml');
            
            if (empty($xmlFiles)) {
                return response()->json([]);
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
            
            return response()->json($records);
            
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
        // Check if we're on Windows and can access the UNC path directly
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // On Windows, try the UNC path directly
            return str_replace('/', '\\', $this->sharedFolderPath);
        } else {
            // On Linux, try to mount the SMB share or use a local mapped path
            // You might need to configure SMB mounting on Linux
            $localPath = '/mnt/kr'; // Adjust this path as needed
            
            if (is_dir($localPath)) {
                return $localPath;
            }
            
            // Fallback: try to access via smbclient or other methods
            // For now, return the original path and let it fail gracefully
            return $this->sharedFolderPath;
        }
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
        $record = [
            'id' => basename($xmlFile, '.xml'),
            'filename' => basename($xmlFile),
            'examination_date' => $this->extractXmlValue($xpath, '//Date') ?: date('Y-m-d'),
            'examination_time' => $this->extractXmlValue($xpath, '//Time') ?: date('H:i:s'),
            'patient_id' => $this->extractXmlValue($xpath, '//PatientID') ?: '',
        ];
        
        // Extract OD (Right Eye) refraction data
        $record['od'] = [
            'esf' => $this->extractXmlValue($xpath, '//OD/Sphere') ?: '',
            'cil' => $this->extractXmlValue($xpath, '//OD/Cylinder') ?: '',
            'eje' => $this->extractXmlValue($xpath, '//OD/Axis') ?: '',
        ];
        
        // Extract OI (Left Eye) refraction data
        $record['oi'] = [
            'esf' => $this->extractXmlValue($xpath, '//OI/Sphere') ?: $this->extractXmlValue($xpath, '//OS/Sphere') ?: '',
            'cil' => $this->extractXmlValue($xpath, '//OI/Cylinder') ?: $this->extractXmlValue($xpath, '//OS/Cylinder') ?: '',
            'eje' => $this->extractXmlValue($xpath, '//OI/Axis') ?: $this->extractXmlValue($xpath, '//OS/Axis') ?: '',
        ];
        
        // Extract DIP (Pupillary Distance)
        $record['dip'] = $this->extractXmlValue($xpath, '//PD') ?: $this->extractXmlValue($xpath, '//PupillaryDistance') ?: '';
        
        // Extract keratometry data if available
        $record['keratometry'] = [
            'od' => [
                'k1' => $this->extractXmlValue($xpath, '//OD/K1') ?: '',
                'cil' => $this->extractXmlValue($xpath, '//OD/K2') ?: '',
            ],
            'oi' => [
                'k1' => $this->extractXmlValue($xpath, '//OI/K1') ?: $this->extractXmlValue($xpath, '//OS/K1') ?: '',
                'cil' => $this->extractXmlValue($xpath, '//OI/K2') ?: $this->extractXmlValue($xpath, '//OS/K2') ?: '',
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
}
