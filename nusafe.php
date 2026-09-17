<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user_id']);
    header("location: authnusafe.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: authnusafe.php");
    exit();
}

require_once 'function.php';

$labs = getAllLabs($conn);
foreach ($labs as $lab) {
    $lab_id = $lab['id'];
}


?>

<!DOCTYPE html>
<html lang="th">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการใบรับรองนักวิจัยเพื่อใช้ประกอบในการขอทุน (สำหรับเจ้าหน้าที่ Lab)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

       
<style>
/* ===========================================
   CSS VARIABLES - ORANGE & GRAY THEME
   =========================================== */

   :root {
    /* Primary Orange Colors */
    --primary-orange: #FF8C42;
    --secondary-orange: #FF7F3F;
    --light-orange: #FFE5D9;
    --dark-orange: #E85D04;
    --accent-orange: #F77F00;
    
    /* Gray Colors */
    --primary-gray: #495057;
    --secondary-gray: #6C757D;
    --light-gray: #F8F9FA;
    --dark-gray: #343A40;
    --medium-gray: #ADB5BD;
    
    /* Gradients */
    --gradient-primary: linear-gradient(135deg, #FF8C42 0%, #FF7F3F 100%);
    --gradient-secondary: linear-gradient(135deg, #F77F00 0%, #FF8C42 100%);
    --gradient-card: linear-gradient(145deg, #ffffff 0%, #FFF8F5 100%);
    --gradient-orange-light: linear-gradient(135deg, #FFE5D9 0%, #FFF8F5 100%);
    
    /* Shadows */
    --shadow-soft: 0 8px 32px rgba(255, 140, 66, 0.15);
    --shadow-hover: 0 12px 40px rgba(255, 140, 66, 0.25);
    --shadow-gray: 0 4px 20px rgba(73, 80, 87, 0.1);
    
    /* Effects */
    --border-radius: 16px;
    --border-radius-small: 8px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===========================================
   GLOBAL STYLES
   =========================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Kanit', sans-serif;
    background: linear-gradient(135deg, #FFF8F5 0%, #F8F9FA 100%);
    min-height: 100vh;
    color: var(--dark-gray);
    line-height: 1.6;
}

/* Scroll Styles */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: var(--light-gray);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--secondary-orange);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-orange);
}

/* ===========================================
   HEADER & NAVIGATION
   =========================================== */

.navbar-custom {
    background-color: #fff;
    box-shadow: var(--shadow-gray);
    padding: 12px 0;
}

.logo-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    box-shadow: var(--shadow-gray);
    border: 2px solid var(--light-orange);
    padding: 2px;
    transition: var(--transition);
}

.logo-circle:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-soft);
    border-color: var(--primary-orange);
}

.logo-circle img {
    max-width: 80%;
    max-height: 80%;
    object-fit: contain;
}

.dashboard-header {
    background: var(--gradient-primary);
    padding: 2rem 0;
    box-shadow: var(--shadow-soft);
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.dashboard-header::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
}

.header-content {
    position: relative;
    z-index: 2;
}

.dashboard-title {
    color: white;
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.dashboard-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
    font-weight: 300;
    margin-top: 0.5rem;
}

.header-icon {
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.8);
    margin-right: 1rem;
    animation: pulse 2s infinite;
}

/* ===========================================
   MAIN CONTENT & LAYOUT
   =========================================== */

.main-content {
    padding: 3rem 0;
    position: relative;
}

.container {
    max-width: 1400px;
}

#data-container {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
    padding: 15px;
}

/* ===========================================
   LAB SECTIONS
   =========================================== */

.lab-section {
    margin-bottom: 3rem;
    background: var(--gradient-card);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid rgba(255, 140, 66, 0.1);
}

.lab-section:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.lab-header {
    background: var(--gradient-primary);
    padding: 1.5rem 2rem;
    position: relative;
    overflow: hidden;
}

.lab-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.lab-section:hover .lab-header::before {
    left: 100%;
}

.lab-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    position: relative;
}

.lab-title i {
    margin-right: 0.75rem;
    font-size: 1.3rem;
}

.lab-header-animated {
    margin: 15px 0;
    padding: 10px 0;
}

.animated-content {
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: flex-start;
}

.pulse-dot {
    width: 12px;
    height: 12px;
    background: var(--primary-orange);
    border-radius: 50%;
    animation: pulse 3s infinite;
}

.animated-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--dark-gray);
    margin: 0;
    position: relative;
    animation: slideInLeft 2s ease-out;
}

.underline-animation {
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, var(--primary-orange), transparent);
    border-radius: 1px;
    animation: expandWidth 1s ease-out 2s both;
}

/* ===========================================
   TABLE STYLES
   =========================================== */

.table-responsive-container {
    width: 100%;
    overflow-x: auto;
    margin-bottom: 20px;
    border-radius: var(--border-radius-small);
    box-shadow: var(--shadow-gray);
}

.table {
    width: 100%;
    min-width: 800px;
    margin-bottom: 0;
    background-color: #fff;
    border-collapse: collapse;
    font-size: 16px;
}

.table thead th {
    position: sticky;
    top: 0;
    background: var(--gradient-primary);
    color: white;
    font-weight: 600;
    padding: 12px 8px;
    text-align: center;
    border: 1px solid rgba(255, 140, 66, 0.2);
    white-space: nowrap;
    font-size: 16px;
    z-index: 10;
}

.table tbody td {
    padding: 10px 8px;
    vertical-align: middle;
    border: 1px solid #dee2e6;
    text-align: center;
    max-width: 150px;
    word-wrap: break-word;
    font-size: 16px;
}

/* Column Specific Styles */
.table tbody td:nth-child(1) { /* ชื่อผู้ใช้ */
    max-width: 120px;
    text-align: left;
    font-weight: 500;
    color: var(--dark-gray);
}

