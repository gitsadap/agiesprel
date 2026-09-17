<?php

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user_id']);
    header("location: index.php");
}
require_once 'function.php';

$loggedInUserId = $_SESSION['user_id']; // ดึง user_id ของนักวิจัยที่ล็อกอิน

// ดึงรายชื่อ Lab ทั้งหมด
$labs = getLabs($conn,$loggedInUserId);
if (count($labs) == 1) {
    $lab_id = $labs[0]['id'];
    $_SESSION['lab_id'] = $lab_id;
} else {
    $lab_id = null;
    unset($_SESSION['lab_id']);
}

$POS2 = $POS ?? '';
   

if($dep_id < 2 || isset($dep_id)){
    $dr = '';
}
if($dep_id>1 && $dep_id <5){
    $dr = 'ดร.';
}
//$requests = getRequest($conn, $lab_id);
//$counts = getRequestCount($conn, $lab_id);
//echo $counts['R_count'];


?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการใบรับรองนักวิจัยเพื่อใช้ประกอบในการขอทุน (สำหรับเจ้าหน้าที่ Lab)</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Prompt) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- เพิ่ม AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        :root {
            --primary-color: #2E7D32;
            --primary-light: #4CAF50;
            --primary-dark: #1B5E20;
            --accent-color: #8BC34A;
            --text-light: #F1F8E9;
            --text-dark: #263238;
            --bg-light: #F9FBE7;
            --bg-gray: #F5F5F5;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        /* Navbar styling */
        .navbar-custom {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--primary-light);
            padding: 2px;
            transition: var(--transition);
        }
        
        .logo-circle:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .logo-circle img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }
        
        .system-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--primary-dark);
            margin-bottom: 2px;
        }
        
        .system-subtitle {
            font-size: 0.85rem;
            color: var(--text-dark);
            opacity: 0.8;
        }
        
        /* Card styling */
        .dashboard-card {
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: 20px;
        }
        
        .dashboard-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }
        
        .card-header-custom {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .card-body-custom {
            padding: 20px;
        }
        
        /* Table styling */
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .table-green {
            width: 100%;
            background: white;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table-green thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 12px;
            text-align: left;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table-green tbody tr {
            transition: var(--transition);
        }
        
        .table-green tbody tr:nth-child(odd) {
            background-color: rgba(139, 195, 74, 0.05);
        }
        
        .table-green tbody tr:hover {
            background-color: rgba(139, 195, 74, 0.15);
        }
        
        .table-green td {
            padding: 12px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }
        
        /* Button styling */
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            padding: 8px 16px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-primary-custom:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-info-custom {
            background-color: #81C784;
            border-color: #81C784;
            color: white;
            border-radius: var(--border-radius);
            padding: 6px 12px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-info-custom:hover {
            background-color: #66BB6A;
            border-color: #66BB6A;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-success-custom {
            background-color: #43A047;
            border-color: #43A047;
            color: white;
            border-radius: var(--border-radius);
            padding: 6px 12px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-success-custom:hover {
            background-color: #388E3C;
            border-color: #388E3C;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-danger-custom {
            background-color: #E53935;
            border-color: #E53935;
            color: white;
            border-radius: var(--border-radius);
            padding: 6px 12px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-danger-custom:hover {
            background-color: #D32F2F;
            border-color: #D32F2F;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary-custom {
            background-color: #78909C;
            border-color: #78909C;
            color: white;
            border-radius: var(--border-radius);
            padding: 6px 12px;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-secondary-custom:hover {
            background-color: #607D8B;
            border-color: #607D8B;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Badge Styling */
        .badge-status {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
            border-radius: 50rem;
            font-weight: 500;
        }
        
        .badge-waiting {
            background-color: #FFC107;
            color: #212121;
        }
        
        .badge-approved {
            background-color: #4CAF50;
            color: white;
        }
        
        .badge-rejected {
            background-color: #F44336;
            color: white;
        }
        
        .badge-processing {
            background-color: #2196F3;
            color: white;
        }
        
        .badge-completed {
            background-color: #9C27B0;
            color: white;
        }
        
        /* Statistics styling */
        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
            border-left: 5px solid var(--primary-color);
        }
        
        .stats-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }
        
        .stats-card-body {
            padding: 20px;
        }
        
        .stats-card .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        
        .stats-card .stats-title {
            font-size: 1rem;
            color: var(--text-dark);
            opacity: 0.7;
        }
        
        .stats-icon {
            font-size: 2.2rem;
            color: var(--primary-light);
        }
        
        /* Loading animation */
        .loading-pulse {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-light);
            box-shadow: 0 0 0 rgba(76, 175, 80, 0.4);
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(76, 175, 80, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
            }
        }
        
        /* รองรับการแสดงผลบนมือถือ */
        @media (max-width: 768px) {
            .system-title {
                font-size: 0.95rem;
            }
            .system-subtitle {
                font-size: 0.8rem;
            }
            .stats-card .stats-number {
                font-size: 1.5rem;
            }
            .stats-icon {
                font-size: 1.8rem;
            }
            .dashboard-card {
                margin-bottom: 15px;
            }
            .table-green td, .table-green th {
                padding: 8px;
            }
        }
        
        /* Progress Bar Styling */
        .progress-custom {
            height: 12px;
            border-radius: 50px;
            background-color: #E8F5E9;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .progress-bar-custom {
            background-color: var(--primary-color);
            border-radius: 50px;
        }
        
        /* Footer styling */
        .footer {
            background-color: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
            font-size: 0.85rem;
            color: var(--text-dark);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top mb-4">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center">
                <div class="logo-circle me-3" data-aos="zoom-in">
                    <img src="./vendor/image/logo.jpg" alt="Logo">
                </div>
                <div>
                    <h1 class="system-title mb-0" data-aos="fade-right" data-aos-delay="100">ระบบออกใบรับรองนักวิจัยเพื่อใช้ประกอบการขอทุน</h1>
                    <p class="system-subtitle mb-0" data-aos="fade-right" data-aos-delay="200">คณะเกษตรศาสตร์ฯ (ส่วนห้องปฏิบัติการ)</p>
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="text-end me-3" data-aos="fade-left" data-aos-delay="100">
                    <span class="d-block text-success fw-bold"><?php echo $POS2.' '.$dr.htmlspecialchars($row['fname']).' '.htmlspecialchars($row['lname']); ?></span>
                </div>
                <a href="./researcher.php?logout" class="btn btn-outline-danger btn-sm" data-aos="fade-left" data-aos-delay="200">
                    <i class='bx bx-log-out me-1'></i>ออกจากระบบ
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-card-body d-flex align-items-center">
                        <div class="me-auto">
                            <div class="stats-number" id="total-requests">0</div>
                            <div class="stats-title">คำขอทั้งหมด</div>
                        </div>
                        <div class="stats-icon">
                            <i class='bx bx-file'></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-card-body d-flex align-items-center">
                        <div class="me-auto">
                            <div class="stats-number" id="waiting-approval">0</div>
                            <div class="stats-title">รอการอนุมัติ</div>
                        </div>
                        <div class="stats-icon">
                            <i class='bx bx-time-five'></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-card-body d-flex align-items-center">
                        <div class="me-auto">
                            <div class="stats-number" id="approved-requests">0</div>
                            <div class="stats-title">อนุมัติแล้ว</div>
                        </div>
                        <div class="stats-icon">
                            <i class='bx bx-check-circle'></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-4 mb-md-0">
                <div class="stats-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-card-body d-flex align-items-center">
                        <div class="me-auto">
                            <div class="stats-number" id="rejected-requests">0</div>
                            <div class="stats-title">ไม่อนุมัติ</div>
                        </div>
                        <div class="stats-icon">
                            <i class='bx bx-x-circle'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="dashboard-card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class='bx bx-list-ul me-2'></i>รายการคำขอใช้บริการห้องปฏิบัติการ
                </h5>
                <button class="btn btn-sm btn-light" onclick="loadDataForAllLabs()">
                    <i class='bx bx-refresh me-1'></i>รีเฟรช
                </button>
            </div>
            <div class="card-body-custom p-0">
                <div id="data-container" class="p-4">
                    <div class="text-center py-5">
                        <div class="loading-pulse mb-3"></div>
                        <p class="text-muted">กำลังโหลดข้อมูล...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
// ฟังก์ชันสำหรับดูเอกสาร PDF
function showPDFWithLoading(pdfPath) {
    Swal.fire({
        title: 'กำลังโหลดเอกสาร',
        html: `<div class="loading-pulse mb-3"></div>`,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            setTimeout(() => {
                window.open(pdfPath, '_blank');
                Swal.close();
            }, 1000);
        }
    });
}

// เหมือนกับ showPDFWithLoading แต่ตั้งชื่อให้สอดคล้องกับตัวอย่างที่ 2
function showPDF(pdfPath) {
    Swal.fire({
        title: 'กำลังโหลดเอกสาร',
        html: `<div class="loading-pulse mb-3"></div>`,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            setTimeout(() => {
                window.open(pdfPath, '_blank');
                Swal.close();
            }, 1000);
        }
    });
}// Initialize AOS animation
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
});

// Global Variables
let statsData = {
    total: 0,
    waiting: 0,
    approved: 0,
    rejected: 0
};

window.onload = function () {
    const labId = <?= json_encode($lab_id) ?>;
    if (labId) {
        loadData(labId);
    } else {
        loadDataForAllLabs();
    }
};

const deanList = [
    { id: 85, name: 'รศ.ดร.พันธ์ทิพย์ กล่อมเจ๊ก' },
    { id: 79, name: 'ดร.เจษฎา วิชาพร' },
    // ... รายชื่อผู้บริหารคนอื่น ๆ
];

function loadDataForAllLabs() {
    // Reset stats
    statsData = {
        total: 0,
        waiting: 0,
        approved: 0,
        rejected: 0
    };
    
    // แสดง loading animation
    const dataContainer = document.getElementById("data-container");
    dataContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="loading-pulse mb-3"></div>
            <p class="text-muted">กำลังโหลดข้อมูล...</p>
        </div>
    `;
    
    fetch("function_labrequest.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                Swal.fire({
                    title: "เกิดข้อผิดพลาด!",
                    text: data.message,
                    icon: "error",
                    confirmButtonText: "ตกลง"
                });
                return;
            }

            dataContainer.innerHTML = ""; // เคลียร์ Container ก่อน

            for (const labId in data.data) {
                if (data.data.hasOwnProperty(labId)) {
                    const labInfo = data.data[labId];
                    const labName = labInfo.name;
                    const requests = labInfo.requests;
                    
                    // Update stats
                    statsData.total += requests.length;
                    
                    // สร้างส่วนหัวของตารางสำหรับ Lab นี้
                    const labHeader = document.createElement("div");
                    labHeader.className = "d-flex align-items-center mb-3";
                    labHeader.innerHTML = `
                        <h5 class="mb-0 text-success">
                            <i class='bx bx-cube-alt me-2'></i>${labName}
                        </h5>
                        <span class="badge bg-success ms-2">${requests.length} คำขอ</span>
                    `;
                    dataContainer.appendChild(labHeader);

                    if (requests.length > 0) {
                        const tableContainer = document.createElement("div");
                        tableContainer.className = "table-responsive";
                        const table = document.createElement("table");
                        table.className = "table table-green";
                        const thead = document.createElement("thead");
                        thead.innerHTML = `
                            <tr>
                                <th>#</th>
                                <th>ชื่อผู้ใช้</th>
                                <th>เบอร์โทรศัพท์</th>
                                <th>สถานะ</th>
                                <th>วัตถุประสงค์</th>
                                <th>NU-LAB-02</th>
                                <th>NU-LAB-01</th>
                                <th>อนุมัติ</th>
                                <th>ไม่อนุมัติ</th>
                                <th>ใบประกาศ</th>
                                <th>เวลา</th>
                            </tr>
                        `;
                        table.appendChild(thead);
                        const tbody = document.createElement("tbody");
                        tbody.id = `data-body-${labId}`;
                        table.appendChild(tbody);
                        tableContainer.appendChild(table);
                        dataContainer.appendChild(tableContainer);

                        // แสดงข้อมูลสำหรับ Lab นี้ใน tbody ที่สร้างขึ้น
                        const tbodyForLab = document.getElementById(`data-body-${labId}`);
                        requests.forEach((row, index) => {
                            const tr = document.createElement("tr");
                            
                            // Column: No.
                            const indexTd = document.createElement("td");
                            indexTd.textContent = index + 1;
                            tr.appendChild(indexTd);

                            // Column: Username
                            const usernameTd = document.createElement("td");
                            usernameTd.id = `username-${row.user_id}-${labId}`;
                            usernameTd.innerHTML = `<div class="d-flex align-items-center">
                                                    <div class="spinner-border spinner-border-sm text-success me-2" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <span>กำลังโหลด...</span>
                                                </div>`;
                            tr.appendChild(usernameTd);
                            fetchUsername(row.user_id, (fullName) => {
                                const userDisplayTd = document.getElementById(`username-${row.user_id}-${labId}`);
                                if (userDisplayTd) {
                                    userDisplayTd.innerHTML = `<span class="fw-medium">${fullName || 'ไม่พบชื่อ'}</span>`;
                                }
                            });

                            // Column: Phone
                            const phoneTd = document.createElement("td");
                            phoneTd.textContent = row.phone;
                            tr.appendChild(phoneTd);

                            // Column: Stage (Status)
                            const stageTd = document.createElement("td");
                            let stageText = "";
                            let badgeClass = "";
                            
                            switch (parseInt(row.stage)) {
                                case 1:
                                    stageText = "รอห้องปฎิบัติการอนุมัติ";
                                    badgeClass = "badge-waiting";
                                    statsData.waiting++;
                                    break;
                                case 2:
                                    stageText = "รอผู้บริหารอนุมัติ";
                                    badgeClass = "badge-processing";
                                    statsData.approved++;
                                    break;
                                case 3:
                                    stageText = "ส่งมหาลัย/กองวิจัย";
                                    badgeClass = "badge-approved";
                                    statsData.approved++;
                                    break;
                                case 4:
                                    stageText = "ห้องปฎิบัติการไม่อนุมัติ";
                                    badgeClass = "badge-rejected";
                                    statsData.rejected++;
                                    break;
                                case 5:
                                    stageText = "ผู้บริหารไม่อนุมัติ";
                                    badgeClass = "badge-rejected";
                                    statsData.rejected++;
                                    break;
                                case 6:
                                    stageText = "สำเร็จ/จัดส่งนักวิจัย";
                                    badgeClass = "badge-completed";
                                    statsData.approved++;
                                    break;
                            }
                            stageTd.innerHTML = `<span class="badge badge-status ${badgeClass}">${stageText}</span>`;
                            tr.appendChild(stageTd);

                            // Column: Purpose
                            const purposeTd = document.createElement("td");
                            let purposeText = "ไม่ระบุ";
                            let purposeIcon = "";
                            
                            switch (row.purpose) {
                                case 'R':
                                    purposeText = "งานวิจัย";
                                    purposeIcon = "bx-book-bookmark";
                                    break;
                                case 'S':
                                    purposeText = "บริการวิชาการ";
                                    purposeIcon = "bx-briefcase";
                                    break;
                                case 'T':
                                    purposeText = "การเรียน/การสอน";
                                    purposeIcon = "bx-chalkboard";
                                    break;
                            }
                            purposeTd.innerHTML = `<span><i class='bx ${purposeIcon} me-1'></i>${purposeText}</span>`;
                            tr.appendChild(purposeTd);

                            // Column: View NU-LAB-02 Button
                            const pdfButtonTd = document.createElement("td");
                            const pdfButton = document.createElement("button");
                            pdfButton.className = "btn btn-info-custom btn-sm";
                            pdfButton.innerHTML = `<i class='bx bx-file me-1'></i>NU-LAB-02`;
                            pdfButton.addEventListener("click", (event) => {
                                event.preventDefault();
                                const encodedPdfPath = encodeURI(row.path);
                                showPDFWithLoading(encodedPdfPath);
                            });
                            pdfButtonTd.appendChild(pdfButton);
                            tr.appendChild(pdfButtonTd);
                            
                            // Column: View NU-LAB-01 Button
                            const viewNu01ButtonTd = createViewNu01Button(row.user_id);
                            tr.appendChild(viewNu01ButtonTd);
                            
                            // Column: Approve Button
                            const approveButtonTd = document.createElement("td");
                            
                            // Column: Disapprove Button
                            const disapproveButtonTd = document.createElement("td"); 

                            if (parseInt(row.stage) === 1) {
                                const approveButton = document.createElement("button");
                                approveButton.className = "btn btn-success-custom btn-sm";
                                approveButton.innerHTML = `<i class='bx bx-check me-1'></i>อนุมัติ`;
                                approveButton.addEventListener("click", () => {
                                    approveRequest(row);
                                });
                                approveButtonTd.appendChild(approveButton);
                                
                                const disapproveButton = document.createElement("button");
                                disapproveButton.className = "btn btn-danger-custom btn-sm";
                                disapproveButton.innerHTML = `<i class='bx bx-x me-1'></i>ไม่อนุมัติ`;
                                disapproveButton.addEventListener("click", () => {
                                    disapproveRequest(row);
                                });
                                disapproveButtonTd.appendChild(disapproveButton);
                            } else {
                                // ถ้าไม่ใช่ stage 1 ให้แสดงสถานะแทนปุ่ม
                                if (parseInt(row.stage) > 1 && parseInt(row.stage) !== 4) {
                                    approveButtonTd.innerHTML = `<span class="badge badge-status badge-approved"><i class='bx bx-check me-1'></i>อนุมัติแล้ว</span>`;
                                } else if (parseInt(row.stage) === 4) {
                                    disapproveButtonTd.innerHTML = `<span class="badge badge-status badge-rejected"><i class='bx bx-x me-1'></i>ไม่อนุมัติแล้ว</span>`;
                                }
                            }
                            
                            tr.appendChild(approveButtonTd);
                            tr.appendChild(disapproveButtonTd);
                            
                            // Column: Certificate Button
                            const certButtonTd = document.createElement("td");
                            const certButton = document.createElement("button");
                            certButton.className = "btn btn-secondary-custom btn-sm";
                            certButton.innerHTML = `<i class='bx bx-certification me-1'></i>ใบประกาศ`;
                            certButton.addEventListener("click", () => {
                                window.open(`view_certificate.php?user_id=${row.user_id}`, '_blank');
                            });
                            certButtonTd.appendChild(certButton);
                            tr.appendChild(certButtonTd);
                            
                            // Column: Timestamp
                            const timestampTd = document.createElement("td");
                            if (row.timestamp) {
                                const timestamp = new Date(row.timestamp);
                                const formattedTime = `${timestamp.toLocaleDateString('th-TH', { day: '2-digit', month: '2-digit', year: 'numeric' })} ${timestamp.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' })}`;
                                timestampTd.innerHTML = `<span class="text-muted small"><i class='bx bx-time me-1'></i>${formattedTime}</span>`;
                            } else {
                                timestampTd.textContent = "N/A";
                            }
                            tr.appendChild(timestampTd);
                            
                            tbodyForLab.appendChild(tr);
                        });
                    } else {
                        // สร้างข้อความ "ไม่มีข้อมูล" สำหรับ Lab ที่ไม่มีข้อมูล
                        const noDataDiv = document.createElement("div");
                        noDataDiv.className = "alert alert-light text-center p-4";
                        noDataDiv.innerHTML = `
                            <div class="mb-3"><i class='bx bx-info-circle text-success' style="font-size: 2.5rem;"></i></div>
                            <h5 class="text-muted">ยังไม่มีผู้ยื่นขอใช้ห้องปฎิบัติการ</h5>
                            <p class="small text-muted mb-0">เมื่อมีคำขอใหม่จะแสดงที่นี่</p>
                        `;
                        dataContainer.appendChild(noDataDiv);
                    }
                }
            }
            
            // อัพเดท Stats
            updateStats();
        })
        .catch(error => {
            console.error("Fetch error:", error);
            Swal.fire({
                title: "เกิดข้อผิดพลาด!",
                text: "ไม่สามารถโหลดข้อมูล: " + error.message,
                icon: "error",
                confirmButtonText: "ตกลง",
                confirmButtonColor: "#2E7D32"
            });
        });
}

