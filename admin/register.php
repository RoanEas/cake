<?php
require_once '../config.php';

if(isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if(empty($username) || empty($password)) {
        $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } elseif($password !== $confirm_password) {
        $error = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } elseif(strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    } else {
        try {
            // Check if admin username already exists
            $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            if($stmt->fetch()) {
                $error = "ชื่อผู้ใช้นี้มีในระบบแล้ว กรุณาใช้ชื่ออื่น";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
                if($stmt_insert->execute([$username, $hashed_password])) {
                    $success = "สมัครสมาชิกผู้ดูแลระบบสำเร็จ! กำลังนำคุณไปยังหน้าเข้าสู่ระบบ...";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php?registered=1';
                        }, 2000);
                    </script>";
                } else {
                    $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                }
            }
        } catch(PDOException $e) {
            $error = "เกิดข้อผิดพลาดทางเทคนิค: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิกผู้ดูแลระบบ | NightCake</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body {
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }
        .login-card {
            background: var(--card-bg);
            border-radius: var(--border-radius-md);
            padding: 3rem 2.5rem;
            box-shadow: var(--shadow-subtle);
            border: 1px solid var(--border-color);
            text-align: center;
        }
        .login-brand {
            font-family: var(--font-serif);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: var(--text-main);
        }
        .login-brand span {
            color: var(--primary-color);
            font-weight: 400;
        }
        .login-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
        }
        .login-error {
            background-color: #fef2f2;
            color: #c81e1e;
            border: 1px solid #fbd5d5;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .login-success {
            background-color: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .login-footer {
            margin-top: 2rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .login-footer a {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
        }
        .login-footer a:hover {
            color: var(--primary-hover);
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-brand">NightCake<span>.</span></div>
        <div class="login-subtitle">สมัครสมาชิกผู้ดูแลระบบ (Admin)</div>

        <?php if($error): ?>
            <div class="login-error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="login-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="admin-form-group" style="text-align: left;">
                <label>ชื่อผู้ใช้ (Username) *</label>
                <input type="text" name="username" class="admin-form-control" placeholder="ระบุชื่อผู้ใช้งานแอดมิน" required autocomplete="username">
            </div>
            <div class="admin-form-group" style="text-align: left;">
                <label>รหัสผ่าน (Password) *</label>
                <input type="password" name="password" class="admin-form-control" placeholder="••••••••" required autocomplete="new-password">
            </div>
            <div class="admin-form-group" style="text-align: left;">
                <label>ยืนยันรหัสผ่าน *</label>
                <input type="password" name="confirm_password" class="admin-form-control" placeholder="••••••••" required autocomplete="new-password">
            </div>
            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem; margin-top: 0.5rem; font-size: 0.95rem;">
                สมัครสมาชิกแอดมิน 🎂
            </button>
        </form>

        <div class="login-footer" style="display: flex; justify-content: space-between;">
            <a href="login.php">เข้าสู่ระบบที่นี่</a>
            <a href="../index.php">← กลับหน้าร้าน</a>
        </div>
    </div>
</div>

</body>
</html>