.table tbody td:nth-child(2) { /* เบอร์โทร */
    max-width: 100px;
    font-family: 'Courier New', monospace;
    color: var(--primary-gray);
}

.table tbody td:nth-child(3) { /* สถานะ */
    max-width: 140px;
}

.table tbody td:nth-child(4) { /* วัตถุประสงค์ */
    max-width: 110px;
}

.table tbody td:nth-child(5),
.table tbody td:nth-child(6),
.table tbody td:nth-child(7),
.table tbody td:nth-child(8),
.table tbody td:nth-child(10) { /* ปุ่มต่างๆ */
    max-width: 90px;
    min-width: 80px;
}

.table tbody td:nth-child(9) { /* เวลา */
    max-width: 130px;
    font-size: 12px;
    color: var(--secondary-gray);
    font-style: italic;
}

/* ===========================================
   BADGE STYLES
   =========================================== */

.badge {
    display: inline-block;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 500;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 16px;
    border: 1px solid transparent;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.badge-primary {
    color: #fff;
    background: var(--gradient-primary);
    border-color: var(--primary-orange);
}

.badge-success {
    color: #fff;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-color: #28a745;
}

.badge-info {
    color: #fff;
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    border-color: #17a2b8;
}

.badge-warning {
    color: var(--dark-gray);
    background: linear-gradient(135deg, #FFC107 0%, #FFE082 100%);
    border-color: #FFC107;
}

.badge-danger {
    color: #fff;
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    border-color: #dc3545;
}

.badge-default {
    color: var(--dark-gray);
    background: linear-gradient(135deg, var(--secondary-gray) 0%, var(--medium-gray) 100%);
    border-color: var(--secondary-gray);
}

/* Status & Purpose Badges */
.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
    text-align: center;
    display: inline-block;
    min-width: 120px;
    box-shadow: var(--shadow-gray);
}

.status-pending {
    background: var(--gradient-orange-light);
    color: var(--dark-orange);
    border: 1px solid var(--primary-orange);
}

.status-approved {
    background: linear-gradient(135deg, #d4edda 0%, #a8e6a3 100%);
    color: #155724;
    border: 1px solid #27ae60;
}

.status-rejected {
    background: linear-gradient(135deg, #f8d7da 0%, #f5b7b1 100%);
    color: #721c24;
    border: 1px solid #e74c3c;
}

.status-completed {
    background: var(--gradient-primary);
    color: white;
    border: 1px solid var(--primary-orange);
}

.purpose-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.purpose-research {
    background: var(--gradient-orange-light);
    color: var(--dark-orange);
    border: 1px solid var(--primary-orange);
}

.purpose-service {
    background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
    color: #6a1b9a;
    border: 1px solid #9c27b0;
}

.purpose-education {
    background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
    color: #ef6c00;
    border: 1px solid #ff9800;
}

/* ===========================================
   BUTTON STYLES
   =========================================== */

.btn {
    display: inline-block;
    padding: 6px 12px;
    margin: 2px;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.5;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    border: 1px solid transparent;
    border-radius: 6px;
    transition: var(--transition);
    white-space: nowrap;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 11px;
    border-radius: 4px;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.action-btn {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    border: none;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 100px;
    margin: 0.2rem;
    box-shadow: var(--shadow-gray);
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.action-btn i {
    margin-right: 0.5rem;
    font-size: 0.9rem;
}

.btn-info {
    color: #fff;
    background: var(--gradient-secondary);
    border-color: var(--accent-orange);
}

.btn-info:hover {
    background: var(--gradient-primary);
    color: white;
}

.btn-success {
    color: #fff;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-color: #28a745;
}

.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    color: white;
}

.btn-danger {
    color: #fff;
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    border-color: #dc3545;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #e8630a 100%);
    color: white;
}

.btn-secondary {
    color: #fff;
    background: linear-gradient(135deg, var(--secondary-gray) 0%, var(--medium-gray) 100%);
    border-color: var(--secondary-gray);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, var(--primary-gray) 0%, var(--secondary-gray) 100%);
    color: white;
}

/* ===========================================
   EMPTY STATE STYLES
   =========================================== */

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
}

.empty-state-icon {
    font-size: 4rem;
    color: var(--secondary-orange);
    margin-bottom: 1rem;
    opacity: 0.6;
}

.empty-state-text {
    color: var(--secondary-gray);
    font-size: 1.1rem;
    font-weight: 400;
}

.simple-empty-badge {
    max-width: 400px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: white;
    border: 2px dashed var(--medium-gray);
    border-radius: 25px;
    padding: 15px 25px;
    margin: 15px 0;
    color: var(--secondary-gray);
    font-size: 14px;
    font-weight: 500;
    transition: var(--transition);
}

.simple-empty-badge:hover {
    border-color: var(--primary-orange);
    background: var(--light-orange);
    color: var(--dark-orange);
}

.badge-icon {
    font-size: 18px;
    opacity: 0.7;
}

.badge-text {
    white-space: nowrap;
}

/* ===========================================
   LOADING & UTILITY STYLES
   =========================================== */

.loading-cell {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading-shimmer 1.5s infinite;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

.table-responsive-container::after {
    content: '← เลื่อนเพื่อดูข้อมูลเพิ่มเติม →';
    display: block;
    text-align: center;
    color: var(--secondary-gray);
    font-size: 11px;
    padding: 5px;
    background: var(--light-gray);
    border-top: 1px solid #dee2e6;
}

/* ===========================================
   ANIMATIONS
   =========================================== */

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes slideInLeft {
    from { transform: translateX(-30px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes expandWidth {
    from { width: 0; }
    to { width: 100%; }
}

@keyframes loading-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===========================================
   RESPONSIVE DESIGN
   =========================================== */

/* Large Desktop (1200px+) */
@media (min-width: 1200px) {
    .table { font-size: 14px; }
    .table thead th,
    .table tbody td { padding: 12px 10px; }
    .btn { font-size: 13px; padding: 6px 12px; }
    .table-responsive-container::after { display: none; }
}

/* Medium Desktop (992px - 1199px) */
@media (max-width: 1199px) and (min-width: 992px) {
    .table { min-width: 900px; font-size: 13px; }
    .animated-title { font-size: 20px; }
}

/* Tablet (768px - 991px) */
@media (max-width: 991px) and (min-width: 768px) {
    #data-container { padding: 10px; }
    .table { min-width: 800px; font-size: 12px; }
    .table thead th,
    .table tbody td { padding: 8px 6px; }
    .animated-title { font-size: 18px; }
    .animated-content { gap: 10px; }
    .btn-sm { padding: 3px 6px; font-size: 10px; }
    .badge { font-size: 10px; padding: 3px 6px; }
}

/* Mobile Landscape (576px - 767px) */
@media (max-width: 767px) and (min-width: 576px) {
    #data-container { padding: 8px; }
    .table-responsive-container { border-radius: 4px; }
    .table { min-width: 700px; font-size: 11px; }
    .table thead th { padding: 8px 4px; font-size: 11px; }
    .table tbody td { padding: 6px 4px; font-size: 11px; }
    .animated-content { flex-direction: column; text-align: center; gap: 8px; }
    .animated-title { font-size: 16px; }
    .underline-animation { width: 100%; flex: none; }
    .btn-sm { padding: 2px 4px; font-size: 9px; margin: 1px; }
    .badge { font-size: 9px; padding: 2px 4px; }
}

/* Mobile Portrait (< 576px) */
@media (max-width: 575px) {
    #data-container { padding: 5px; }
    .table { min-width: 600px; font-size: 10px; }
    .table thead th { padding: 6px 3px; font-size: 10px; }
    .table tbody td { padding: 5px 3px; font-size: 10px; }
    .animated-content { flex-direction: column; text-align: center; gap: 5px; }
    .animated-title { font-size: 14px; }
    .pulse-dot { width: 8px; height: 8px; }
    .btn-sm { padding: 2px 3px; font-size: 8px; margin: 0.5px; }
    .badge { font-size: 8px; padding: 2px 3px; }
    .table tbody td:nth-child(2) { display: none; }
    .table thead th:nth-child(2) { display: none; }
    .dashboard-title { font-size: 2rem; }
    .header-icon { font-size: 2rem; margin-right: 0.5rem; }
    .main-content { padding: 2rem 0; }
    .lab-header { padding: 1rem 1.5rem; }
    .lab-title { font-size: 1.2rem; }
    .action-btn { padding: 0.4rem 0.8rem; font-size: 0.7rem; min-width: 80px; }
    .status-badge { min-width: 100px; font-size: 0.7rem; }
    .simple-empty-badge { max-width: 100%; justify-content: center; }
    .badge-text { white-space: normal; text-align: center; }
}

/* Very Small Mobile (< 400px) */
@media (max-width: 399px) {
    .table { min-width: 500px; }
    .table tbody td:nth-child(4),
    .table tbody td:nth-child(9) { display: none; }
    .table thead th:nth-child(4),
    .table thead th:nth-child(9) { display: none; }
}

/* ===========================================
   PRINT STYLES
   =========================================== */

@media print {
    .table-responsive-container { overflow: visible !important; box-shadow: none; }
    .table { min-width: auto !important; font-size: 10px !important; }
    .btn { display: none !important; }
    .animated-content { page-break-after: avoid; }
}

/* ===========================================
   ADDITIONAL COMPONENTS
   =========================================== */

.cert-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: var(--transition);
}

.cert-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.cert-header {
    background: var(--gradient-primary);
    color: white;
    padding: 1rem 1.5rem;
    font-weight: 600;
    font-size: 1.1rem;
}

.cert-body {
    padding: 1.5rem;
}

/* SweetAlert2 Custom Styles */
.swal2-popup {
    border-radius: var(--border-radius) !important;
    box-shadow: var(--shadow-hover) !important;
}

.swal2-title {
    color: var(--dark-orange) !important;
    font-family: 'Kanit', sans-serif !important;
}

.swal2-content {
    font-family: 'Kanit', sans-serif !important;
}

.swal2-confirm {
    background: var(--gradient-primary) !important;
    border-radius: 25px !important;
    padding: 0.75rem 2rem !important;
    font-weight: 500 !important;
}

.swal2-cancel {
    background: var(--secondary-gray) !important;
    border-radius: 25px !important;
    padding: 0.75rem 2rem !important;
    font-weight: 500 !important;
}

/* Apply animations to elements */
.modern-table-container,
.empty-card-minimal,
.custom-alert-box,
.simple-empty-badge {
    animation: fadeInUp 0.5s ease-out;
}

</style>
</head>


<body class="bg-light">
<div class="container-fluid py-2 ml-5 mr-5">
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <!-- Desktop Layout (แสดงแถวเดียว) -->
        <div class="d-none d-md-flex justify-content-between align-items-center">
            <!-- โลโก้และชื่อระบบ (ด้านซ้าย) -->
            <div class="d-flex align-items-center">
                <div class="logo-circle me-3">
                    <img src="./vendor/image/logo.jpg" alt="Logo">
                </div>
                <div>
                    <h5 class="mb-1">ระบบออกใบรับรองนักวิจัยเพื่อใช้ประกอบการขอทุนสนับสนุนการวิจัย คณะเกษตรศาสตร์ฯ </h5>
                    <p class="text-muted mb-0">สำหรับคณะกรรมการความปลอดภัย มหาวิทยาลัยนเรศวร</p>
                </div>
            </div>
            
            <!-- ข้อมูลผู้ใช้และปุ่มออกจากระบบ (ด้านขวา) -->
            <div class="d-flex align-items-center">
                <div class="text-end me-3">
                    <span class="d-block text-primary fw-bold"><?php echo 'ผู้ใช้:'.$_SESSION['user_id'].' '; ?></span>
                </div>
                <a href="./nusafe.php?logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ
                </a>
            </div>
        </div>
        
        <!-- Mobile Layout (แสดงแบบซ้อนกัน) -->
        <div class="d-md-none">
            <!-- ส่วนบน: โลโก้และชื่อระบบ -->
            <div class="d-flex align-items-center mb-2">
                <div class="logo-circle me-2">
                    <img src="./vendor/image/logo.jpg" alt="Logo">
                </div>
                <div>
                    <h6 class="mb-1">ระบบออกใบรับรองนักวิจัย</h6>
                    <p class="text-muted small mb-0">คณะเกษตรศาสตร์ฯ (สำหรับคณะกรรมการความปลอดภัย มหาวิทยาลัยนเรศวร)</p>
                </div>
            </div>
            
            <!-- ส่วนล่าง: ข้อมูลผู้ใช้และปุ่มออกจากระบบ -->
            <div class="d-flex justify-content-between align-items-center mt-2">
                <div>
                    <span class="small text-primary fw-bold"><?php echo 'ผู้ใช้:'.$_SESSION['user_id'].' '; ?></span>
                </div>
                <a href="./nusafe.php?logout" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> ออก
                </a>
            </div>
        </div>
    </div>
</div>
    <div class="card p-4">

            <div id="data-container"></div>
        
    </div>
</div>
</div>

 <!-- Footer -->
 
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
window.onload = function () {
    const labId = <?= json_encode($lab_id) ?>;
    if (labId) {
        loadData(labId);
    } else {
        Swal.fire("ไม่พบ lab_id", "กรุณาเข้าสู่ระบบใหม่", "error");
    }
};


function loadDataForAllLabs() {
    fetch("function_nusafe.php")
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

            const dataContainer = document.getElementById("data-container");
dataContainer.innerHTML = "";

for (const labId in data.data) {
    if (data.data.hasOwnProperty(labId)) {
        const labInfo = data.data[labId];
        const labName = labInfo.name;
        const requests = labInfo.requests;

        // สร้างส่วนหัวของตารางสำหรับ Lab นี้
        const labHeader5 = document.createElement("div");
        labHeader5.className = "lab-header-animated";
        labHeader5.innerHTML = `
            <div class="animated-content">
                <div class="pulse-dot"></div>
                <h5 class="animated-title">${labName}</h5>
                <div class="underline-animation"></div>
            </div>
        `;
        dataContainer.appendChild(labHeader5);

        if (requests.length > 0) {
            // สร้าง responsive container สำหรับตาราง
            const tableContainer = document.createElement("div");
            tableContainer.className = "table-responsive-container";
            
            // สร้างตารางถ้ามีข้อมูล
            const table = document.createElement("table");
            table.className = "table table-primary table-striped table-hover";
            
            const thead = document.createElement("thead");
            thead.innerHTML = `
                <tr>
                    <th>ชื่อผู้ใช้</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>สถานะ</th>
                    <th>วัตถุประสงค์</th>
                    <th>NU-LAB-02</th>
                    <th>อนุมัติ</th>
                    <th>ไม่อนุมัติ</th>
                    <th>ดูใบประกาศ</th>
                    <th>เวลา</th>
                    <th>NU-LAB-01</th>
                </tr>
            `;
            table.appendChild(thead);
            
            const tbody = document.createElement("tbody");
            tbody.id = `data-body-${labId}`;
            table.appendChild(tbody);
            
            // เพิ่มตารางใน container
            tableContainer.appendChild(table);
            dataContainer.appendChild(tableContainer);

            // แสดงข้อมูลสำหรับ Lab นี้ใน tbody ที่สร้างขึ้น
            const tbodyForLab = document.getElementById(`data-body-${labId}`);
            requests.forEach(row => {
                const tr = document.createElement("tr");

                // --- Column: Username ---
                const usernameTd = document.createElement("td");
                usernameTd.id = `username-${row.user_id}-${labId}`;
                usernameTd.className = "loading-cell";
                usernameTd.textContent = "กำลังโหลด...";
                tr.appendChild(usernameTd);
                
                fetchUsername(row.user_id, (fullName) => {
                    const userDisplayTd = document.getElementById(`username-${row.user_id}-${labId}`);
                    if (userDisplayTd) {
                        userDisplayTd.className = "";
                        userDisplayTd.textContent = fullName || 'ไม่พบชื่อ';
                    }
                });

                // --- Column: Phone ---
                const phoneTd = document.createElement("td");
                phoneTd.textContent = row.phone;
                tr.appendChild(phoneTd);

                // --- Column: Stage (Number Conversion) ---
                const numberTd = document.createElement("td");
                let numberText = "N/A";
                let badgeClass = "badge-default";

                switch (parseInt(row.stage)) {
                    case 1:
                        numberText = "รอห้องปฎิบัติการอนุมัติ";
                        badgeClass = "badge-warning";
                        break;
                    case 2:
                        numberText = "รอผู้บริหารอนุมัติ";
                        badgeClass = "badge-info";
                        break;
                    case 3:
                        numberText = "รออนุมัติ";
                        badgeClass = "badge-primary";
                        break;
                    case 4:
                        numberText = "ห้องปฎิบัติการไม่อนุมัติ";
                        badgeClass = "badge-danger";
                        break;
                    case 5:
                        numberText = "ผู้บริหารไม่อนุมัติ";
                        badgeClass = "badge-danger";
                        break;
                    case 6:
                        numberText = "สำเร็จ/จัดส่งนักวิจัย";
                        badgeClass = "badge-success";
                        break;
                }

                // สร้าง badge element
                const badgeSpan = document.createElement("span");
                badgeSpan.className = `badge ${badgeClass}`;
                badgeSpan.textContent = numberText;
                badgeSpan.title = numberText; // เพิ่ม tooltip

                numberTd.appendChild(badgeSpan);
                tr.appendChild(numberTd);

                // --- Column: Purpose ---
                const purposeTd = document.createElement("td");
                let purposeText = "ไม่ระบุ";
                switch (row.purpose) {
                    case 'R':
                        purposeText = "งานวิจัย";
                        break;
                    case 'S':
                        purposeText = "บริการวิชาการ";
                        break;
                    case 'T':
                        purposeText = "การเรียน/การสอน";
                        break;
                }
                purposeTd.textContent = purposeText;
                purposeTd.title = purposeText; // เพิ่ม tooltip
                tr.appendChild(purposeTd);

                // --- Column: View PDF Button ---
                const pdfButtonTd = document.createElement("td");
                const pdfButton = document.createElement("button");
                pdfButton.className = "btn btn-sm btn-info mb-1";
                pdfButton.textContent = "ดู NU-LAB-02";
                pdfButton.title = "ดูใบ NU-LAB-02"; // เพิ่ม tooltip
                pdfButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    // เพิ่ม loading state
                    pdfButton.textContent = "กำลังโหลด...";
                    pdfButton.disabled = true;
                    
                    const encodedPdfPath = encodeURI(row.path);
                    showPDF(encodedPdfPath);
                    
                    // Reset button หลัง 2 วินาที
                    setTimeout(() => {
                        pdfButton.textContent = "ดู NU-LAB-02";
                        pdfButton.disabled = false;
                    }, 2000);
                });
                pdfButtonTd.appendChild(pdfButton);
                
                // ปุ่มดู NU-LAB-01
                const pdf01Button = document.createElement("button");
                pdf01Button.className = "btn btn-sm btn-primary ml-1 mb-1";
                pdf01Button.textContent = "ดู NU-LAB-01";
                pdf01Button.title = "ดูใบ NU-LAB-01";
                pdf01Button.addEventListener("click", (event) => {
                    event.preventDefault();
                    pdf01Button.textContent = "กำลังโหลด...";
                    pdf01Button.disabled = true;
                    
                    fetchNu01Path(row.user_id).then(nu01Path => {
                        showPDF(encodeURI(nu01Path));
                        setTimeout(() => {
                            pdf01Button.textContent = "ดู NU-LAB-01";
                            pdf01Button.disabled = false;
                        }, 2000);
                    }).catch(err => {
                        Swal.fire('ข้อผิดพลาด', err.message, 'error');
                        pdf01Button.textContent = "ดู NU-LAB-01";
                        pdf01Button.disabled = false;
                    });
                });
                pdfButtonTd.appendChild(pdf01Button);
                
                tr.appendChild(pdfButtonTd);

                // --- Approve/Disapprove Buttons ---
                const approveButtonTd = document.createElement("td");
                const disapproveButtonTd = document.createElement("td");

                if (row.stage === 3) {
                    const approveButton = document.createElement("button");
                    approveButton.className = "btn btn-sm btn-success";
                    approveButton.textContent = "อนุมัติ";
                    approveButton.title = "อนุมัติคำขอ";
                    approveButton.addEventListener("click", () => {
                      
                            approveButton.textContent = "กำลังดำเนินการ...";
                            approveButton.disabled = true;
                            approveRequest(row);
                        
                    });
                    approveButtonTd.appendChild(approveButton);

                    const disapproveButton = document.createElement("button");
                    disapproveButton.className = "btn btn-sm btn-danger";
                    disapproveButton.textContent = "ไม่อนุมัติ";
                    disapproveButton.title = "ไม่อนุมัติคำขอ";
                    disapproveButton.addEventListener("click", () => {
                     
                            disapproveButton.textContent = "กำลังดำเนินการ...";
                            disapproveButton.disabled = true;
                            disapproveRequest(row);
                        
                    });
                    disapproveButtonTd.appendChild(disapproveButton);
                } else {
                    approveButtonTd.innerHTML = '<span class="text-muted">-</span>';
                    disapproveButtonTd.innerHTML = '<span class="text-muted">-</span>';
                }

                tr.appendChild(approveButtonTd);
                tr.appendChild(disapproveButtonTd);

                // --- Certificate Button ---
                const certButtonTd = document.createElement("td");
                const certButton = document.createElement("button");
                certButton.className = "btn btn-sm btn-secondary";
                certButton.textContent = "ดูใบประกาศ";
                certButton.title = "ดูใบประกาศ";
                certButton.addEventListener("click", () => {
                    window.open(`view_certificate.php?user_id=${row.user_id}`, '_blank');
                });
                certButtonTd.appendChild(certButton);
                tr.appendChild(certButtonTd);

                // --- Timestamp ---
                const timestampTd = document.createElement("td");
                if (row.timestamp) {
                    const timestamp = new Date(row.timestamp);
                    const formattedTime = `${timestamp.toLocaleTimeString('th-TH', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    })} ${timestamp.toLocaleDateString('th-TH', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    })}`;
                    timestampTd.textContent = formattedTime;
                    timestampTd.title = timestamp.toLocaleString('th-TH'); // Full datetime in tooltip
                } else {
                    timestampTd.textContent = "N/A";
                }
                tr.appendChild(timestampTd);

                // --- NU-LAB-01 Button ---
                const viewNu01ButtonCell = createViewNu01Button(row.user_id);
                tr.appendChild(viewNu01ButtonCell);

                tbodyForLab.appendChild(tr);
            });
        } else {
            // ถ้าไม่มีข้อมูล แสดง empty state
            const simpleBadge = document.createElement("div");
            simpleBadge.className = "simple-empty-badge";
            simpleBadge.innerHTML = `
                <span class="badge-icon">🔍</span>
                <span class="badge-text">ยังไม่มีผู้ยื่นขอใช้ห้องปฎิบัติการ</span>
            `;
            dataContainer.appendChild(simpleBadge);
        }
    }
}
        })
        .catch(error => {
            console.error("Fetch error:", error);
            Swal.fire({
                title: "เกิดข้อผิดพลาด!",
                text: "ไม่สามารถโหลดข้อมูล: " + error.message,
                icon: "error",
                confirmButtonText: "ตกลง"
            });
        });
}

window.onload = loadDataForAllLabs;
function handleResponsiveTable() {
    const tables = document.querySelectorAll('.table-responsive-container');
    
    tables.forEach(container => {
        const table = container.querySelector('table');
        
        // เช็คว่าตารางมีความกว้างเกินขนาดหน้าจอหรือไม่
        if (table.scrollWidth > container.clientWidth) {
            container.classList.add('has-scroll');
        } else {
            container.classList.remove('has-scroll');
        }
    });
}

// เรียกใช้เมื่อ window resize
window.addEventListener('resize', handleResponsiveTable);
window.addEventListener('load', handleResponsiveTable);

// เพิ่ม CSS สำหรับ empty state ถ้ายังไม่มี
if (!document.getElementById('empty-state-css')) {
    const emptyStateCSS = document.createElement('style');
    emptyStateCSS.id = 'empty-state-css';
    emptyStateCSS.textContent = `
        .empty-card-minimal {
            max-width: 500px;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px 20px;
            margin: 15px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .empty-card-minimal::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .status-badge {
            background: #fff5f5;
            color: #e53e3e;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: 1px solid #fed7d7;
        }
        
        .message-text {
            font-size: 15px;
            color: #4a5568;
            font-weight: 500;
        }
    `;
    document.head.appendChild(emptyStateCSS);
}

function createViewNu01Button(userId) {
    const viewNu01ButtonTd = document.createElement("td");
    const viewNu01Button = document.createElement("button");
    viewNu01Button.className = "btn btn-sm btn-info";
    viewNu01Button.textContent = "ดู NU-LAB-01";
    viewNu01Button.addEventListener("click", () => {
        fetch(`nu01view.php?user_id=${userId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success' && data.path) {
                    window.open(data.path, '_blank');
                } else {
                    Swal.fire('Error', data.message || 'ไม่พบไฟล์เอกสาร NU-LAB-01', 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching NU-LAB-01:', error);
                Swal.fire('Error', error.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล NU-LAB-01', 'error');
            });
    });
    viewNu01ButtonTd.appendChild(viewNu01Button);
    return viewNu01ButtonTd;
}



async function showPDF(pdfUrl, showLoading = true) {
    try {
        // สร้าง URL ที่มี timestamp เพื่อบังคับให้โหลดใหม่
        const timestamp = new Date().getTime();
        const separator = pdfUrl.includes('?') ? '&' : '?';
        const cacheBustedUrl = pdfUrl + separator + 'v=' + timestamp + '&nocache=' + Date.now();
        
        // เปิดหน้าต่างใหม่ไปยัง URL โดยตรง
        // การใช้ Blob อาจมีปัญหากับ PDF Viewer บนบางเบราว์เซอร์ (Safari/Mobile)
        // หรือปัญหา Memory เวลาเปิดไฟล์ใหญ่
        const newWindow = window.open(cacheBustedUrl, '_blank');
        
        if (!newWindow) {
            alert('กรุณาอนุญาตให้เปิด popup หรือคลิกลิงก์ด้านล่าง');
        }
        
    } catch (error) {
        console.error('Error loading PDF:', error);
        alert('เกิดข้อผิดพลาดในการเปิด PDF: ' + error.message);
    }
}

function fetchUsername(userId, callback) {
    fetch('get_username.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`,
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            callback(null);
        } else if (data.fname && data.lname) {
            callback(`${data.fname} ${data.lname}`);
        } else {
            callback('ไม่พบข้อมูลชื่อ');
        }
    })
    .catch(error => {
        callback('เกิดข้อผิดพลาด');
    });
}

function approveRequest(requestData) {
    Swal.fire({
        title: 'อนุมัติใบคำร้อง',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'อนุมัติ',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading indicator
            Swal.fire({
                title: 'กำลังดำเนินการ',
                text: 'กรุณารอสักครู่...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // ดึง path ของ NU-LAB-01 ก่อน
            fetchNu01Path(requestData.user_id)
                .then(nu01Path => {
                    // ดึงข้อมูล email จาก API
                    fetch('get_username.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `user_id=${requestData.user_id}`,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // ส่งข้อมูลที่รวม path และ email
                        sendApprovalData({ 
                            path: nu01Path,
                            user_id: requestData.user_id,
                            email: data.email
                        });
                    })
                    .catch(() => {
                        // หากดึง email ไม่ได้ ให้ส่งเฉพาะ path เหมือนเดิม
                        sendApprovalData({ 
                            path: nu01Path,
                            user_id: requestData.user_id
                        });
                    });
                })
                .catch(error => {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถดึงข้อมูล NU-LAB-01: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'ตกลง'
                    });
                });
        }
    });
}

