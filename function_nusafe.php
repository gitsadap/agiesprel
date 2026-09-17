<?php
require_once 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ไม่พบ User ใน Session กรุณา login']);
    exit();
}

$loggedInUserId = intval($_SESSION['user_id']);

try {
    // ดึง id และ name ของห้องปฏิบัติการที่เจ้าหน้าที่ดูแล
    $queryLabs = "SELECT id, name FROM lab";
    $stmtLabs = $conn->prepare($queryLabs);
    $stmtLabs->execute();
    $managedLabs = $stmtLabs->fetchAll(PDO::FETCH_ASSOC); // ดึงมาเป็น Array ของ Associative Array

    $responseData = [];

    if (!empty($managedLabs)) {
        foreach ($managedLabs as $lab) {
            $labId = $lab['id']; // ใช้ id จากข้อมูลที่ดึงมา
            $labName = $lab['name']; // ดึงชื่อห้องปฏิบัติการ

            $queryRequests = "SELECT * FROM request WHERE lab_id = :lab_id AND stage = '3'";
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
            }

            $responseData[$labId] = ['name' => $labName, 'requests' => $requestsData]; // เก็บชื่อห้องไว้ด้วย
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