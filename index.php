<!DOCTYPE html>
<html lang="th" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการใบรับรองนักวิจัย | คณะเกษตรศาสตร์ฯ มหาวิทยาลัยนเรศวร</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Prompt (Thai) -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2e7d32; /* สีเขียวเข้ม */
            --primary-light: #60ad5e; /* สีเขียวอ่อน */
            --primary-dark: #005005; /* สีเขียวเข้มมาก */
            --accent-color: #81c784; /* สีเขียวเน้น */
            --text-color: #333333;
            --light-bg: #f1f8e9; /* พื้นหลังสีเขียวอ่อนมาก */
            --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            --card-border: rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, var(--light-bg) 0%, #e8f5e9 100%);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }
        
        .login-card {
            background-color: white;
            border-radius: 12px;
            border: none;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(46, 125, 50, 0.1);
        }
        
        .login-header {
            position: relative;
            padding-bottom: 1rem;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            box-shadow: 0 2px 15px rgba(46, 125, 50, 0.15);
            border: 3px solid rgba(46, 125, 50, 0.1);
            padding: 2px;
            margin: 0 auto 15px;
            transition: transform 0.3s ease;
        }
        
        .logo-circle:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }
        
        .logo-circle img {
            max-width: 85%;
            max-height: 85%;
            object-fit: contain;
        }
        
        .system-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 5px;
        }
        
        .faculty-name {
            color: #6c757d;
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .login-divider {
            position: relative;
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(255,255,255,0) 0%, 
                rgba(46, 125, 50, 0.3) 50%, 
                rgba(255,255,255,0) 100%);
            margin: 1.5rem 0;
        }
        
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #e0e0e0;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.15);
            background-color: white;
        }
        
        .form-control::placeholder {
            color: #aaa;
            font-size: 0.9rem;
        }
        
        .user-type-wrapper {
            position: relative;
        }
        
        .user-type-wrapper::after {
            content: '\F282'; /* Bootstrap icon code for chevron-down */
            font-family: 'Bootstrap Icons';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .footer {
            background-color: white;
            margin-top: auto;
            padding: 1.5rem 0;
            flex-shrink: 0;
        }
        
        .footer-card {
            border: none;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.03);
            border-radius: 12px;
        }
        
        .footer-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-subtitle {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-copyright {
            color: #aaa;
            font-size: 0.8rem;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .spinner-border {
            color: var(--primary-color);
        }
        
        .loading-text {
            margin-top: 1rem;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        /* เพิ่ม AOS animations */
        .login-card {
            overflow: visible; /* ทำให้ animation ไม่ถูกตัด */
        }
        
        .pulse-effect {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(46, 125, 50, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(46, 125, 50, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(46, 125, 50, 0);
            }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            .main-content {
                padding: 20px 0;
            }
            
            .login-card {
                padding: 1.5rem;
            }
            
            .system-title {
                font-size: 1.3rem;
            }
            
            .logo-circle {
                width: 70px;
                height: 70px;
            }
            
            .footer-title {
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .system-title {
                font-size: 1.1rem;
                padding: 0 10px;
            }
            
            .logo-circle {
                width: 60px;
                height: 60px;
            }
        }
        .footer-subtitle {
            font-size: 0.9rem;
            color: #666;
        }
        .counter {
            font-weight: bold;
            color: #007bff;
            margin: 0 0.5rem;
        }
        #last-updated {
            font-weight: normal;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border" role="status" style="width: 3rem; height: 3rem;"></div>
            <div class="loading-text">กำลังเข้าสู่ระบบ...</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-6 col-xl-5">
                    <div class="login-card p-4 p-md-5" data-aos="fade-up" data-aos-duration="1000">
                        <div class="login-header" data-aos="fade-down" data-aos-delay="200">
                            <div class="logo-circle pulse-effect" data-aos="zoom-in" data-aos-delay="400">
                                <img src="./vendor/image/logo.jpg" alt="Logo" class="img-fluid">
                            </div>
                            <h1 class="system-title" data-aos="fade-up" data-aos-delay="600">ระบบจัดการใบรับรองนักวิจัย</h1>
                            <p class="faculty-name" data-aos="fade-up" data-aos-delay="700">คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม</p>
                        </div>
                        
                        <div class="login-divider"></div>
                        
                        <form id="loginForm" method="post" data-aos="fade-up" data-aos-delay="800">
                            <div class="mb-3" data-aos="fade-right" data-aos-delay="900">
                                <label for="userType" class="form-label">
                                    <i class="bi bi-person-badge me-2"></i>ประเภทผู้ใช้
                                </label>
                                <div class="user-type-wrapper">
                                    <select class="form-select" id="userType" name="userType">
                                        <option value="researcher">นักวิจัย</option>
                                        <option value="lab">ห้องปฏิบัติการ</option>
                                        <option value="executive">ผู้มีอำนาจลงนาม</option>
                                        <option value="student" disabled>นิสิต (กำลังพัฒนา)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3" data-aos="fade-right" data-aos-delay="1000">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-2"></i>ชื่อผู้ใช้
                                </label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="กรุณาใส่ชื่อผู้ใช้" autocomplete="username">
                            </div>
                            <div class="mb-4" data-aos="fade-right" data-aos-delay="1100">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-2"></i>รหัสผ่าน
                                </label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="กรุณาใส่รหัสผ่าน" autocomplete="current-password">
                            </div>
                            <div class="d-grid gap-2" data-aos="zoom-in" data-aos-delay="1200">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                                </button>
                            </div>
                        </form>

                        <div class="d-grid gap-2" data-aos="zoom-in" data-aos-delay="1200">
                               
                                    <a href="https://scribehow.com/viewer/_____MTit1nHxS72HjMWucM1_Hw" >คู่มือการใช้งาน</a>
                               
                            </div>


                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer" data-aos="fade-up" data-aos-offset="100">
        <div class="container">
            <div class="card footer-card">
                <div class="card-body text-center p-3">
                    <!-- <a class="btn btn-primary btn-sm" href="#">ประเมินการใช้งาน</a> -->
                    <p class="footer-title mt-2">โครงการวิจัยสถาบัน การพัฒนาแพลตฟอร์มการออกใบรับรองนักวิจัยสำหรับใช้ในการขอทุนสนับสนุนงานวิจัย</p>
                    <p class="footer-subtitle">Development of a Researcher Certification Platform for Research Grant Applications.</p>
                    <p class="footer-copyright">&copy; <?php echo date('Y'); ?> คณะเกษตรศาสตร์ ทรัพยากรธรรมชาติและสิ่งแวดล้อม มหาวิทยาลัยนเรศวร</p>
                    <p class="footer-subtitle">
    จำนวนผู้ใช้งาน <span class="counter" id="visitor-counter">กำลังโหลด...</span> อัพเดทล่าสุด: <span id="last-updated">กำลังโหลด...</span>
</p>
              
                    
                   
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS Animation
        AOS.init({
            once: true, // Animation only happens once
            mirror: false, // No mirror animation when scrolling back up
            duration: 800 // Animation duration
        });
        
        const loginForm = document.getElementById('loginForm');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // ฟังก์ชันแสดง loading
        function showLoading() {
            loadingOverlay.style.visibility = 'visible';
            loadingOverlay.style.opacity = '1';
        }

        // ฟังก์ชันซ่อน loading
        function hideLoading() {
            loadingOverlay.style.opacity = '0';
            setTimeout(() => {
                loadingOverlay.style.visibility = 'hidden';
            }, 300);
        }

        async function submitForm(event) {
            event.preventDefault();

            // แสดง loading spinner
            showLoading();

            const form = document.getElementById('loginForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const data = await response.json();
                
                // ซ่อน loading
                hideLoading();

                if (data.success) {
                    let dashboardUrl = '';
                    switch (data.userType) {
                        case 'researcher':
                            dashboardUrl = 'researcher.php';
                            break;
                        case 'lab':
                            dashboardUrl = 'lab_dashboard.php';
                            break;
                        case 'executive':
                            dashboardUrl = 'executive_dashboard.php';
                            break;
                        case 'student':
                            dashboardUrl = 'student.php';
                            break;
                    }
                    
                    // แสดง alert สำเร็จ
                    Swal.fire({
                        icon: 'success',
                        title: 'เข้าสู่ระบบสำเร็จ',
                        text: 'กำลังนำท่านไปยังหน้าหลัก...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = dashboardUrl;
                    });
                } else {
                    // แสดง alert เกิดข้อผิดพลาด
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: data.message || 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
                    });
                }
            } catch (error) {
                // ซ่อน loading
                hideLoading();
                
                // แสดง alert เกิดข้อผิดพลาด
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: error.message || 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                });
                console.error('Error:', error);
            }
        }

        // ผูกฟังก์ชันกับเหตุการณ์ submit ของฟอร์ม
        loginForm.addEventListener('submit', submitForm);
    });

     // ฟังก์ชันสำหรับเพิ่มจำนวนผู้เข้าชม (เรียกเมื่อโหลดหน้าครั้งแรก)
     function incrementCounter() {
            fetch('counter.php?action=increment')
                .then(response => response.json())
                .then(data => {
                    updateCounterDisplay(data);
                })
                .catch(error => {
                    console.error('เกิดข้อผิดพลาด:', error);
                });
        }
        
        // ฟังก์ชันสำหรับอัพเดทจำนวนผู้เข้าชมแบบ real-time (ไม่เพิ่มค่า)
        function getCounter() {
            fetch('counter.php')
                .then(response => response.json())
                .then(data => {
                    updateCounterDisplay(data);
                })
                .catch(error => {
                    console.error('เกิดข้อผิดพลาด:', error);
                });
        }
        
        // ฟังก์ชันสำหรับอัพเดทการแสดงผลบนหน้าเว็บ
        function updateCounterDisplay(data) {
            document.getElementById('visitor-counter').textContent = data.counter;
            document.getElementById('last-updated').textContent = data.formatted_time;
        }
        
        // เมื่อโหลดหน้าครั้งแรกให้เพิ่มจำนวนผู้เข้าชม
        document.addEventListener('DOMContentLoaded', incrementCounter);
        
        // อัพเดททุกๆ 30 วินาที โดยไม่เพิ่มค่า
        setInterval(getCounter, 30000);


    </script>
</body>
</html>