// ตัวอย่างการใช้งานฟังก์ชัน fetchUsername ใหม่
function exampleUsage() {
    fetchUsername(123, (userData) => {
        if (userData) {
            console.log('ชื่อ-นามสกุล:', userData.fullName);
            console.log('อีเมล์:', userData.email);
        } else {
            console.log('ไม่พบข้อมูลผู้ใช้');
        }
    });
}

function fetchNu01Path(userId) {
    return fetch(`nu01view.php?user_id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.path) {
                return data.path;
            } else {
                throw new Error(data.message || 'ไม่พบไฟล์เอกสาร NU-LAB-01');
            }
        });
}

function sendApprovalData(dataToSend) {
    // แสดง SweetAlert2 พร้อมช่องกรอกรหัสผ่าน
    Swal.fire({
        title: 'ยืนยันการอนุมัติ',
        html: `
            <div style="text-align: left; margin: 20px 0;">
                <label for="approval-password" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    กรุณากรอกรหัสผ่านเพื่อยืนยันการอนุมัติ:
                </label>
                <p>รหัสผ่านเป็นรูปเเบบ Case-sensitive กรุณากรอกพิมพ์เล็ก-พิมพ์ใหญ่ ให้ถูกต้อง</p>
                <input 
                    type="password" 
                    id="approval-password" 
                    class="swal2-input" 
                    placeholder="รหัสผ่าน"
                    style="width: 100%; margin: 0;"
                    autocomplete="current-password"
                >
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'อนุมัติ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        preConfirm: () => {
            const passwordInput = document.getElementById('approval-password');
            const password = passwordInput.value;
            
            // อนุญาตให้รหัสผ่านเป็นค่าว่างได้ตามที่ผู้ใช้ต้องการ
            // ลบโค้ดส่วน validation ออก
            
            // ตรวจสอบให้แน่ใจว่าได้ค่าที่ถูกต้องและ case sensitive
            console.log('Password entered:', password);
            console.log('Password length:', password.length);
            
            return password;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const password = result.value;
            processApproval(dataToSend, password);
        }
    });
}

