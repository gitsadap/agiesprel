<?php
require_once dirname(__DIR__, 2) . '/env.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');

header('Content-Type: application/json; charset=utf-8');

$dbConfig = [
    'server'   => env('DB_MSSQL_ANALYTICS_SERVER', "10.10.98.203"),
    'database' => env('DB_MSSQL_ANALYTICS_DATABASE', "NUDB"),
    'username' => env('DB_MSSQL_ANALYTICS_USER', "AGRI_Teeradety"),
    'password' => env('DB_MSSQL_ANALYTICS_PASSWORD', "")
];

class StudentAnalytics {
    public static function getStudentData($config) {
        try {
            $dsn = "sqlsrv:Server={$config['server']};Database={$config['database']};TrustServerCertificate=1;LoginTimeout=30";
            $conn = new PDO($dsn, $config['username'], $config['password']);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlData = "SELECT * FROM [Agri].[View_Student4AgriFaculty] 
                        WHERE (
                            (LEVGROUPNAME = 'ปริญญาตรี' AND (PROGRAMNAME LIKE '%สิ่งแวดล้อม%' OR PROGRAMNAME LIKE '%ภูมิ%'))
                            OR 
                            (LEVGROUPNAME IN ('ปริญญาโท', 'ปริญญาเอก') AND (
                                PROGRAMNAME LIKE '%การจัดการสิ่งแวดล้อม%' OR 
                                PROGRAMNAME LIKE '%ทรัพยากรธรรมชาติ%' OR 
                                PROGRAMNAME LIKE '%วิทยาศาสตร์สิ่งแวดล้อม%' OR 
                                PROGRAMNAME LIKE '%ภูมิสารสนเทศ%'
                            ))
                        )
                        ORDER BY LEVGROUPNAME DESC, PROGRAMNAME ASC";

            $stmt = $conn->query($sqlData);
            
            $organized = [];
            $summary = []; 
            $grandTotal = ['graduated' => 0, 'lost' => 0, 'active' => 0];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $level = trim($row['LEVGROUPNAME']);
                $program = trim($row['PROGRAMNAME']);
                $status = trim($row['STDSTATUSNAME'] ?? '');
                $stdCode = trim($row['STDCODE'] ?? '');
                
                // ตรรกะการจำแนกสถานะ
                $isGraduated = (mb_strpos($status, 'สำเร็จการศึกษา') !== false);
                $isLost = (mb_strpos($status, 'พ้นสภาพ') !== false || mb_strpos($status, 'ลาออก') !== false || mb_strpos($status, 'คัดชื่อ') !== false);
                
                if (!isset($summary[$level]['programs'][$program])) {
                    $summary[$level]['programs'][$program] = ['graduated' => 0, 'lost' => 0, 'active' => 0];
                }

                if ($isGraduated) {
                    $summary[$level]['programs'][$program]['graduated']++;
                    $grandTotal['graduated']++;
                    $displayStatus = 'graduated';
                } elseif ($isLost) {
                    $summary[$level]['programs'][$program]['lost']++;
                    $grandTotal['lost']++;
                    $displayStatus = 'lost';
                } else {
                    $summary[$level]['programs'][$program]['active']++;
                    $grandTotal['active']++;
                    $displayStatus = 'active';
                }

                // จัดกลุ่มข้อมูลนิสิตรายบุคคล (เพิ่มข้อมูลรหัสปีกลับเข้ามา)
                $organized[$level][$program][] = [
                    'std_code'     => $stdCode,
                    'year'         => substr($stdCode, 0, 2), // ดึงรหัส 2 ตัวแรกมาเป็นปี เช่น 65, 66
                    'fullname'     => trim(($row['PREFIXNAME'] ?? '').($row['STDNAME'] ?? '').' '.($row['STDSURNAME'] ?? '')),
                    'status_text'  => $status,
                    'status_group' => $displayStatus
                ];
            }

            // คำนวณ Success Rate และสรุปผล
            foreach ($summary as $lvl => &$lvlData) {
                foreach ($lvlData['programs'] as $progName => &$stats) {
                    $closedCases = $stats['graduated'] + $stats['lost'];
                    $stats['success_rate'] = ($closedCases > 0) 
                        ? round(($stats['graduated'] / $closedCases) * 100, 2) . '%' 
                        : '0%';
                }
            }

            return [
                'status' => 'success',
                'analysis_overview' => [
                    'grand_total' => $grandTotal['graduated'] + $grandTotal['lost'] + $grandTotal['active'],
                    'total_graduated' => $grandTotal['graduated'],
                    'total_lost' => $grandTotal['lost'],
                    'total_active' => $grandTotal['active'],
                    'overall_success_rate' => (($grandTotal['graduated'] + $grandTotal['lost']) > 0) ? round(($grandTotal['graduated'] / ($grandTotal['graduated'] + $grandTotal['lost'])) * 100, 2) . '%' : '0%'
                ],
                'data_summary' => $summary,
                'results' => $organized
            ];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

$result = StudentAnalytics::getStudentData($dbConfig);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);