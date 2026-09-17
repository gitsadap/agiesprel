<?php
require_once 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่พบ User ID ใน Session']);
    exit();
}

// ตรวจสอบถ้ามาจาก loginnusafe จะเป็น string (เช่น 'gitsadap')
$sessionUserId = $_SESSION['user_id'];
$loggedInUserId = is_numeric($sessionUserId) ? intval($sessionUserId) : $sessionUserId;

file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . " - Exec function called. sessionUserId = " . var_export($sessionUserId, true) . ", loggedInUserId = " . var_export($loggedInUserId, true) . "\n", FILE_APPEND);

try {
    // ดึง id และ name ของห้องปฏิบัติการที่เจ้าหน้าที่ดูแล
    $queryLabs = "SELECT id, name FROM lab";
    $stmtLabs = $conn->prepare($queryLabs);
    $stmtLabs->execute();
    $managedLabs = $stmtLabs->fetchAll(PDO::FETCH_ASSOC); // ดึงมาเป็น Array ของ Associative Array

    $responseData = [];
    $allUserIds = [];

    if (!empty($managedLabs)) {
        foreach ($managedLabs as $lab) {
            $labId = $lab['id']; // ใช้ id จากข้อมูลที่ดึงมา
            $labName = $lab['name']; // ดึงชื่อห้องปฏิบัติการ
            
            // ตรวจสอบว่าเป็น Superuser หรือไม่
            $adminUsersConfig = function_exists('env') ? env('ADMIN_USERS', 'wiphadab,wisas,nungruthait,gitsadap') : 'wiphadab,wisas,nungruthait,gitsadap';
            $adminStrings = array_map('trim', explode(',', $adminUsersConfig));
            if ($loggedInUserId == 13 || in_array($loggedInUserId, $adminStrings)) {
                // Superuser - ดูข้อมูลทั้งหมดโดยไม่มีเงื่อนไข deansign
                $queryRequests = "SELECT * FROM request WHERE (lab_id = :lab_id AND stage > '1')";
                $stmtRequests = $conn->prepare($queryRequests);
                $stmtRequests->bindParam(':lab_id', $labId, PDO::PARAM_INT);
            } else {
                // ชั่วคราว: ยกเลิกการกรอง deansign เพื่อให้ผู้บริหารเห็นข้อมูลทั้งหมด (เพื่อใช้ทดสอบหาต้นตอ)
                $queryRequests = "SELECT * FROM request WHERE (lab_id = :lab_id AND stage > '1')";
                $stmtRequests = $conn->prepare($queryRequests);
                $stmtRequests->bindParam(':lab_id', $labId, PDO::PARAM_INT);
                // $stmtRequests->bindParam(':dean_sign', $loggedInUserId, PDO::PARAM_INT);
            }
            
            $stmtRequests->execute();
            $requestsData = $stmtRequests->fetchAll(PDO::FETCH_ASSOC);

            foreach ($requestsData as &$row) {
                // ทำพาธให้เป็น relative อย่างสมบูรณ์ เพื่อป้องกัน Nginx 404 Redirect
                $cleanPath = $row['path'];
                $cleanPath = str_replace("/esprel/export/", "export/", $cleanPath);
                $cleanPath = str_replace("./export/", "export/", $cleanPath);
                $row['path'] = $cleanPath;
                
                if (!empty($row['user_id'])) {
                    $allUserIds[] = intval($row['user_id']);
                }
            }

            $responseData[$labId] = ['name' => $labName, 'requests' => $requestsData]; // เก็บชื่อห้องไว้ด้วย
        }

        // ดึงชื่อนักวิจัยทั้งหมดแบบรวดเดียว (Batch Query)
        $userNames = [];
        if (!empty($allUserIds)) {
            $uniqueUserIds = array_unique($allUserIds);
            $idList = implode(',', $uniqueUserIds);
            
            require_once './vendor/include/connect.php'; // โหลดการเชื่อมต่อ MySQL ($c)
            mysqli_set_charset($c, "utf8");
            $queryUsers = "SELECT user_id, fname, lname FROM user WHERE user_id IN ($idList)";
            $resultUsers = mysqli_query($c, $queryUsers);
            if ($resultUsers) {
                while ($userRow = mysqli_fetch_assoc($resultUsers)) {
                    $userNames[$userRow['user_id']] = $userRow['fname'] . ' ' . $userRow['lname'];
                }
            }
        }

        // นำชื่อที่ได้ไปใส่กลับใน responseData
        foreach ($responseData as $labId => &$labInfo) {
            foreach ($labInfo['requests'] as &$row) {
                $uid = $row['user_id'];
                $row['user_fullname'] = isset($userNames[$uid]) ? $userNames[$uid] : 'ไม่พบชื่อ';
            }
        }

        // เพิ่มข้อมูล metadata ใน response สำหรับ debug
        $metadata = [
            'user_id' => $sessionUserId,
            'logged_in_int' => $loggedInUserId,
            'is_superuser' => ($loggedInUserId == 13 || in_array($loggedInUserId, $adminStrings)),
            'total_labs' => count($managedLabs),
            'query_timestamp' => date('Y-m-d H:i:s')
        ];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'data' => $responseData,
            'metadata' => $metadata
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => [], 'metadata' => ['user_id' => $sessionUserId, 'logged_in_int' => $loggedInUserId]]); // ไม่มีห้องที่ดูแล
    }

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>