function processApproval(dataToSend, password) {
    // เตรียมข้อมูลสำหรับส่ง รวมถึงรหัสผ่าน และ email
    const approvalData = {
        path: dataToSend.path,
        user_id: dataToSend.user_id,
        P12_password: password,  // เพิ่มรหัสผ่านในตัวแปร P12_password
        email: dataToSend.email  // เพิ่ม email ในข้อมูลที่ส่ง
    };
    
    // Debug: แสดงค่า password และ email ก่อนส่ง
    console.log('Sending password:', password);
    console.log('Sending email:', dataToSend.email);
    console.log('Full approval data:', approvalData);

    // Show loading indicator
    Swal.fire({
        title: 'กำลังประมวลผล',
        text: 'กรุณารอสักครู่...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const url = 'nusafe_approve.php';

    // วิธีที่ 1: ใช้ JSON format (แนะนำ)
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(approvalData)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
            });
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Invalid JSON response:", text);
                throw new Error(`Server returned invalid JSON: ${text.substring(0, 100)}...`);
            }
        });
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'สำเร็จ!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                const currentLabId = new URLSearchParams(window.location.search).get('lab_id');
                if (currentLabId) {
                    loadData(currentLabId);
                }
                window.location.href = 'nusafe.php';
            });
        } else {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                window.location.href = 'nusafe.php';
            });
        }
    })
    .catch(error => {
            console.error('Error sending approval data:', error);
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: 'ไม่สามารถส่งข้อมูลการอนุมัติ: ' + error.message,
                icon: 'error',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                window.location.href = 'nusafe.php';
            });
        });
}

