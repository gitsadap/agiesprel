<?php
require_once dirname(__DIR__, 2) . '/env.php';

$host = env('DB_MYSQL_HOST', "10.10.58.16");
$port = (int) env('DB_MYSQL_PORT', 3306);
$user = env('DB_MYSQL_USER', "gitsadap");
$pw = env('DB_MYSQL_PASSWORD', "");
$dbname = env('DB_MYSQL_DATABASE', "db_user");

// เชื่อมต่อฐานข้อมูลด้วย mysqli
$c = mysqli_connect($host, $user, $pw, $dbname, $port);
if (!$c) {
    $response = array(
        'success' => false,
        'error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้: ' . mysqli_connect_error()
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
mysqli_set_charset($c, "utf8"); // ตั้งค่า charset เป็น utf8 เพื่อรองรับภาษาไทย

$output = array(
    'success' => true,
    'database' => $dbname,
    'tables' => array()
);

// ดึงรายชื่อตารางทั้งหมดในฐานข้อมูล
$resultTables = mysqli_query($c, "SHOW TABLES FROM `$dbname`");

if ($resultTables) {
    while ($rowTable = mysqli_fetch_row($resultTables)) {
        $tableName = $rowTable[0];
        $tableData = array(
            'name' => $tableName,
            'data' => array()
        );

        // Select * จากแต่ละตาราง
        $sqlData = "SELECT * FROM `$tableName`";
        $resultData = mysqli_query($c, $sqlData);

        if ($resultData) {
            $data = mysqli_fetch_all($resultData, MYSQLI_ASSOC);
            if ($data) {
                $tableData['data'] = $data;
            } else {
                $tableData['message'] = "ไม่มีข้อมูลในตารางนี้.";
            }
            mysqli_free_result($resultData); // คืนหน่วยความจำ
        } else {
            $tableData['error'] = "เกิดข้อผิดพลาดในการดึงข้อมูลจากตาราง '$tableName': " . mysqli_error($c);
        }
        $output['tables'][] = $tableData;
    }
    mysqli_free_result($resultTables); // คืนหน่วยความจำ
} else {
    $output['success'] = false;
    $output['error'] = "เกิดข้อผิดพลาดในการดึงรายชื่อตาราง: " . mysqli_error($c);
}

mysqli_close($c);

header('Content-Type: application/json');
echo json_encode($output);

?>