<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?require_login=1");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch fresh user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Parse addresses
$addresses = [];
if (!empty($user['address'])) {
    $addresses = json_decode($user['address'], true);
    if (!is_array($addresses)) {
        // Fallback for old plain text address
        $addresses = [
            ['id' => 1, 'name' => 'ที่อยู่จัดส่ง', 'text' => $user['address'], 'is_default' => true]
        ];
    }
}

// Handle forms
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Handle avatar upload
        $profile_pic = $user['profile_pic'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_name = $_FILES['avatar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = 'uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
                $dest_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Delete old file if exists and is not default
                    if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) {
                        @unlink($user['profile_pic']);
                    }
                    $profile_pic = $dest_path;
                }
            } else {
                $error = 'ไฟล์รูปภาพไม่ถูกต้อง รองรับเฉพาะ JPG, JPEG, PNG และ GIF';
            }
        }
        
        if (empty($username)) {
            $error = 'กรุณากรอกชื่อผู้ใช้งาน';
        } else {
            // Update db
            $stmt_update = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, profile_pic = ? WHERE id = ?");
            if ($stmt_update->execute([$username, $email, $phone, $profile_pic, $user_id])) {
                $_SESSION['username'] = $username;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['profile_pic'] = $profile_pic;
                
                $message = 'อัปเดตโปรไฟล์เรียบร้อยแล้วค่ะ! ✨';
                // Refresh local user data
                $user['username'] = $username;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['profile_pic'] = $profile_pic;
            } else {
                $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        }
    }
    
    // Add Address
    if (isset($_POST['add_address'])) {
        $addr_name = trim($_POST['addr_name'] ?? '');
        $addr_text = trim($_POST['addr_text'] ?? '');
        
        if (!empty($addr_name) && !empty($addr_text)) {
            $new_addr_id = time() . rand(100, 999);
            // If it's the first address, make it default
            $is_default = empty($addresses) ? true : false;
            
            $addresses[] = [
                'id' => $new_addr_id,
                'name' => $addr_name,
                'text' => $addr_text,
                'is_default' => $is_default
            ];
            
            $serialized = json_encode($addresses, JSON_UNESCAPED_UNICODE);
            $stmt_addr = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
            if ($stmt_addr->execute([$serialized, $user_id])) {
                $_SESSION['user_address'] = $addr_text; // Backwards compatible default string
                $message = 'เพิ่มที่อยู่จัดส่งใหม่เรียบร้อยแล้วค่ะ! 🏡';
            }
        }
    }
    
    // Delete Address
    if (isset($_POST['delete_address'])) {
        $del_id = $_POST['address_id'] ?? '';
        $new_addresses = [];
        $was_default = false;
        
        foreach ($addresses as $addr) {
            if ($addr['id'] == $del_id) {
                if ($addr['is_default']) {
                    $was_default = true;
                }
                continue;
            }
            $new_addresses[] = $addr;
        }
        
        // If the deleted address was default, set the first one as default
        if ($was_default && !empty($new_addresses)) {
            $new_addresses[0]['is_default'] = true;
        }
        
        $addresses = $new_addresses;
        $serialized = json_encode($addresses, JSON_UNESCAPED_UNICODE);
        $stmt_addr = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
        if ($stmt_addr->execute([$serialized, $user_id])) {
            // Find default text
            $default_text = '';
            foreach ($addresses as $a) {
                if ($a['is_default']) $default_text = $a['text'];
            }
            $_SESSION['user_address'] = $default_text;
            $message = 'ลบที่อยู่จัดส่งเรียบร้อยแล้วค่ะ 🗑️';
        }
    }
    
    // Set Default Address
    if (isset($_POST['set_default'])) {
        $def_id = $_POST['address_id'] ?? '';
        foreach ($addresses as &$addr) {
            if ($addr['id'] == $def_id) {
                $addr['is_default'] = true;
                $_SESSION['user_address'] = $addr['text'];
            } else {
                $addr['is_default'] = false;
            }
        }
        
        $serialized = json_encode($addresses, JSON_UNESCAPED_UNICODE);
        $stmt_addr = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
        if ($stmt_addr->execute([$serialized, $user_id])) {
            $message = 'ตั้งที่อยู่หลักเรียบร้อยแล้วค่ะ 🌟';
        }
    }
}