function disapproveRequest(requestData) {
    Swal.fire({
        title: 'ไม่อนุมัติใบคำร้อง',
        html: `<input type="text" id="disapprove-reason" class="swal2-input" placeholder="เหตุผลที่ไม่อนุมัติ">`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ส่งเหตุผล',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
        preConfirm: () => {
            const reason = document.getElementById('disapprove-reason').value;
            if (!reason) {
                Swal.showValidationMessage(`กรุณากรอกเหตุผลที่ไม่อนุมัติ`);
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = result.value;
            // ตรวจสอบว่ามีเหตุผลที่ถูกป้อนเข้ามาจริง
            if (reason && reason.trim() !== '') {
                // แสดง loading indicator
                Swal.fire({
                    title: 'กำลังดำเนินการ',
                    text: 'กรุณารอสักครู่...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // ดึง path ของ NU-LAB-01 ก่อน (เหมือนกับ approveRequest)
                fetchNu01Path(requestData.user_id)
                    .then(nu01Path => {
                        // ส่งข้อมูลพร้อม path ที่ได้จาก API
                        sendDisapprovalData({
                            path: nu01Path,
                            user_id: requestData.user_id,
                            lab_id: requestData.lab_id,
                            id: requestData.id
                        }, reason);
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: 'ไม่สามารถดึงข้อมูล NU-LAB-01: ' + error.message,
                            icon: 'error',
                            confirmButtonText: 'ตกลง'
                        });
                    });
            } else {
                Swal.fire({
                    title: 'ผิดพลาด!',
                    text: 'กรุณากรอกเหตุผลที่ไม่อนุมัติ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            }
        }
    });
}

/**
 * ส่งข้อมูลการไม่อนุมัติไปยัง server ด้วย POST method
 * @param {Object} dataToSend - ข้อมูลคำขอ (user_id, path, lab_id, id)
 * @param {string} reason - เหตุผลที่ไม่อนุมัติ
 */
function sendDisapprovalData(dataToSend, reason) {
    // ตรวจสอบว่ามีข้อมูลที่จำเป็นครบถ้วน
    if (!dataToSend || !dataToSend.user_id) {
        console.error('Missing required data for disapproval', dataToSend);
        Swal.fire({
            title: 'ข้อมูลไม่ครบถ้วน!',
            text: 'ไม่สามารถดำเนินการได้เนื่องจากข้อมูลไม่ครบถ้วน',
            icon: 'error',
            confirmButtonText: 'ตกลง'
        });
        return;
    }

    // สร้างข้อมูลสำหรับส่ง
    const postData = {
        user_id: dataToSend.user_id,
        path: dataToSend.path || '', // path ที่ได้จาก fetchNu01Path
        reason: reason,
        lab_id: dataToSend.lab_id || '',
        id: dataToSend.id || ''
    };

    // ส่งข้อมูลด้วย POST method
    fetch('nusafe_disapprove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(postData).toString()
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Invalid JSON response:", text);
                throw new Error(`Server returned invalid JSON: ${text.substring(0, 100)}...`);
            }
        });
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'สำเร็จ!',
                text: data.message || 'ไม่อนุมัติคำขอเรียบร้อยแล้ว',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                // รีเฟรชข้อมูล
                const currentLabId = new URLSearchParams(window.location.search).get('lab_id');
                if (currentLabId) {
                    loadData(currentLabId);
                } else {
                    // หากไม่มี lab_id ให้โหลดหน้าหลัก
                    window.location.href = 'nusafe.php';
                }
            });
        } else {
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: data.message || 'เกิดข้อผิดพลาดในการไม่อนุมัติ',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            });
        }
    })
    .catch(error => {
        console.error('Error sending disapproval data:', error);
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: 'ไม่สามารถส่งข้อมูลการไม่อนุมัติ: ' + error.message,
            icon: 'error',
            confirmButtonText: 'ตกลง'
        });
    });
}

