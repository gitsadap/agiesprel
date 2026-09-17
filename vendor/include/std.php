<?php
require_once dirname(__DIR__, 2) . '/env.php';

// กำหนด DSN (Data Source Name) ที่คุณตั้งค่าไว้ใน ODBC
$dsn = env('DB_MSSQL_ODBC_DSN', "NU-SQL-01");

// กำหนด Username และ Password (ถ้ามี)
$username = env('DB_MSSQL_USER', "AGRI_TeeradetY");
$password = env('DB_MSSQL_PASSWORD', "");

// สร้างการเชื่อมต่อ ODBC
$conn = odbc_connect($dsn, $username, $password);

if (!$conn) {
    die("<h3>ERROR : ไม่สามารถเชื่อมต่อฐานข้อมูล ODBC ได้: " . odbc_error() . " - " . odbc_errormsg() . "</h3>");
}

echo "เชื่อมต่อฐานข้อมูล ODBC สำเร็จ.<br><br>";

// คำสั่ง SQL เพื่อดึงชื่อฐานข้อมูล
$sqlDbName = "SELECT DB_NAME() AS DatabaseName";
$resultDbName = odbc_exec($conn, $sqlDbName);

if ($resultDbName && odbc_fetch_array($resultDbName)) {
    $databaseName = odbc_result($resultDbName, "DatabaseName");
    echo "รายชื่อตารางในฐานข้อมูล '" . $databaseName . "':<br>";
    odbc_free_result($resultDbName);
} else {
    echo "<h3>ERROR : ไม่สามารถดึงชื่อฐานข้อมูลได้: " . odbc_error($conn) . " - " . odbc_errormsg($conn) . "</h3>";
    // หากไม่สามารถดึงชื่อฐานข้อมูลได้ ก็อาจจะไม่สามารถดึงรายชื่อตารางต่อ
    odbc_close($conn);
    exit();
}

// คำสั่ง SQL เพื่อดึงรายชื่อตารางทั้งหมด (สำหรับ SQL Server)
$sqlTables = "SELECT TABLE_NAME
              FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_CATALOG = '$databaseName'";

$resultTables = odbc_exec($conn, $sqlTables);

if ($resultTables) {
    while ($rowTable = odbc_fetch_array($resultTables)) {
        $tableName = $rowTable['TABLE_NAME'];
        echo "- ตาราง: <b>" . $tableName . "</b><br>";

        // คำสั่ง SQL เพื่อดึงข้อมูลทั้งหมดจากแต่ละตาราง
        $sqlData = "SELECT * FROM [$tableName]";
        $resultData = odbc_exec($conn, $sqlData);

        if ($resultData) {
            echo "<pre>";
            while ($rowData = odbc_fetch_array($resultData)) {
                print_r($rowData);
            }
            echo "</pre><br>";
            odbc_free_result($resultData); // คืนหน่วยความจำ
        } else {
            echo "  เกิดข้อผิดพลาดในการดึงข้อมูลจากตาราง '$tableName': " . odbc_error($conn) . " - " . odbc_errormsg($conn) . "<br><br>";
        }
    }
    odbc_free_result($resultTables); // คืนหน่วยความจำ
} else {
    echo "<h3>ERROR : ไม่สามารถดึงรายชื่อตารางได้: " . odbc_error($conn) . " - " . odbc_errormsg($conn) . "</h3>";
}

odbc_close($conn); // ปิดการเชื่อมต่อ ODBC

?>