<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_msg = '';
$success_msg = '';

if (isset($_GET['registered'])) {
    $success_msg = 'สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบด้วยบัญชีของคุณค่ะ 🍰';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_msg = 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Store user details in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['user_address'] = $user['address'];
                
                $success_msg = 'เข้าสู่ระบบสำเร็จ! ยินดีต้อนรับกลับนะคะ...';
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 1000);
                </script>";
            } else {
                $error_msg = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง';
            }
        } catch (PDOException $e) {
            $error_msg = 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | NightCake</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #fcf6f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: #ffffff;
            border: 1.5px solid var(--border-color, #f1e1e1);
            border-radius: var(--border-radius-md, 16px);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-subtle, 0 4px 20px rgba(247, 168, 184, 0.08));
        }
        .auth-title {
            font-family: var(--font-serif);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .auth-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: var(--text-main);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border-color, #f1e1e1);
            border-radius: var(--border-radius-sm, 8px);
            font-family: var(--font-thai);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background-color: #fff;
            color: var(--text-main);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color, #f7a8b8);
            box-shadow: 0 0 0 3px rgba(247, 168, 184, 0.15);
        }
        .btn-auth {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.8rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            border: none;
            background-color: var(--primary-color, #f7a8b8);
            color: #fff;
            box-shadow: 0 4px 15px rgba(247, 168, 184, 0.3);
            transition: all 0.3s ease;
            font-family: inherit;
            margin-top: 1.5rem;
        }
        .btn-auth:hover {
            background-color: var(--primary-hover, #e093a2);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(247, 168, 184, 0.4);
        }
        .auth-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .auth-footer a {
            color: var(--primary-color, #f7a8b8);
            font-weight: 600;
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius-sm, 8px);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-title">NightCake.</div>
    <div class="auth-subtitle">เข้าสู่ระบบเพื่อความสะดวกในการสั่งซื้อและติดตามเค้ก</div>

    <?php if($error_msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">ชื่อผู้ใช้งาน (Username)</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้งาน" required autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">รหัสผ่าน (Password)</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="กรอกรหัสผ่าน" required autocomplete="current-password">
        </div>
        
        <button type="submit" class="btn-auth">เข้าสู่ระบบ 🎂</button>
    </form>

    <div class="auth-footer">
        ยังไม่มีบัญชีผู้ใช้งาน? <a href="register.php">สมัครสมาชิกที่นี่</a>
    </div>
</div>

</body>
</html>
