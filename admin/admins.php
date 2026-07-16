<?php
include_once 'header.php';

// Handle admin registration
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif ($password !== $confirm_password) {
        $error = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } elseif (strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "ชื่อผู้ใช้งาน '$username' นี้มีในระบบแล้ว";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt_ins = $pdo->prepare("INSERT INTO admin (username, password, role) VALUES (?, ?, 'admin')");
                if ($stmt_ins->execute([$username, $hashed])) {
                    $success = "สมัครสมาชิกผู้ดูแลระบบใหม่ '$username' สำเร็จเรียบร้อยแล้วค่ะ! 🎉";
                } else {
                    $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                }
            }
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาดทางเทคนิค: " . $e->getMessage();
        }
    }
}

// Fetch all admins
$stmt_all = $pdo->query("SELECT id, username, role FROM admin ORDER BY id ASC");
$admins = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem; align-items: start; margin-bottom: 3rem;">
    <!-- Left: Admins List Table -->
    <div class="admin-card" style="margin: 0;">
        <div class="admin-card-title">👥 บัญชีผู้ดูแลระบบในปัจจุบัน</div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">รหัส</th>
                        <th>ชื่อผู้ใช้งาน</th>
                        <th>ระดับ / ยศ</th>
                        <th style="text-align: right; width: 100px;">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $adm): ?>
                        <tr>
                            <td><strong>#<?= $adm['id'] ?></strong></td>
                            <td><?= htmlspecialchars($adm['username']) ?></td>
                            <td>
                                <span class="admin-badge admin-badge-success">
                                    👑 <?= htmlspecialchars($adm['role'] ?? 'admin') ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <span style="color: #10b981; font-weight: 500; font-size: 0.85rem;">🟢 เปิดใช้งาน</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right: Add New Admin Form -->
    <div class="admin-card" style="margin: 0; padding: 2rem;">
        <div class="admin-card-title" style="margin-bottom: 0.5rem; text-align: center;">➕ สมัครสมาชิกแอดมินใหม่</div>
        <p style="font-size: 0.8rem; color: var(--text-muted); text-align: center; margin-bottom: 1.5rem;">สร้างบัญชีแอดมินใหม่เพื่อร่วมกันดูแลระบบหลังบ้าน</p>

        <?php if($error): ?>
            <div style="background-color: #fef2f2; color: #c81e1e; border: 1px solid #fbd5d5; padding: 0.75rem 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.2rem; font-size: 0.85rem; font-weight: 500; text-align: center;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div style="background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; padding: 0.75rem 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.2rem; font-size: 0.85rem; font-weight: 500; text-align: center;">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="register_admin" value="1">
            <div class="admin-form-group" style="text-align: left; margin-bottom: 1rem;">
                <label style="display:block; margin-bottom: 0.4rem; font-weight:600; font-size: 0.82rem; color: var(--text-main);">ชื่อผู้ใช้งาน (Username) *</label>
                <input type="text" name="username" class="admin-form-control" placeholder="ระบุชื่อแอดมินใหม่" required autocomplete="username" style="width: 100%; box-sizing: border-box;">
            </div>
            <div class="admin-form-group" style="text-align: left; margin-bottom: 1rem;">
                <label style="display:block; margin-bottom: 0.4rem; font-weight:600; font-size: 0.82rem; color: var(--text-main);">รหัสผ่าน (Password) *</label>
                <input type="password" name="password" class="admin-form-control" placeholder="••••••••" required autocomplete="new-password" style="width: 100%; box-sizing: border-box;">
            </div>
            <div class="admin-form-group" style="text-align: left; margin-bottom: 1.5rem;">
                <label style="display:block; margin-bottom: 0.4rem; font-weight:600; font-size: 0.82rem; color: var(--text-main);">ยืนยันรหัสผ่าน *</label>
                <input type="password" name="confirm_password" class="admin-form-control" placeholder="••••••••" required autocomplete="new-password" style="width: 100%; box-sizing: border-box;">
            </div>
            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem; font-size: 0.9rem; font-weight: 600;">
                ลงทะเบียนแอดมินใหม่ 🎂
            </button>
        </form>
    </div>
</div>

<?php
include_once 'footer.php';
?>