function displayFiles(certData, certType) {
            const fileDisplay = document.getElementById('fileDisplay');
            fileDisplay.innerHTML = ''; // ล้างเนื้อหาเดิม
            
            if (certType === 'nu') {
                // กรณีเป็นประเภท nu (มีหลายไฟล์)
                Object.keys(certData).forEach((key) => {
                    // ข้ามค่า certype
                    if (key === 'certype') return;
                    
                    const file = certData[key];
                    const ext = file.type;
                    const orgNumber = key; // เลของค์ประกอบ
                    
                    const cardDiv = document.createElement('div');
                    cardDiv.className = 'cert-card';
                    
                    const headerDiv = document.createElement('div');
                    headerDiv.className = 'cert-header';
                    headerDiv.textContent = `องค์ประกอบที่ ${orgNumber}`;
                    
                    const bodyDiv = document.createElement('div');
                    bodyDiv.className = 'cert-body';
                    
                    if (ext === 'pdf') {
                        // กรณีเป็นไฟล์ PDF ใช้ iframe
                        const iframe = document.createElement('iframe');
                        iframe.src = file.path;
                        iframe.style.width = '100%';
                        iframe.style.height = '500px';
                        iframe.style.border = 'none';
                        bodyDiv.appendChild(iframe);
                    } else if (ext === 'image') {
                        // กรณีเป็นรูปภาพ
                        const img = document.createElement('img');
                        img.src = file.path;
                        img.style.maxWidth = '100%';
                        img.style.height = 'auto';
                        bodyDiv.appendChild(img);
                    } else {
                        // กรณีไม่ทราบประเภทไฟล์
                        const link = document.createElement('a');
                        link.href = file.path;
                        link.target = '_blank';
                        link.textContent = `ดาวน์โหลดไฟล์`;
                        link.className = 'btn btn-primary';
                        bodyDiv.appendChild(link);
                    }
                    
                    cardDiv.appendChild(headerDiv);
                    cardDiv.appendChild(bodyDiv);
                    fileDisplay.appendChild(cardDiv);
                });
                
                // ถ้าไม่มีไฟล์
                if (fileDisplay.children.length === 0) {
                    fileDisplay.innerHTML = '<div class="alert alert-warning">ไม่พบไฟล์ใบประกาศ</div>';
                }
            } else if (certType === 'elearning') {
                // กรณีเป็นประเภท elearning (มีไฟล์เดียว)
                if (certData && (certData.pdf || certData.image)) {
                    const cardDiv = document.createElement('div');
                    cardDiv.className = 'cert-card';
                    
                    const headerDiv = document.createElement('div');
                    headerDiv.className = 'cert-header';
                    headerDiv.textContent = 'ใบประกาศ E-Learning';
                    
                    const bodyDiv = document.createElement('div');
                    bodyDiv.className = 'cert-body';
                    
                    if (certData.pdf) {
                        // กรณีเป็นไฟล์ PDF
                        const iframe = document.createElement('iframe');
                        iframe.src = certData.pdf;
                        iframe.style.width = '100%';
                        iframe.style.height = '600px';
                        iframe.style.border = 'none';
                        bodyDiv.appendChild(iframe);
                    } else if (certData.image) {
                        // กรณีเป็นรูปภาพ
                        const img = document.createElement('img');
                        img.src = certData.image;
                        img.style.maxWidth = '100%';
                        img.style.height = 'auto';
                        bodyDiv.appendChild(img);
                    }
                    
                    cardDiv.appendChild(headerDiv);
                    cardDiv.appendChild(bodyDiv);
                    fileDisplay.appendChild(cardDiv);
                } else {
                    fileDisplay.innerHTML = '<div class="alert alert-warning">ไม่พบไฟล์ใบประกาศ E-Learning</div>';
                }
            } else {
                fileDisplay.innerHTML = '<div class="alert alert-danger">ประเภทใบประกาศไม่ถูกต้อง</div>';
            }
        }

        // ดึงข้อมูลใบประกาศจาก API
        fetch(`get_certificates.php?user_id=<?php echo $user_id; ?>`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(certData => {
                // ตรวจสอบประเภทของใบประกาศ
                const certType = certData.certype || 'nu';
                // แสดงไฟล์
                displayFiles(certData, certType);
            })
            .catch(error => {
                document.getElementById('fileDisplay').innerHTML = `
                    <div class="alert alert-danger">
                        <h4>เกิดข้อผิดพลาด</h4>
                        <p>${error.message}</p>
                    </div>`;
                console.error('Error fetching certificate data:', error);
            });

function checkAndClearSession() {
    fetch('clear_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.cleared) {
                Swal.fire({
                    title: 'Session หมดอายุ!',
                    text: data.message,
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    // ทำการ Redirect ไปยังหน้า Login หรือ Reload หน้าเว็บ
                    window.location.href = 'login.php'; // เปลี่ยนเป็น URL หน้า Login ของคุณ
                    // หรือ window.location.reload();
                });
            }
        })
        .catch(error => {
            console.error('เกิดข้อผิดพลาดในการตรวจสอบ Session:', error);
        });
}

setInterval(checkAndClearSession, 600000);
checkAndClearSession();
</script>



</body>
</html>

