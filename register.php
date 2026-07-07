<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_msg = '';
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($username) || empty($password)) {
        $error_msg = 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน';
    } elseif ($password !== $confirm_password) {
        $error_msg = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
    } elseif (strlen($password) < 6) {
        $error_msg = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error_msg = 'ชื่อผู้ใช้งานนี้ถูกใช้ไปแล้ว กรุณาเลือกชื่ออื่น';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, email, phone, address) VALUES (?, ?, ?, ?, ?)");
                if ($stmt_insert->execute([$username, $hashed_password, $email, $phone, $address])) {
                    $success_msg = 'สมัครสมาชิกสำเร็จ! กำลังนำคุณไปยังหน้าเข้าสู่ระบบ...';
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php?registered=1';
                        }, 2000);
                    </script>";
                } else {
                    $error_msg = 'เกิดข้อผิดพลาดทางระบบ กรุณาลองใหม่อีกครั้ง';
                }
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
    <title>สมัครสมาชิก | NightCake</title>
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
            max-width: 450px;
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
    <div class="auth-subtitle">สมัครสมาชิกเพื่อรับความสุขแสนละมุนจากร้านเบเกอรี่</div>

    <?php if($error_msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if($success_msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">ชื่อผู้ใช้งาน (Username) *</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้งาน" required autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">รหัสผ่าน (Password) *</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="รหัสผ่านอย่างน้อย 6 ตัวอักษร" required autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="confirm_password">ยืนยันรหัสผ่าน (Confirm Password) *</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านอีกครั้ง" required autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="email">อีเมล (Email)</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="เช่น customer@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label for="phone">เบอร์โทรศัพท์ (Phone)</label>
            <input type="tel" id="phone" name="phone" class="form-control" placeholder="เช่น 0891234567" autocomplete="tel">
        </div>
        <div class="form-group">
            <label for="address">ที่อยู่จัดส่งเริ่มต้น (Default Delivery Address)</label>
            <textarea id="address" name="address" class="form-control" rows="3" placeholder="ระบุบ้านเลขที่, ถนน, แขวง, เขต, จังหวัด และรหัสไปรษณีย์"></textarea>
        </div>
        
        <button type="submit" class="btn-auth">สมัครสมาชิก 🍰</button>
    </form>

    <div class="auth-footer">
        มีบัญชีผู้ใช้งานอยู่แล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a>
    </div>
</div>

</body>
</html>
