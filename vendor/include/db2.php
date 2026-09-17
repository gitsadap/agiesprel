<?php
require_once dirname(__DIR__, 2) . '/env.php';

$host = env('DB_PG_HOST', '10.10.58.21');
$port = env('DB_PG_PORT', '5432');
$dbname = env('DB_PG_DATABASE', 'ESPReL');
$user = env('DB_PG_USER', 'agi');
$password = env('DB_PG_PASSWORD', '');

try {
    // เชื่อมต่อฐานข้อมูล PostgreSQL ด้วย PDO
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "เชื่อมต่อฐานข้อมูล '$dbname' สำเร็จ.<br><br>";

    // ดึงรายชื่อตารางทั้งหมดในฐานข้อมูล PostgreSQL ยกเว้น 'spatial_ref_sys'
    $resultTables = $conn->query("
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        AND table_name != 'spatial_ref_sys'
    ");

    if ($resultTables) {
        echo "รายชื่อตารางในฐานข้อมูล '$dbname' (ไม่รวม spatial_ref_sys):<br>";
        while ($rowTable = $resultTables->fetch(PDO::FETCH_ASSOC)) {
            $tableName = $rowTable['table_name'];
            echo "- ตาราง: <b>" . $tableName . "</b><br>";

            // Select * จากแต่ละตาราง
            $sqlData = "SELECT * FROM \"$tableName\""; // ใช้เครื่องหมาย double quote สำหรับชื่อตาราง
            $resultData = $conn->query($sqlData);

            if ($resultData) {
                $data = $resultData->fetchAll(PDO::FETCH_ASSOC);
                if ($data) {
                    echo "<pre>";
                    print_r($data);
                    echo "</pre><br>";
                } else {
                    echo "  ไม่มีข้อมูลในตาราง '$tableName'.<br><br>";
                }
                $resultData->closeCursor(); // คืน Cursor
            } else {
                echo "  เกิดข้อผิดพลาดในการดึงข้อมูลจากตาราง '$tableName': " . $conn->errorInfo()[2] . "<br><br>";
            }
        }
        $resultTables->closeCursor(); // คืน Cursor
    } else {
        echo "เกิดข้อผิดพลาดในการดึงรายชื่อตาราง: " . $conn->errorInfo()[2] . "<br>";
    }

    // ปิดการเชื่อมต่อ (PDO จะปิดการเชื่อมต่อเมื่อตัวแปร $conn ถูกทำลาย)
    $conn = null;

} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage() . "<br>";
    die();
}

?>