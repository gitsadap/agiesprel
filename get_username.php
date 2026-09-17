<?php
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);

require_once './vendor/include/connect.php';

if (isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    mysqli_set_charset($c, "utf8");

    $query = "SELECT fname, lname,email FROM user WHERE user_id = ?";
    $stmt = mysqli_prepare($c, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo json_encode($row);
            } else {
                echo json_encode(['error' => "ไม่พบผู้ใช้ที่มี ID: " . $userId]);
            }
            $stmt->close();
            mysqli_close($c); // ใช้ mysqli_close แทน $conn->close()
        } else {
            echo json_encode(['error' => "เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($c)]);
            mysqli_close($c);
        }
    } else {
        echo json_encode(['error' => "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . mysqli_error($c)]);
        mysqli_close($c);
    }
} else {
    echo json_encode(['error' => "ไม่ได้ระบุ User ID"]);
}
?>