// สำหรับโหลดข้อมูลของ Lab เดียว
function loadData(labId) {
    // Reset stats
    statsData = {
        total: 0,
        waiting: 0,
        approved: 0,
        rejected: 0
    };
    
    // แสดง loading animation
    const dataContainer = document.getElementById("data-container");
    dataContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="loading-pulse mb-3"></div>
            <p class="text-muted">กำลังโหลดข้อมูล...</p>
        </div>
    `;
    
    fetch(`lab_data.php?lab_id=${labId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                Swal.fire({
                    title: "เกิดข้อผิดพลาด!",
                    text: data.message,
                    icon: "error",
                    confirmButtonText: "ตกลง",
                    confirmButtonColor: "#2E7D32"
                });
                return;
            }

            dataContainer.innerHTML = ""; // เคลียร์ Container ก่อน
            
            const labInfo = data.data;
            const labName = labInfo.name;
            const requests = labInfo.requests;
            
            // สร้างส่วนหัวของตารางสำหรับ Lab
            const labHeader = document.createElement("div");
            labHeader.className = "d-flex align-items-center mb-3";
            labHeader.innerHTML = `
                <h5 class="mb-0 text-success">
                    <i class='bx bx-cube-alt me-2'></i>${labName}
                </h5>
                <span class="badge bg-success ms-2">${requests.length} คำขอ</span>
            `;
            dataContainer.appendChild(labHeader);
            
            // Update stats
            statsData.total = requests.length;

            if (requests.length > 0) {
                const tableContainer = document.createElement("div");
                tableContainer.className = "table-responsive";
                const table = document.createElement("table");
                table.className = "table table-green";
                const thead = document.createElement("thead");
                thead.innerHTML = `
                    <tr>
                        <th>#</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>สถานะ</th>
                        <th>วัตถุประสงค์</th>
                        <th>NU-LAB-02</th>
                        <th>NU-LAB-01</th>
                        <th>อนุมัติ</th>
                        <th>ไม่อนุมัติ</th>
                        <th>ใบประกาศ</th>
                        <th>เวลา</th>
                    </tr>
                `;
                table.appendChild(thead);
                const tbody = document.createElement("tbody");
                tbody.id = `data-body-${labId}`;
                table.appendChild(tbody);
                tableContainer.appendChild(table);
                dataContainer.appendChild(tableContainer);

                // แสดงข้อมูลสำหรับ Lab นี้ใน tbody ที่สร้างขึ้น
                const tbodyForLab = document.getElementById(`data-body-${labId}`);
                requests.forEach((row, index) => {
                    const tr = document.createElement("tr");
                    
                    // Column: No.
                    const indexTd = document.createElement("td");
                    indexTd.textContent = index + 1;
                    tr.appendChild(indexTd);

                    // Column: Username
                    const usernameTd = document.createElement("td");
                    usernameTd.id = `username-${row.user_id}-${labId}`;
                    usernameTd.innerHTML = `<div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm text-success me-2" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span>กำลังโหลด...</span>
                                        </div>`;
                    tr.appendChild(usernameTd);
                    fetchUsername(row.user_id, (fullName) => {
                        const userDisplayTd = document.getElementById(`username-${row.user_id}-${labId}`);
                        if (userDisplayTd) {
                            userDisplayTd.innerHTML = `<span class="fw-medium">${fullName || 'ไม่พบชื่อ'}</span>`;
                        }
                    });

                    // Column: Phone
                    const phoneTd = document.createElement("td");
                    phoneTd.textContent = row.phone;
                    tr.appendChild(phoneTd);

                    // Column: Stage (Status)
                    const stageTd = document.createElement("td");
                    let stageText = "";
                    let badgeClass = "";
                    
                    switch (parseInt(row.stage)) {
                        case 1:
                            stageText = "รอห้องปฎิบัติการอนุมัติ";
                            badgeClass = "badge-waiting";
                            statsData.waiting++;
                            break;
                        case 2:
                            stageText = "รอผู้บริหารอนุมัติ";
                            badgeClass = "badge-processing";
                            statsData.approved++;
                            break;
                        case 3:
                            stageText = "ส่งมหาลัย/กองวิจัย";
                            badgeClass = "badge-approved";
                            statsData.approved++;
                            break;
                        case 4:
                            stageText = "ห้องปฎิบัติการไม่อนุมัติ";
                            badgeClass = "badge-rejected";
                            statsData.rejected++;
                            break;
                        case 5:
                            stageText = "ผู้บริหารไม่อนุมัติ";
                            badgeClass = "badge-rejected";
                            statsData.rejected++;
                            break;
                        case 6:
                            stageText = "สำเร็จ/จัดส่งนักวิจัย";
                            badgeClass = "badge-completed";
                            statsData.approved++;
                            break;
                    }
                    stageTd.innerHTML = `<span class="badge badge-status ${badgeClass}">${stageText}</span>`;
                    tr.appendChild(stageTd);

                    // Column: Purpose
                    const purposeTd = document.createElement("td");
                    let purposeText = "ไม่ระบุ";
                    let purposeIcon = "";
                    
                    switch (row.purpose) {
                        case 'R':
                            purposeText = "งานวิจัย";
                            purposeIcon = "bx-book-bookmark";
                            break;
                        case 'S':
                            purposeText = "บริการวิชาการ";
                            purposeIcon = "bx-briefcase";
                            break;
                        case 'T':
                            purposeText = "การเรียน/การสอน";
                            purposeIcon = "bx-chalkboard";
                            break;
                    }
                    purposeTd.innerHTML = `<span><i class='bx ${purposeIcon} me-1'></i>${purposeText}</span>`;
                    tr.appendChild(purposeTd);

                    // Column: View NU-LAB-02 Button
                    const pdfButtonTd = document.createElement("td");
                    const pdfButton = document.createElement("button");
                    pdfButton.className = "btn btn-info-custom btn-sm";
                    pdfButton.innerHTML = `<i class='bx bx-file me-1'></i>NU-LAB-02`;
                    pdfButton.addEventListener("click", (event) => {
                        event.preventDefault();
                        const encodedPdfPath = encodeURI(row.path);
                        showPDF(encodedPdfPath);
                    });
                    pdfButtonTd.appendChild(pdfButton);
                    tr.appendChild(pdfButtonTd);
                    
                    // Column: View NU-LAB-01 Button
                    const viewNu01ButtonTd = createViewNu01Button(row.user_id);
                    tr.appendChild(viewNu01ButtonTd);
                    
                    // Column: Approve Button
                    const approveButtonTd = document.createElement("td");
                    
                    // Column: Disapprove Button
                    const disapproveButtonTd = document.createElement("td"); 

                    if (parseInt(row.stage) === 1) {
                        const approveButton = document.createElement("button");
                        approveButton.className = "btn btn-success-custom btn-sm";
                        approveButton.innerHTML = `<i class='bx bx-check me-1'></i>อนุมัติ`;
                        approveButton.addEventListener("click", () => {
                            approveRequest(row);
                        });
                        approveButtonTd.appendChild(approveButton);
                        
                        const disapproveButton = document.createElement("button");
                        disapproveButton.className = "btn btn-danger-custom btn-sm";
                        disapproveButton.innerHTML = `<i class='bx bx-x me-1'></i>ไม่อนุมัติ`;
                        disapproveButton.addEventListener("click", () => {
                            disapproveRequest(row);
                        });
                        disapproveButtonTd.appendChild(disapproveButton);
                    } else {
                        // ถ้าไม่ใช่ stage 1 ให้แสดงสถานะแทนปุ่ม
                        if (parseInt(row.stage) > 1 && parseInt(row.stage) !== 4) {
                            approveButtonTd.innerHTML = `<span class="badge badge-status badge-approved"><i class='bx bx-check me-1'></i>อนุมัติแล้ว</span>`;
                        } else if (parseInt(row.stage) === 4) {
                            disapproveButtonTd.innerHTML = `<span class="badge badge-status badge-rejected"><i class='bx bx-x me-1'></i>ไม่อนุมัติแล้ว</span>`;
                        }
                    }
                    
                    tr.appendChild(approveButtonTd);
                    tr.appendChild(disapproveButtonTd);
                    
                    // Column: Certificate Button
                    const certButtonTd = document.createElement("td");
                    const certButton = document.createElement("button");
                    certButton.className = "btn btn-secondary-custom btn-sm";
                    certButton.innerHTML = `<i class='bx bx-certification me-1'></i>ใบประกาศ`;
                    certButton.addEventListener("click", () => {
                        window.open(`view_certificate.php?user_id=${row.user_id}`, '_blank');
                    });
                    certButtonTd.appendChild(certButton);
                    tr.appendChild(certButtonTd);
                    
                    // Column: Timestamp
                    const timestampTd = document.createElement("td");
                    if (row.timestamp) {
                        const timestamp = new Date(row.timestamp);
                        const formattedTime = `${timestamp.toLocaleDateString('th-TH', { day: '2-digit', month: '2-digit', year: 'numeric' })} ${timestamp.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' })}`;
                        timestampTd.innerHTML = `<span class="text-muted small"><i class='bx bx-time me-1'></i>${formattedTime}</span>`;
                    } else {
                        timestampTd.textContent = "N/A";
                    }
                    tr.appendChild(timestampTd);
                    
                    tbodyForLab.appendChild(tr);
                });
            } else {
                // สร้างข้อความ "ไม่มีข้อมูล" สำหรับ Lab ที่ไม่มีข้อมูล
                const noDataDiv = document.createElement("div");
                noDataDiv.className = "alert alert-light text-center p-4";
                noDataDiv.innerHTML = `
                    <div class="mb-3"><i class='bx bx-info-circle text-success' style="font-size: 2.5rem;"></i></div>
                    <h5 class="text-muted">ยังไม่มีผู้ยื่นขอใช้ห้องปฎิบัติการ</h5>
                    <p class="small text-muted mb-0">เมื่อมีคำขอใหม่จะแสดงที่นี่</p>
                `;
                dataContainer.appendChild(noDataDiv);
            }
            
            // อัพเดท Stats
            updateStats();
        })
        .catch(error => {
            console.error("Fetch error:", error);
            Swal.fire({
                title: "เกิดข้อผิดพลาด!",
                text: "ไม่สามารถโหลดข้อมูล: " + error.message,
                icon: "error",
                confirmButtonText: "ตกลง",
                confirmButtonColor: "#2E7D32"
            });
        });
} badge-status badge-rejected"><i class='bx bx-x me-1'></i>ไม่อนุมัติแล้ว</span>`;
                        }
                    }
                    
                    tr.appendChild(approveButtonTd);
                    tr.appendChild(disapproveButtonTd);
                    
                    // Column: Certificate Button
                    const certButtonTd = document.createElement("td");
                    const certButton = document.createElement("button");
                    certButton.className = "btn btn-secondary-custom btn-sm";
                    certButton.innerHTML = `<i class='bx bx-certification me-1'></i>ใบประกาศ`;
                    certButton.addEventListener("click", () => {
                        window.open(`view_certificate.php?user_id=${row.user_id}`, '_blank');
                    });
                    certButtonTd.appendChild(certButton);
                    tr.appendChild(certButtonTd);
                    
                    // Column: Timestamp
                    const timestampTd = document.createElement("td");
                    if (row.timestamp) {
                        const timestamp = new Date(row.timestamp);
                        const formattedTime = `${timestamp.toLocaleDateString('th-TH', { day: '2-digit', month: '2-digit', year: 'numeric' })} ${timestamp.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' })}`;
                        timestampTd.innerHTML = `<span class="text-muted small"><i class='bx bx-time me-1'></i>${formattedTime}</span>`;
                    } else {
                        timestampTd.textContent = "N/A";
                    }
                    tr.appendChild(timestampTd);
                    
                    tbodyForLab.appendChild(tr);
                });
            } else {
                // สร้างข้อความ "ไม่มีข้อมูล" สำหรับ Lab ที่ไม่มีข้อมูล
                const noDataDiv = document.createElement("div");
                noDataDiv.className = "alert alert-light text-center p-4";
                noDataDiv.innerHTML = `
                    <div class="mb-3"><i class='bx bx-info-circle text-success' style="font-size: 2.5rem;"></i></div>
                    <h5 class="text-muted">ยังไม่มีผู้ยื่นขอใช้ห้องปฎิบัติการ</h5>
                    <p class="small text-muted mb-0">เมื่อมีคำขอใหม่จะแสดงที่นี่</p>
                `;
                dataContainer.appendChild(noDataDiv);
            }
            
            // อัพเดท Stats
            updateStats();
        })
        .catch(error => {
            console.error("Fetch error:", error);
            Swal.fire({
                title: "เกิดข้อผิดพลาด!",
                text: "ไม่สามารถโหลดข้อมูล: " + error.message,
                icon: "error",
                confirmButtonText: "ตกลง",
                confirmButtonColor: "#2E7D32"
            });
        });
} badge-status badge-rejected"><i class='bx bx-x me-1'></i>ไม่อนุมัติแล้ว</span>`;
                        }
                    }
                    
                    tr.appendChild(approveButtonTd);
                    tr.appendChild(disapproveButtonTd);
                    
                    // Column: Certificate Button
                    const certButtonTd = document.createElement("td");
                    const certButton = document.createElement("button");
                    certButton.className = "btn btn-secondary-custom btn-sm";
                    certButton.innerHTML = `<i class='bx bx-certification me-1'></i>ใบประกาศ`;
                    certButton.addEventListener("click", () => {
                        window.open(`view_certificate.php?user_id=${row.user_id}`, '_blank');
                    });
                    certButtonTd.appendChild(certButton);
                    tr.appendChild(certButtonTd);
                    
                    // Column: Timestamp
                    const timestampTd = document.createElement("td");
                    if (row.timestamp) {
                        const timestamp = new Date(row.timestamp);
                        const formattedTime = `${timestamp.toLocaleDateString('th-TH', { day: '2-digit', month: '2-digit', year: 'numeric' })} ${timestamp.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' })}`;
                        timestampTd.innerHTML = `<span class="text-muted small"><i class='bx bx-time me-1'></i>${formattedTime}</span>`;
                    } else {
                        timestampTd.textContent = "N/A";
                    }
                    tr.appendChild(timestampTd);
                    
                    tbodyForLab.appendChild(tr);
                });
            } else {
                // สร้างข้อความ "ไม่มีข้อมูล" สำหรับ Lab ที่ไม่มีข้อมูล
                const noDataDiv = document.createElement("div");
                noDataDiv.className = "alert alert-light text-center p-4";
                noDataDiv.innerHTML = `
                    <div class="mb-3"><i class='bx bx-info-circle text-success' style="font-size: 2.5rem;"></i></div>
                    <h5 class="text-muted">ยังไม่มีผู้ยื่นขอใช้ห้องปฎิบัติการ</h5>
                    <p class="small text-muted mb-0">เมื่อมีคำขอใหม่จะแสดงที่นี่</p>
                `;
                labCard.appendChild(noDataDiv);
                dataContainer.appendChild(labCard);
            }
            
            // อัพเดท Stats
            updateStats();
        })
        .catch(error => {
            console.error("Fetch error:", error);
            Swal.fire({
                title: "เกิดข้อผิดพลาด!",
                text: "ไม่สามารถโหลดข้อมูล: " + error.message,
                icon: "error",
                confirmButtonText: "ตกลง",
                confirmButtonColor: "#2E7D32"
            });
        });
}

