<?php
require_once __DIR__ . '/env.php';
session_start();
header('Content-Type: application/json');

// รับค่าจาก JavaScript
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

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

// ตรวจสอบการเชื่อมต่อและการ bind
if ($bind_success) {
    // LDAP Authentication สำเร็จ
    ldap_unbind($ldap_conn);
    
    // รายชื่อผู้มีสิทธิ์เข้าใช้งาน
    $adminUsersConfig = env('ADMIN_USERS', 'wiphadab,wisas,nungruthait,gitsadap');
    $authorized_users = array_map('trim', explode(',', $adminUsersConfig));
    
    // ตรวจสอบว่า username อยู่ในรายชื่อผู้มีสิทธิ์หรือไม่
    if (in_array($username, $authorized_users)) {
        // ผู้ใช้มีสิทธิ์ - สร้าง session และส่งข้อมูลกลับ
        session_regenerate_id(true);

        $_SESSION['user_id'] = $username;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // บันทึกลง log (ถ้าต้องการ)
        $log_message = date('Y-m-d H:i:s') . " - User $username logged in successfully.\n";
        file_put_contents('login_log.txt', $log_message, FILE_APPEND);
        
        echo json_encode(['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ']);
    } else {
        // ผู้ใช้ไม่มีสิทธิ์
        echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าใช้งานระบบนี้']);
    }
} else {
    // LDAP Authentication ไม่สำเร็จ
    if ($ldap_conn) {
        ldap_unbind($ldap_conn);
    }
    
    // บันทึกลง log (ถ้าต้องการ)
    $log_message = date('Y-m-d H:i:s') . " - Failed login attempt for user $username.\n";
    file_put_contents('login_log.txt', $log_message, FILE_APPEND);
    
    echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
}
?>