<?php
require_once 'connect.php';
require_once './vendor/include/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION["user_id"] ?? null;

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}


$query = "SELECT * FROM lab ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->execute();
$labs = $stmt->fetchAll(PDO::FETCH_ASSOC);    

mysqli_set_charset($c, "utf8");

$query = "SELECT * FROM user WHERE user_id = ?"; // เปลี่ยน placeholder เป็น ?
$stmt = $c->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $user_id); // "i" หมายถึง integer, เปลี่ยนตามประเภทข้อมูลของ user_id
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
} else {
    // จัดการกรณีที่ prepare ล้มเหลว
    echo "Error preparing statement: " . $c->error;
}

if(isset($row['acad_pos_id'])){
    $pos_id= $row['acad_pos_id'];
    $query = "SELECT th_name FROM academic_position WHERE acad_pos_id = ?";
    $stmt = $c->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $pos_id); // "i" หมายถึง integer
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $POS = $result->fetch_column();
            $result->free();
        }
        $stmt->close();
    } else {
        // จัดการกรณี prepare ล้มเหลว
        
        echo "Error preparing statement: " . $c->error;
    }
}

if (isset($row['depart_id'])) {
    $dep_id = $row['depart_id'];
    $query2 = "SELECT dep FROM department WHERE code = :depart_id";
    $stmt2 = $conn->prepare($query2);

    if ($stmt2) {
        // Bind parameter โดยใช้ Named Placeholder
        $stmt2->bindParam(':depart_id', $dep_id, PDO::PARAM_INT);

        if ($stmt2->execute()) {
            // ดึงผลลัพธ์
            $DEP = $stmt2->fetchColumn(); // เทียบเท่า fetch_column() ของ MySQLi

            // ไม่จำเป็นต้อง free() result ใน PDO
        } else {
            // จัดการกรณี execute ล้มเหลว
            echo "Error executing statement: " . print_r($stmt2->errorInfo(), true);
        }

        // ปิด Statement
        $stmt2->closeCursor(); // เทียบเท่า $stmt2->close() ของ MySQLi
    } else {
        // จัดการกรณี prepare ล้มเหลว
        echo "Error preparing statement: " . $c->error;
    }
}

function getRequestCount($conn, $lab_id) {
    $query = "SELECT
            COUNT(CASE WHEN purpose = 'R' THEN 1 END) AS R,
            COUNT(CASE WHEN purpose = 'S' THEN 1 END) AS S,
            COUNT(CASE WHEN purpose = 'T' THEN 1 END) AS T
        FROM request WHERE lab_id = :lab_id ";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getLabs($conn, $user_id) {
    $query = "SELECT id,name,manager,room FROM lab WHERE manager_id = :user_id or scientists_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getContactLabs($conn, $lab_id) {
    $query = "SELECT c.*,l.name from contact c join lab l on l.id = c.id_lab WHERE id_lab = :lab_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['id_lab'])) {
        return $result; // ส่งคืนค่าในคอลัมน์ description ล่าสุด
    } else {
        return "0"; // หรือค่าอื่น ๆ ตามสถานการณ์
    }
}

function getAllLabs($conn) {
    $query = "SELECT id,name,manager,room FROM lab";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



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
function getSignStatus($conn, $user_id) {
    $query = "SELECT sign
              FROM users
              WHERE user_id = :user_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['sign'])) {
        return $result['sign']; // ส่งคืนค่าในคอลัมน์ description ล่าสุด
    } else {
        return "0"; // หรือค่าอื่น ๆ ตามสถานการณ์
    }
}
function getCertStatus($conn, $user_id) {
    $query = "SELECT stage,path,lab_id
              FROM request
              WHERE user_id = :user_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['stage'])) {
        return $result; // ส่งคืนค่าในคอลัมน์ description ล่าสุด
    } else {
        return "0"; // หรือค่าอื่น ๆ ตามสถานการณ์
    }
}

function getnu01($conn, $user_id) {
    $query = "SELECT file
              FROM lab_cert
              WHERE cert = :user_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['file'])) {
        return $result['file']; // ส่งคืนค่า path โดยตรง ไม่ใช่ array ทั้งก้อน
    } else {
        return "0"; // ส่งคืนเป็นสตริงว่าง ซึ่งสามารถตรวจสอบได้ง่ายกว่า "0" 
    }
}


function getApprovalStatus($conn, $user_id) {
    $query = "SELECT description
              FROM uploads
              WHERE user_id = :user_id
              ORDER BY upload_id DESC
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['description'])) {
        return $result['description']; // ส่งคืนค่าในคอลัมน์ description ล่าสุด
    } else {
        return "0"; // หรือค่าอื่น ๆ ตามสถานการณ์
    }
}

function getUploadPathsByType($conn, $upload_id, $certtype) {
    $query = "SELECT file_path FROM upload_paths WHERE upload_id = :upload_id ";
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
        return $results_nu; // คืนเป็น array ที่มี index เป็นลำดับ และมี 'path' กับ 'type'
    } else {
        return [];
    }
}

?>