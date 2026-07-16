<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการหลังบ้าน | NightCake</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <div>
                <div class="sidebar-brand-text">NightCake<span>.</span></div>
                <div class="sidebar-brand-sub">ระบบจัดการหลังบ้าน</div>
            </div>
        </div>

        <div class="sidebar-section-label">เมนูหลัก</div>
        <ul class="sidebar-menu">
            <li class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
                <a href="index.php"><span class="menu-icon">📊</span> แดชบอร์ด</a>
            </li>
            <li class="<?= in_array($current_page, ['products.php','add_product.php','edit_product.php']) ? 'active' : '' ?>">
                <a href="products.php"><span class="menu-icon">🎂</span> จัดการสินค้า</a>
            </li>
            <li class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">
                <a href="categories.php"><span class="menu-icon">📂</span> หมวดหมู่สินค้า</a>
            </li>
            <li class="<?= $current_page == 'settings.php' ? 'active' : '' ?>">
                <a href="settings.php"><span class="menu-icon">⚙️</span> ตั้งค่าระบบ</a>
            </li>
            <li class="<?= $current_page == 'customize_theme.php' ? 'active' : '' ?>">
                <a href="customize_theme.php"><span class="menu-icon">🎨</span> ปรับแต่งหน้าเว็บ</a>
            </li>
            <li class="<?= $current_page == 'admins.php' ? 'active' : '' ?>">
                <a href="admins.php"><span class="menu-icon">👥</span> ผู้ดูแลระบบ</a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="../index.php" target="_blank"><span class="menu-icon">🌐</span> ดูหน้าเว็บหลัก</a>
            <a href="logout.php" class="logout-link"><span class="menu-icon">🚪</span> ออกจากระบบ</a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main">
        <header class="admin-header">
            <div class="admin-header-title">
                <?php
                    if($current_page == 'index.php') echo 'แดชบอร์ดและคำสั่งซื้อล่าสุด';
                    elseif($current_page == 'products.php') echo 'รายการสินค้าทั้งหมด';
                    elseif($current_page == 'add_product.php') echo 'เพิ่มเค้กใหม่';
                    elseif($current_page == 'edit_product.php') echo 'แก้ไขรายละเอียดสินค้า';
                    elseif($current_page == 'categories.php') echo 'จัดการหมวดหมู่สินค้า';
                    elseif($current_page == 'settings.php') echo 'ตั้งค่าระบบการแจ้งเตือน';
                    elseif($current_page == 'customize_theme.php') echo 'ปรับแต่งธีมหน้าแรก (Live Customizer)';
                    elseif($current_page == 'admins.php') echo 'จัดการบัญชีผู้ดูแลระบบ (Admins)';
                    else echo 'ระบบจัดการ';
                ?>
            </div>
            <div class="user-profile">
                <span>🍰</span> แอดมิน NightCake
            </div>
        </header>
        <div class="admin-body">
