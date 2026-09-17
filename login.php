<?php
require_once __DIR__ . '/env.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
// รับค่าจาก JavaScript
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$userType = $_POST['userType'] ?? ''; // รับค่า userType ที่ผู้ใช้เลือก

// เชื่อมต่อ LDAP
$ldap_server = env('LDAP_SERVER', 'ldap://10.10.10.71');
$ldap_domain = env('LDAP_DOMAIN', '@nu.local');
$ldap_user = $username . $ldap_domain;

// ปิดการตรวจสอบ Certificate ป้องกันปัญหา TLS
if (!defined('LDAP_OPT_X_TLS_REQUIRE_CERT')) {
    define('LDAP_OPT_X_TLS_REQUIRE_CERT', 24582);
}
if (!defined('LDAP_OPT_X_TLS_NEVER')) {
    define('LDAP_OPT_X_TLS_NEVER', 0);
}
ldap_set_option(NULL, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);

$ldap_conn = ldap_connect($ldap_server);
ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

$bind_success = false;
if ($ldap_conn) {
    // ลอง Bind แบบปกติ
    $bind_success = @ldap_bind($ldap_conn, $ldap_user, $password);

    if (!$bind_success) {
        $errno = ldap_errno($ldap_conn);
        // หากเซิร์ฟเวอร์ต้องการการเชื่อมต่อที่ปลอดภัย (Error 8) ให้เปิด StartTLS แล้วลอง Bind ใหม่
        if ($errno == 8) {
            if (@ldap_start_tls($ldap_conn)) {
                $bind_success = @ldap_bind($ldap_conn, $ldap_user, $password);
            }
        }
    }
}