// อัพเดทข้อมูลสถิติบนหน้าแดชบอร์ด
function updateStats() {
    document.getElementById('total-requests').textContent = statsData.total;
    document.getElementById('waiting-approval').textContent = statsData.waiting;
    document.getElementById('approved-requests').textContent = statsData.approved;
    document.getElementById('rejected-requests').textContent = statsData.rejected;
    
    // เพิ่ม animation เมื่ออัพเดทตัวเลข
    document.querySelectorAll('.stats-number').forEach(element => {
        element.classList.add('animate__animated', 'animate__heartBeat');
        setTimeout(() => {
            element.classList.remove('animate__animated', 'animate__heartBeat');
        }, 1000);
    });
}

// ฟังก์ชันสำหรับดึงข้อมูลชื่อผู้ใช้
function fetchUsername(userId, callback) {
    fetch(`get_username.php?user_id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                callback(data.fullname);
            } else {
                callback('ไม่พบชื่อ');
            }
        })
        .catch(error => {
            console.error("Error fetching username:", error);
            callback('ไม่พบชื่อ');
        });
}

// ฟังก์ชันสำหรับสร้างปุ่มดู NU-LAB-01
function createViewNu01Button(userId) {
    const buttonTd = document.createElement("td");
    const button = document.createElement("button");
    button.className = "btn btn-warning-custom btn-sm";
    button.innerHTML = `<i class='bx bx-file-blank me-1'></i>NU-LAB-01`;
    button.addEventListener("click", () => {
        window.open(`view_nulab01.php?user_id=${userId}`, '_blank');
    });
    buttonTd.appendChild(button);
    return buttonTd;
}

// ฟังก์ชันสำหรับแสดง PDF พร้อม Loading
function showPDFWithLoading(pdfPath) {
    Swal.fire({
        title: 'กำลังโหลดเอกสาร',
        html: `<div class="loading-pulse mb-3"></div>`,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            setTimeout(() => {
                window.open(pdfPath, '_blank');
                Swal.close();
            }, 1000);
        }
    });
}

// ฟังก์ชันอนุมัติคำขอ
function approveRequest(requestData) {
    // ระบบบังคับให้เป็นผู้บริหารคนเดียว (pantipk) -> deansign = 85
    const fixedDeanId = 85;

    Swal.fire({
        title: 'ยืนยันการอนุมัติ',
        text: 'คุณต้องการอนุมัติใบคำร้องใช่หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยันการอนุมัติ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#2E7D32',
        cancelButtonColor: '#757575',
        reverseButtons: true
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Show loading indicator with progress
        Swal.fire({
            title: 'กำลังดำเนินการ',
            html: `
                <div class="progress progress-custom mt-3">
                    <div class="progress-bar progress-bar-custom progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 0%;" 
                         aria-valuenow="0" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                         <span class="visually-hidden">0%</span>
                    </div>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                const progressBar = Swal.getPopup().querySelector('.progress-bar');
                let progress = 0;

                const interval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);

                    if (progress >= 100) {
                        clearInterval(interval);

                        const approvalData = {
                            ...requestData,
                            deansign: fixedDeanId
                        };

                        sendApprovalData(approvalData);
                    }
                }, 150);
            }
        });
    });
}
    </script>
</body>
</html>
