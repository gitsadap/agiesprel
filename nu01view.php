<?php
// ตั้งค่า Header เป็น JSON
header('Content-Type: application/json');
require_once 'function.php';
// ตรวจสอบว่า Method เป็น POST
if (!isset($_GET['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No user_id provided']);
    exit;
}

$user_id = intval($_GET['user_id']);

try {
    $stmt = $conn->prepare("SELECT file FROM lab_cert WHERE cert = :user_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(['status' => 'success', 'path' => $row['file']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบไฟล์เอกสาร NU-LAB-01']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>