// Avatar image display helper
$avatar_url = !empty($user['profile_pic']) && file_exists($user['profile_pic']) ? $user['profile_pic'] : 'images/default-avatar.png';
// Create default avatar placeholder if not exists
if (!file_exists('images/default-avatar.png')) {
    if (!file_exists('images')) mkdir('images', 0777, true);
    // Create simple pink circle avatar
    $im = imagecreatetruecolor(150, 150);
    $bg = imagecolorallocate($im, 255, 230, 235);
    $pink = imagecolorallocate($im, 247, 168, 184);
    imagefilledrectangle($im, 0, 0, 150, 150, $bg);
    imagefilledellipse($im, 75, 75, 120, 120, $pink);
    imagepng($im, 'images/default-avatar.png');
    imagedestroy($im);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน | NightCake</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --bg-color: <?= htmlspecialchars($theme_bg_color) ?> !important;
        }
        body {
            background-color: var(--bg-color);
            transition: background-color 0.5s ease;
        }
        .profile-container {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            gap: 3rem;
            margin-top: 2rem;
            margin-bottom: 4rem;
            align-items: start;
        }
        .profile-sidebar {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .avatar-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(247, 168, 184, 0.2);
            margin-bottom: 1.5rem;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .avatar-wrapper:hover {
            transform: scale(1.05);
        }
        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .avatar-wrapper:hover .avatar-overlay {
            opacity: 1;
        }
        .profile-section-title {
            font-family: var(--font-serif);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .address-card {
            background-color: #fff;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            position: relative;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
        }
        .address-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(247, 168, 184, 0.08);
        }
        .address-card.default {
            border-color: var(--primary-color);
            background-color: rgba(247, 168, 184, 0.03);
        }
        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .address-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-main);
        }
        .address-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        .address-badge {
            background-color: var(--primary-color);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .address-actions {
            display: flex;
            gap: 0.8rem;
            align-items: center;
        }
        .action-link-btn {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            transition: color 0.2s;
        }
        .action-link-btn:hover {
            color: var(--primary-color);
        }
        .action-link-btn.delete-btn:hover {
            color: #ef4444;
        }
        
        /* Modal Style for Slide Bouncy Animation */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-card {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-50px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.open .modal-card {
            transform: translateY(0) scale(1);
        }
        .modal-header {
            font-family: var(--font-serif);
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: var(--text-main);
        }
        .add-btn-circle {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .add-btn-circle:hover {
            transform: rotate(90deg) scale(1.1);
            background-color: var(--primary-hover);
        }
        
        .alert-toast {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
        }
        .alert-toast-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="announcement-bar">
    🧁 ฉลองวันพิเศษของคุณด้วยโบว์เค้กมินิมอล บริการเขียนหน้าเค้กฟรี! สั่งล่วงหน้าและจัดส่งเดลิเวอรี่ทั่วกรุงเทพฯ
</div>

<header>
    <a href="index.php" class="logo">NightCake<span>.</span></a>
    <nav>
        <a href="index.php">หน้าแรก</a>
        <a href="index.php#products">เค้กทั้งหมด</a>
        <a href="index.php#contact">ติดต่อเรา / สาขา</a>
        <a href="#" id="cart-toggle-nav" style="display: flex; align-items: center; gap: 0.4rem; font-weight: 500; margin-right: 0.8rem;">
            🛒 ตะกร้าสินค้า (<span id="cart-count-nav">0</span>)
        </a>
        
        <!-- User Profile Nav -->
        <span class="user-auth-nav" style="border-left: 1.5px solid var(--border-color, #f1e1e1); padding-left: 0.8rem; display: inline-flex; align-items: center; gap: 0.8rem;">
            <a href="profile.php" style="font-size: 0.85rem; color: var(--primary-color); font-weight: 600; text-decoration: none;">👋 สวัสดี, <?= htmlspecialchars($_SESSION['username']) ?></a>
            <a href="logout.php" style="font-size: 0.85rem; color: var(--text-light); font-weight: 500;">ออกจากระบบ</a>
        </span>
    </nav>
</header>

<main class="container">
    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 2rem; margin-top: 1.5rem;">
        <a href="index.php" style="color: inherit; text-decoration: none;">หน้าแรก</a> &gt; 
        <span>โปรไฟล์ของฉัน</span>
    </div>

    <?php if($message): ?>
        <div class="alert-toast"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert-toast alert-toast-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-container">
        <!-- Left: Edit Profile Details -->
        <div class="card" style="margin: 0;">
            <div class="profile-section-title">ข้อมูลส่วนตัว</div>
            <form method="POST" enctype="multipart/form-data" id="profile-form">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="profile-sidebar">
                    <div class="avatar-wrapper" onclick="triggerAvatarSelect()">
                        <img id="avatar-preview" src="<?= htmlspecialchars($avatar_url) ?>" alt="User Avatar">
                        <div class="avatar-overlay">เปลี่ยนรูปภาพ 📷</div>
                    </div>
                    <input type="file" id="avatar-input" name="avatar" style="display: none;" accept="image/*" onchange="previewAvatar(this)">
                </div>

                <div class="form-group">
                    <label>ชื่อผู้ใช้งาน (Username)</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>เบอร์โทรศัพท์ (Phone)</label>
                    <input type="tel" name="phone" class="form-control" placeholder="ระบุเบอร์โทรศัพท์" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>อีเมล (Email)</label>
                    <input type="email" name="email" class="form-control" placeholder="ระบุอีเมล" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-block" style="margin-top: 1.5rem; justify-content: center;">บันทึกข้อมูลส่วนตัว 💾</button>
            </form>
        </div>

        <!-- Right: Address List Management -->
        <div class="card" style="margin: 0;">
            <div class="profile-section-title">
                <span>สมุดที่อยู่จัดส่ง</span>
                <button type="button" class="add-btn-circle" onclick="openAddressModal()" title="เพิ่มที่อยู่ใหม่">+</button>
            </div>

            <div id="address-list">
                <?php if(!empty($addresses)): ?>
                    <?php foreach($addresses as $addr): ?>
                        <div class="address-card <?= $addr['is_default'] ? 'default' : '' ?>" id="addr-card-<?= $addr['id'] ?>">
                            <div class="address-header">
                                <span class="address-name">🏡 <?= htmlspecialchars($addr['name']) ?></span>
                                <?php if($addr['is_default']): ?>
                                    <span class="address-badge">หลัก 🌟</span>
                                <?php endif; ?>
                            </div>
                            <div class="address-text"><?= nl2br(htmlspecialchars($addr['text'])) ?></div>
                            <div class="address-header">
                                <div class="address-actions">
                                    <?php if(!$addr['is_default']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="set_default" value="1">
                                            <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                            <button type="submit" class="action-link-btn">ตั้งเป็นที่อยู่หลัก</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDeleteAddress(this, '<?= $addr['id'] ?>')">
                                        <input type="hidden" name="delete_address" value="1">
                                        <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                        <button type="submit" class="action-link-btn delete-btn">ลบ</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-light); padding: 3rem 1rem;">
                        <span style="font-size: 2.5rem; display: block; margin-bottom: 1rem;">📍</span>
                        ยังไม่มีข้อมูลที่อยู่จัดส่ง คุณสามารถกดปุ่ม + ด้านบนเพื่อเพิ่มที่อยู่ได้ค่ะ
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Add Address Bouncy Modal Overlay -->
<div class="modal-overlay" id="address-modal" onclick="closeAddressModalOnClick(event)">
    <div class="modal-card">
        <div class="modal-header">
            <span>เพิ่มที่อยู่จัดส่งใหม่</span>
            <button type="button" class="modal-close" onclick="closeAddressModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="add_address" value="1">
            <div class="form-group">
                <label>ชื่อเรียกที่อยู่ (เช่น บ้าน / ที่ทำงาน / คอนโด) *</label>
                <input type="text" name="addr_name" class="form-control" placeholder="เช่น บ้านแม่, คอนโดลาดพร้าว" required>
            </div>
            <div class="form-group">
                <label>ที่อยู่จัดส่ง / สถานที่จัดงานโดยละเอียด *</label>
                <textarea name="addr_text" class="form-control" rows="4" placeholder="ระบุบ้านเลขที่, ชื่ออาคาร, ซอย, ถนน, ตำบล, อำเภอ, จังหวัด และรหัสไปรษณีย์" required></textarea>
            </div>
            <button type="submit" class="btn btn-block" style="justify-content: center; margin-top: 1.5rem;">บันทึกที่อยู่ 🏡</button>
        </form>
    </div>
</div>

<!-- Cart Drawer Template (needed for footer/script dependency) -->
<?php include_once 'cart_drawer.php'; ?>

<script>
function triggerAvatarSelect() {
    document.getElementById('avatar-input').click();
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
            // Add click visual pop/scale effect
            const wrapper = document.querySelector('.avatar-wrapper');
            wrapper.style.transform = 'scale(0.95)';
            setTimeout(() => {
                wrapper.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    wrapper.style.transform = '';
                }, 150);
            }, 100);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Bouncy Modal Toggle Logic
function openAddressModal() {
    const modal = document.getElementById('address-modal');
    modal.classList.add('open');
}

function closeAddressModal() {
    const modal = document.getElementById('address-modal');
    modal.classList.remove('open');
}

function closeAddressModalOnClick(e) {
    if (e.target.id === 'address-modal') {
        closeAddressModal();
    }
}

// Address delete confirmation with animation
function confirmDeleteAddress(form, id) {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบที่อยู่นี้?')) {
        const card = document.getElementById('addr-card-' + id);
        if (card) {
            // Shake and fade out transition
            card.style.transition = 'all 0.5s ease';
            card.style.transform = 'translateX(20px)';
            card.style.opacity = '0';
            setTimeout(() => {
                form.submit();
            }, 400);
            return false; // prevent instant submit
        }
        return true;
    }
    return false;
}
</script>

<footer style="margin-top: 4rem;">
    <div class="container footer-content" style="text-align: center; color: var(--text-light); font-size: 0.85rem; border-top: 1px solid var(--border-color); padding-top: 2rem; padding-bottom: 2rem;">
        <p>&copy; 2026 NightCake. All rights reserved. Crafting sweet memories since 2023.</p>
    </div>
</footer>

<script src="js/script.js?v=<?= time() ?>"></script>
</body>
</html>
