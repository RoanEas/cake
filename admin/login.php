<?php
require_once '../config.php';

if(isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';
if (isset($_GET['registered'])) {
    $success = 'สมัครสมาชิกผู้ดูแลระบบสำเร็จ! กรุณาเข้าสู่ระบบค่ะ 🍰';
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['role'] = !empty($admin['role']) ? $admin['role'] : 'admin';
        header("Location: index.php");
        exit;
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบจัดการ | NightCake</title>
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
        <div class="login-subtitle">เข้าสู่ระบบจัดการหลังบ้าน</div>

        <?php if($error): ?>
            <div class="login-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="login-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="admin-form-group" style="text-align: left;">
                <label>ชื่อผู้ใช้</label>
                <input type="text" name="username" class="admin-form-control" placeholder="admin" required>
            </div>
            <div class="admin-form-group" style="text-align: left;">
                <label>รหัสผ่าน</label>
                <input type="password" name="password" class="admin-form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem; margin-top: 0.5rem; font-size: 0.95rem;">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="login-footer">
            <a href="../index.php">← กลับหน้าแรก</a>
        </div>
    </div>
</div>

</body>
</html>
