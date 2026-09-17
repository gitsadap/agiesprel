<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once 'connect.php';
require_once './vendor/include/connect.php';


$user_id = $_GET['user_id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    echo json_encode(['error' => 'Missing or invalid user_id']);
    exit;
}

$latestUpload = getLatestUploadInfo($conn, $user_id);
if ($latestUpload === "0") {
    echo json_encode(['error' => 'ไม่พบข้อมูลอัปโหลดล่าสุดของผู้ใช้นี้']);
    exit;
}

$upload_id = $latestUpload['upload_id'];
$upload_type = $latestUpload['upload_type'];

$certData = getUploadPathsByType($conn, $upload_id, $upload_type);

if (empty($certData)) {
    echo json_encode(['error' => 'ไม่สามารถโหลดข้อมูลใบประกาศ หรือไม่มีไฟล์ที่รองรับ']);
    exit;
}

echo json_encode($certData);

function getLatestUploadInfo($conn, $user_id) {
    $query = "SELECT upload_id, upload_type
              FROM uploads
              WHERE user_id = :user_id
              ORDER BY upload_id DESC
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result; // ส่งคืน array ที่มี 'upload_id' และ 'upload_type'
    } else {
        return "0"; // ไม่พบข้อมูล หรือ count เท่ากับ 0
    }
}

function getUploadPathsByType($conn, $upload_id, $certtype) {
    $query = "SELECT file_path FROM upload_paths WHERE upload_id = :upload_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
    $result = [];

    if ($certtype === 'elearning') {
        if ($stmt->execute()) {
            $paths = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($paths as $path) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $result['image'] = $path;
                } elseif ($ext === 'pdf') {
                    $result['pdf'] = $path;
                }
            }
            $result['certype'] = $certtype;
            return $result; // คืนเป็น array ที่มี 'image' และ/หรือ 'pdf' เป็น key
        } else {
            print_r($stmt->errorInfo());
            return [];
        }
    } elseif ($certtype === 'nu') {
        $query_nu = "SELECT file_path, file_order FROM upload_paths WHERE upload_id = :upload_id ORDER BY file_order ASC";
        $stmt_nu = $conn->prepare($query_nu);
        $stmt_nu->bindParam(':upload_id', $upload_id, PDO::PARAM_INT);
        $results_nu = [];
        if ($stmt_nu->execute()) {
            while ($row = $stmt_nu->fetch(PDO::FETCH_ASSOC)) {
                $ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                $type = '';
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $type = 'image';
                } elseif ($ext === 'pdf') {
                    $type = 'pdf';
                }
                $results_nu[$row['file_order']] = ['path' => $row['file_path'], 'type' => $type];
            }
        } else {
            print_r($stmt_nu->errorInfo());
        }
        $results_nu['certype'] = $certtype;
        return $results_nu; // คืนเป็น array ที่มี index เป็นลำดับ และมี 'path' กับ 'type'
    } else {
        return [];
    }
}

?>