if ($bind_success) {
    // ✅ LDAP ผ่าน
    ldap_unbind($ldap_conn);

    // จัดการกรณี userType เป็น student
    if ($userType == 'student') {
        $serverName = env('DB_MSSQL_HOST', '10.10.22.9'); // SQL Server
        $databaseName = env('DB_MSSQL_DATABASE', 'NUDB');
        $uid = env('DB_MSSQL_USER', 'AGRI_TeeradetY');
        $pwd = env('DB_MSSQL_PASSWORD', '');
        
        try {
            // สร้างการเชื่อมต่อ PDO ด้วย sqlsrv driver
            $conn = new PDO("sqlsrv:server=$serverName;Database=$databaseName;Encrypt=no;TrustServerCertificate=yes", $uid, $pwd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $schemaName = "Agri"; // ชื่อ Schema
            $viewName = "View_Student4AgriFaculty"; // ชื่อ View
            
            // ปรับปรุงคำสั่ง SQL ให้ปลอดภัยขึ้นด้วยการใช้ parameterized query
            $sqlData = "SELECT * FROM [$schemaName].[$viewName] WHERE STDCODE = :username";
            $stmt = $conn->prepare($sqlData);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $studentData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = count($studentData);
            
            if ($count > 0) {
                // ดึงข้อมูลแรกที่ได้ (กรณีที่อาจมีมากกว่า 1 แถว)
                $firstStudent = $studentData[0];
                
                // ป้องกัน Session Fixation
                session_regenerate_id(true);

                // สร้าง session สำหรับ student
                $_SESSION["user_id"] = $username;
                $_SESSION["user_type"] = "student";
                
                // เพิ่มข้อมูลชื่อ นามสกุล และสาขาเข้าไปใน session
                $_SESSION["std_prefix"] = $firstStudent['PREFIXNAME'] ?? ''; 
                $_SESSION["std_firstname"] = $firstStudent['STDNAME'] ?? '';
                $_SESSION["std_lastname"] = $firstStudent['STDSURNAME'] ?? '';
                $_SESSION["std_program"] = $firstStudent['PROGRAMACADNAME'] ?? '';
                $_SESSION["level"] = $firstStudent['LEVGROUPNAME'] ?? '';
                $_SESSION["std_department"] = $firstStudent['DEPARTMENTNAME'] ?? '';
                $_SESSION["std_faculty"] = $firstStudent['FACULTYNAME'] ?? '';
                $_SESSION["STDMAIL"] = $firstStudent['STDMAIL'] ?? '';
                
                // เตรียมข้อมูลสำหรับ student
                $data = [];
                foreach ($studentData as $rowData) {
                    $stdcode = $rowData['STDCODE'];
                    $data[$stdcode] = $rowData;
                }
                
                echo json_encode([
                    'success' => true,
                    'username' => $username,
                    'userType' => 'student', // ระบุ userType เป็น student ตามที่ต้องการ
                    'fullname' => ($_SESSION["std_prefix"] . ' ' . $_SESSION["std_firstname"] . ' ' . $_SESSION["std_lastname"]),
                    'program' => $_SESSION["std_program"],
                    'level' => $_SESSION["level"],
                    'email' => $_SESSION["STDMAIL"],
                    'data' => $data,
                    'count' => $count
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลนิสิตในระบบ'
                ], JSON_UNESCAPED_UNICODE);
            }
            
            $conn = null; // ปิดการเชื่อมต่อ
            exit; // จบการทำงานหลังจากจัดการ student
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูลนิสิต: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // ถ้าไม่ใช่ student ให้ดำเนินการต่อกับ MySQL (ส่วนของ user ทั่วไป)
    $db_host = env('DB_MYSQL_HOST', '10.10.58.16');
    $db_port = (int) env('DB_MYSQL_PORT', 3306);
    $db_user = env('DB_MYSQL_USER', 'gitsadap');
    $db_pass = env('DB_MYSQL_PASSWORD', '');
    $db_name = env('DB_MYSQL_DATABASE', 'db_user');

    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    if ($mysqli->connect_error) {
        echo json_encode(['success' => false, 'message' => 'MySQL connect error: ' . $mysqli->connect_error]);
        exit;
    }

    // ตรวจสอบใน userdb
    $stmt = $mysqli->prepare("SELECT user_id, username FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData) {
        $pg_host = env('DB_PG_HOST', '10.10.58.21');
        $pg_port = env('DB_PG_PORT', '5432');
        $pg_user = env('DB_PG_USER', 'agi');
        $pg_pass = env('DB_PG_PASSWORD', '');
        $pg_dbname = env('DB_PG_DATABASE', 'ESPReL');

        try {
            // สร้าง instance ของ PDO สำหรับ PostgreSQL
            $pdo_pg = new PDO("pgsql:host=$pg_host;port=$pg_port;dbname=$pg_dbname", $pg_user, $pg_pass);
            $pdo_pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $loggedInUserId = $userData['user_id'];

            // ตรวจสอบว่าผู้ใช้มีในระบบ PostgreSQL หรือไม่
            $checkUserSql = "SELECT COUNT(*) FROM users WHERE user_id = :user_id";
            $checkUserStmt = $pdo_pg->prepare($checkUserSql);
            $checkUserStmt->bindParam(':user_id', $loggedInUserId, PDO::PARAM_INT);
            $checkUserStmt->execute();
            $userCount = $checkUserStmt->fetchColumn();

            if ($userCount > 0) {
                // อัพเดทเวลาเข้าสู่ระบบ
                $updateUserSql = "UPDATE users SET login_at = NOW() WHERE user_id = :user_id";
                $updateUserStmt = $pdo_pg->prepare($updateUserSql);
                $updateUserStmt->bindParam(':user_id', $loggedInUserId, PDO::PARAM_INT);
                $updateUserStmt->execute();
            } else {
                // เพิ่มผู้ใช้ใหม่
                $insertUserSql = "INSERT INTO users (user_id, created_at) VALUES (:user_id, NOW())";
                $insertUserStmt = $pdo_pg->prepare($insertUserSql);
                $insertUserStmt->bindParam(':user_id', $loggedInUserId, PDO::PARAM_INT);
                $insertUserStmt->execute();
            }

            // ฟังก์ชั่นตรวจสอบ role จาก adminlist
            function check_role_from_adminlist($pdo, $username) {
                $query = "SELECT lab, executive FROM adminlist WHERE username = :username";
                $stmt_pg = $pdo->prepare($query);
                $stmt_pg->bindParam(':username', $username);
                $stmt_pg->execute();
                $row_pg = $stmt_pg->fetch(PDO::FETCH_ASSOC);
            
                if ($row_pg) {
                    if ($row_pg['executive'] == 1) return "executive";
                    if ($row_pg['lab'] == 1) return "lab";
                }
                return "researcher";
            }
            
            // ระดับสิทธิ์: 1 = researcher, 2 = lab, 3 = executive
            function has_access($requestedRole, $actualRole) {
                $rolesHierarchy = [
                    'researcher' => 1,
                    'lab' => 2,
                    'executive' => 3
                ];
                return $rolesHierarchy[$actualRole] >= $rolesHierarchy[$requestedRole];
            }
            
            $requestedUserType = $_POST['userType'] ?? 'researcher'; // รับจากฟอร์ม
            $calculatedUserType = check_role_from_adminlist($pdo_pg, $username); // สิทธิ์จริงจากระบบ
            
            if (!has_access($requestedUserType, $calculatedUserType)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'คุณไม่มีสิทธิ์เข้าสู่ระบบในประเภทผู้ใช้ที่เลือก' 
                ]);
                exit;
            }
            
            // ป้องกัน Session Fixation
            session_regenerate_id(true);
            
            echo json_encode([
                'success' => true,
                'username' => $userData['username'],
                'userType' => $requestedUserType // ใช้ role ที่เลือกในการ redirect
            ]);
            
            $_SESSION["user_id"] = $userData["user_id"];
            $_SESSION["user_type"] = $requestedUserType;
            
            // ตรวจสอบลายเซ็น
            try {
                $check_sign_query = "SELECT sign FROM user_signatures WHERE user_id = :user_id LIMIT 1";
                $stmt_check_sign = $pdo_pg->prepare($check_sign_query);
                $stmt_check_sign->bindParam(':user_id', $loggedInUserId, PDO::PARAM_INT);
                $stmt_check_sign->execute();
                $sign_result = $stmt_check_sign->fetch(PDO::FETCH_ASSOC);
            
                $_SESSION["sign"] = $sign_result && $sign_result['sign'] ? $sign_result['sign'] : 0;
            } catch (PDOException $e) {
                $_SESSION["sign"] = 0;
            }

            // ปิด connection PDO
            $pdo_pg = null;

        } catch (PDOException $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'เกิดข้อผิดพลาดในการจัดการข้อมูลผู้ใช้: ' . $e->getMessage()
            ]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้ในระบบ']);
    }

    $stmt->close();
    $mysqli->close();

} else {
    // ❌ LDAP ไม่ผ่าน
    echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
}
?>