<?php
require_once 'connect.php';
require_once './vendor/include/connect.php'; // เพิ่มการเชื่อมต่อ MySQL
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่พบ User ID ใน Session']);
    exit();
}

$loggedInUserId = intval($_SESSION['user_id']);

try {
    // ดึง id และ name ของห้องปฏิบัติการที่เจ้าหน้าที่ดูแล
    $queryLabs = "SELECT id, name FROM lab WHERE manager_id = :user_id OR scientists_id = :user_id";
    $stmtLabs = $conn->prepare($queryLabs);
    $stmtLabs->bindParam(':user_id', $loggedInUserId, PDO::PARAM_INT);
    $stmtLabs->execute();
    $managedLabs = $stmtLabs->fetchAll(PDO::FETCH_ASSOC); // ดึงมาเป็น Array ของ Associative Array

    $responseData = [];
    $allUserIds = []; // เก็บ user_id ทั้งหมดเพื่อไป query ชื่อทีเดียว

    if (!empty($managedLabs)) {
        foreach ($managedLabs as $lab) {
            $labId = $lab['id']; // ใช้ id จากข้อมูลที่ดึงมา
            $labName = $lab['name']; // ดึงชื่อห้องปฏิบัติการ

            $queryRequests = "SELECT * FROM request WHERE lab_id = :lab_id ORDER BY id DESC"; // เพิ่ม ORDER BY เพื่อให้รายการใหม่ขึ้นก่อน
            $stmtRequests = $conn->prepare($queryRequests);
            $stmtRequests->bindParam(':lab_id', $labId, PDO::PARAM_INT);
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

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $responseData]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => []]); // ไม่มีห้องที่ดูแล
    }

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>