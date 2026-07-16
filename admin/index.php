<?php
include_once 'header.php';

// Handle admin registration within the dashboard
$reg_error = '';
$reg_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_admin_index'])) {
    $reg_username = trim($_POST['reg_username'] ?? '');
    $reg_password = $_POST['reg_password'] ?? '';
    $reg_confirm = $_POST['reg_confirm_password'] ?? '';

    if (empty($reg_username) || empty($reg_password)) {
        $reg_error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif ($reg_password !== $reg_confirm) {
        $reg_error = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } elseif (strlen($reg_password) < 6) {
        $reg_error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
            $stmt->execute([$reg_username]);
            if ($stmt->fetch()) {
                $reg_error = "ชื่อผู้ใช้งาน '$reg_username' นี้มีในระบบแล้ว";
            } else {
                $hashed = password_hash($reg_password, PASSWORD_DEFAULT);
                $stmt_ins = $pdo->prepare("INSERT INTO admin (username, password, role) VALUES (?, ?, 'admin')");
                if ($stmt_ins->execute([$reg_username, $hashed])) {
                    $reg_success = "สมัครสมาชิกผู้ดูแลระบบใหม่ '$reg_username' สำเร็จเรียบร้อยแล้วค่ะ! 🎉";
                } else {
                    $reg_error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                }
            }
        } catch (PDOException $e) {
            $reg_error = "เกิดข้อผิดพลาดทางเทคนิค: " . $e->getMessage();
        }
    }
}

// Calculate Dashboard Stats
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn() ?? 0;
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Fetch Active and Archived Orders
$stmt_active = $pdo->query("SELECT * FROM orders WHERE is_archived = 0 ORDER BY created_at DESC");
$active_orders = $stmt_active->fetchAll(PDO::FETCH_ASSOC);

$stmt_archived = $pdo->query("SELECT * FROM orders WHERE is_archived = 1 ORDER BY created_at DESC");
$archived_orders = $stmt_archived->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Stats Grid Widgets -->
<div class="stats-grid">
    <div class="stat-widget">
        <div class="stat-info">
            <h4>คำสั่งซื้อทั้งหมด</h4>
            <p><?= $total_orders ?></p>
        </div>
        <div class="stat-icon">📦</div>
    </div>
    <div class="stat-widget">
        <div class="stat-info">
            <h4>รอดำเนินการ</h4>
            <p style="color: #f59e0b;"><?= $pending_orders ?></p>
        </div>
        <div class="stat-icon">⏳</div>
    </div>
    <div class="stat-widget">
        <div class="stat-info">
            <h4>สินค้าในร้าน</h4>
            <p><?= $total_products ?></p>
        </div>
        <div class="stat-icon">🎂</div>
    </div>
    <div class="stat-widget">
        <div class="stat-info">
            <h4>รายได้รวม</h4>
            <p>฿<?= number_format($total_revenue, 2) ?></p>
        </div>
        <div class="stat-icon">💰</div>
    </div>
</div>

<?php if(isset($_GET['status_update']) && $_GET['status_update'] == 'success'): ?>
    <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #10b981;">
        ✅ อัปเดตสถานะคำสั่งซื้อและส่งการแจ้งเตือน LINE เรียบร้อยแล้ว!
    </div>
<?php endif; ?>
<?php if(isset($_GET['archive_update']) && $_GET['archive_update'] == 'success'): ?>
    <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #10b981;">
        ✅ ย้ายคำสั่งซื้อลงประวัติการจัดส่ง/กู้คืนรายการ เรียบร้อยแล้ว!
    </div>
<?php endif; ?>

<!-- Tabs navigation for Active Orders & Delivery History -->
<div class="tabs-navigation" style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border-color); margin-bottom: 1.5rem; padding-bottom: 0.25rem; flex-wrap: wrap;">
    <button onclick="switchTab('active')" id="tab-active-btn" class="tab-btn" style="background: none; border: none; font-family: inherit; font-size: 0.95rem; font-weight: 600; color: var(--primary-hover); border-bottom: 3px solid var(--primary-color); padding: 0.6rem 1.2rem; cursor: pointer; transition: var(--transition);">
        🎂 รายการสั่งซื้อใหม่ / รอจัดส่ง
    </button>
    <button onclick="switchTab('archived')" id="tab-archived-btn" class="tab-btn" style="background: none; border: none; font-family: inherit; font-size: 0.95rem; font-weight: 600; color: var(--text-light); border-bottom: 3px solid transparent; padding: 0.6rem 1.2rem; cursor: pointer; transition: var(--transition);">
        📦 ประวัติการจัดส่งทั้งหมด
    </button>
    <button onclick="switchTab('register-admin')" id="tab-register-admin-btn" class="tab-btn" style="background: none; border: none; font-family: inherit; font-size: 0.95rem; font-weight: 600; color: var(--text-light); border-bottom: 3px solid transparent; padding: 0.6rem 1.2rem; cursor: pointer; transition: var(--transition);">
        👥 สมัครสมาชิกแอดมินใหม่
    </button>
</div>

<!-- 1. Active Orders Card -->
<div class="admin-card" id="active-orders-card">
    <div class="admin-card-title" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
        <span>รายการสั่งซื้อปัจจุบันที่ต้องดำเนินการ</span>
        <button onclick="window.location.reload();" class="admin-btn admin-btn-secondary admin-btn-sm" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.45rem 0.8rem; font-size: 0.8rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); cursor: pointer; background-color: var(--secondary-color); font-weight: 500; font-family: inherit;">
            🔄 รีเฟรชข้อมูล
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อลูกค้า</th>
                    <th>เบอร์โทร</th>
                    <th>ที่อยู่จัดส่ง</th>
                    <th>รายการสินค้า</th>
                    <th>ยอดรวม</th>
                    <th>การชำระเงิน</th>
                    <th>สถานะ</th>
                    <th>เวลาสั่งซื้อ</th>
                    <th>ใบเสร็จ</th>
                    <th style="text-align: right;">จัดการสถานะ / ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($active_orders) > 0): ?>
                    <?php foreach($active_orders as $order): ?>
                    <tr>
                        <td data-label="เลขที่ออเดอร์"><strong>#<?= $order['id'] ?></strong></td>
                        <td data-label="ลูกค้า"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td data-label="เบอร์โทร"><?= htmlspecialchars($order['phone']) ?></td>
                        <td data-label="ที่อยู่จัดส่ง" style="max-width: 180px; font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($order['address'] ?? '-') ?></td>
                        <td data-label="รายการสินค้า">
                            <?php
                                $items = json_decode($order['items'], true);
                                if(is_array($items)) {
                                    foreach($items as $item) {
                                        echo "• " . htmlspecialchars($item['name']) . " x " . $item['qty'] . "<br>";
                                    }
                                } else {
                                    echo "<em style='color: var(--text-muted);'>-</em>";
                                }
                                if(!empty($order['details'])) {
                                    echo "<small style='color: var(--primary-color); display:block; margin-top:0.3rem;'>📝 " . htmlspecialchars($order['details']) . "</small>";
                                }
                            ?>
                        </td>
                        <td data-label="ยอดรวม"><strong>฿<?= number_format($order['total_price'], 2) ?></strong></td>
                        <td data-label="การชำระเงิน">
                            <?php if (($order['payment_method'] ?? 'cod') === 'cod'): ?>
                                <span style="font-size: 0.85rem; font-weight: 500; color: #4b5563;">💵 เก็บปลายทาง</span>
                            <?php else: ?>
                                <span style="font-size: 0.85rem; font-weight: 500; color: #2563eb; display: block; margin-bottom: 0.3rem;">📱 โอนเงิน (QR)</span>
                                <?php if (!empty($order['payment_slip'])): ?>
                                    <a href="../uploads/slips/<?= htmlspecialchars($order['payment_slip']) ?>" target="_blank" class="admin-btn admin-btn-secondary admin-btn-sm" style="padding: 0.15rem 0.5rem; font-size: 0.7rem; border-radius: var(--border-radius-sm); display: inline-flex; gap: 0.2rem; background-color: var(--secondary-color);">
                                        👁️ ดูสลิป
                                    </a>
                                    <?php if (!empty($order['trans_ref'])): ?>
                                        <span class="admin-badge admin-badge-success" style="font-size: 0.65rem; padding: 0.1rem 0.4rem; vertical-align: middle; margin-left: 0.2rem;" title="เลขอ้างอิง: <?= htmlspecialchars($order['trans_ref']) ?>">🤖 ออโต้</span>
                                    <?php else: ?>
                                        <span class="admin-badge admin-badge-warning" style="font-size: 0.65rem; padding: 0.1rem 0.4rem; vertical-align: middle; margin-left: 0.2rem;">🔎 ตรวจเอง</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td data-label="สถานะ">
                            <?php
                                $status = $order['status'] ?? 'pending';
                                $statusMap = [
                                    'pending'   => ['label' => 'รอตรวจสอบ',     'class' => 'admin-badge-warning'],
                                    'confirmed' => ['label' => 'ยืนยันแล้ว',     'class' => 'admin-badge-info'],
                                    'shipping'  => ['label' => 'กำลังจัดส่ง',     'class' => 'admin-badge-shipping'],
                                    'completed' => ['label' => 'สำเร็จ',          'class' => 'admin-badge-success'],
                                    'cancelled' => ['label' => 'ยกเลิก',          'class' => 'admin-badge-danger'],
                                ];
                                $s = $statusMap[$status] ?? $statusMap['pending'];
                            ?>
                            <span class="admin-badge <?= $s['class'] ?>"><?= $s['label'] ?></span>
                        </td>
                        <td data-label="เวลาสั่งซื้อ" style="font-size: 0.8rem; color: var(--text-muted);"><?= $order['created_at'] ?></td>
                        <td data-label="ใบเสร็จ">
                            <button class="admin-btn admin-btn-secondary admin-btn-sm" onclick='viewReceipt(<?= json_encode($order, JSON_UNESCAPED_UNICODE) ?>)' style="padding: 0.3rem 0.6rem; font-size: 0.78rem; display: inline-flex; gap: 0.25rem; align-items: center; cursor: pointer; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); background-color: var(--secondary-color); font-weight: 500; font-family: inherit;">
                                🧾 ใบเสร็จ
                            </button>
                        </td>
                        <td style="text-align: right;" data-label="จัดการ">
                            <div style="display: inline-flex; align-items: center; gap: 0.35rem; justify-content: flex-end;">
                                <select class="admin-form-control" style="width: auto; min-width: 110px; display: inline-block; font-size: 0.8rem; padding: 0.25rem 0.5rem; height: auto;" onchange="if(confirm('คุณต้องการเปลี่ยนสถานะของคำสั่งซื้อ #<?= $order['id'] ?> เป็น ' + this.options[this.selectedIndex].text + ' ใช่หรือไม่?')) { window.location.href = 'update_order.php?id=<?= $order['id'] ?>&status=' + this.value; } else { this.value = '<?= $status ?>'; }">
                                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>⏳ รอตรวจสอบ</option>
                                    <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>✅ ยืนยันแล้ว</option>
                                    <option value="shipping" <?= $status === 'shipping' ? 'selected' : '' ?>>🚚 กำลังจัดส่ง</option>
                                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>🎉 สำเร็จ</option>
                                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>❌ ยกเลิก</option>
                                </select>
                                <a href="archive_order.php?id=<?= $order['id'] ?>&archive=1" onclick="return confirm('คุณต้องการลบคำสั่งซื้อนี้ออกจากรายการสั่งซื้อหลักใช่หรือไม่? (ข้อมูลจะถูกย้ายไปที่ประวัติการจัดส่ง)')" class="admin-btn" style="padding: 0.3rem 0.5rem; font-size: 0.85rem; border: 1px solid #fecaca; background-color: #fef2f2; color: #dc3545; border-radius: var(--border-radius-sm); line-height: 1; text-decoration: none;" title="ลบออกจากหน้ารายการหลัก">
                                    🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="11" style="text-align:center; color: var(--text-muted); padding: 3rem 0;">ไม่มีข้อมูลคำสั่งซื้อปัจจุบันที่ต้องดำเนินการ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Archived Orders (Delivery History) Card -->
<div class="admin-card" id="archived-orders-card" style="display: none;">
    <div class="admin-card-title" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
        <span>ประวัติการจัดส่งและคำสั่งซื้อที่เสร็จสิ้นทั้งหมด</span>
        <button onclick="window.location.reload();" class="admin-btn admin-btn-secondary admin-btn-sm" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.45rem 0.8rem; font-size: 0.8rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); cursor: pointer; background-color: var(--secondary-color); font-weight: 500; font-family: inherit;">
            🔄 รีเฟรชข้อมูล
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อลูกค้า</th>
                    <th>เบอร์โทร</th>
                    <th>ที่อยู่จัดส่ง</th>
                    <th>รายการสินค้า</th>
                    <th>ยอดรวม</th>
                    <th>การชำระเงิน</th>
                    <th>สถานะ</th>
                    <th>เวลาสั่งซื้อ</th>
                    <th>ใบเสร็จ</th>
                    <th style="text-align: right;">กู้คืนกลับรายการหลัก</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($archived_orders) > 0): ?>
                    <?php foreach($archived_orders as $order): ?>
                    <tr>
                        <td data-label="เลขที่ออเดอร์"><strong>#<?= $order['id'] ?></strong></td>
                        <td data-label="ลูกค้า"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td data-label="เบอร์โทร"><?= htmlspecialchars($order['phone']) ?></td>
                        <td data-label="ที่อยู่จัดส่ง" style="max-width: 180px; font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($order['address'] ?? '-') ?></td>
                        <td data-label="รายการสินค้า">
                            <?php
                                $items = json_decode($order['items'], true);
                                if(is_array($items)) {
                                    foreach($items as $item) {
                                        echo "• " . htmlspecialchars($item['name']) . " x " . $item['qty'] . "<br>";
                                    }
                                } else {
                                    echo "<em style='color: var(--text-muted);'>-</em>";
                                }
                                if(!empty($order['details'])) {
                                    echo "<small style='color: var(--primary-color); display:block; margin-top:0.3rem;'>📝 " . htmlspecialchars($order['details']) . "</small>";
                                }
                            ?>
                        </td>
                        <td data-label="ยอดรวม"><strong>฿<?= number_format($order['total_price'], 2) ?></strong></td>
                        <td data-label="การชำระเงิน">
                            <?php if (($order['payment_method'] ?? 'cod') === 'cod'): ?>
                                <span style="font-size: 0.85rem; font-weight: 500; color: #4b5563;">💵 เก็บปลายทาง</span>
                            <?php else: ?>
                                <span style="font-size: 0.85rem; font-weight: 500; color: #2563eb; display: block; margin-bottom: 0.3rem;">📱 โอนเงิน (QR)</span>
                                <?php if (!empty($order['payment_slip'])): ?>
                                    <a href="../uploads/slips/<?= htmlspecialchars($order['payment_slip']) ?>" target="_blank" class="admin-btn admin-btn-secondary admin-btn-sm" style="padding: 0.15rem 0.5rem; font-size: 0.7rem; border-radius: var(--border-radius-sm); display: inline-flex; gap: 0.2rem; background-color: var(--secondary-color);">
                                        👁️ ดูสลิป
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td data-label="สถานะ">
                            <?php
                                $status = $order['status'] ?? 'pending';
                                $statusMap = [
                                    'pending'   => ['label' => 'รอตรวจสอบ',     'class' => 'admin-badge-warning'],
                                    'confirmed' => ['label' => 'ยืนยันแล้ว',     'class' => 'admin-badge-info'],
                                    'shipping'  => ['label' => 'กำลังจัดส่ง',     'class' => 'admin-badge-shipping'],
                                    'completed' => ['label' => 'สำเร็จ',          'class' => 'admin-badge-success'],
                                    'cancelled' => ['label' => 'ยกเลิก',          'class' => 'admin-badge-danger'],
                                ];
                                $s = $statusMap[$status] ?? $statusMap['pending'];
                            ?>
                            <span class="admin-badge <?= $s['class'] ?>"><?= $s['label'] ?></span>
                        </td>
                        <td data-label="เวลาสั่งซื้อ" style="font-size: 0.8rem; color: var(--text-muted);"><?= $order['created_at'] ?></td>
                        <td data-label="ใบเสร็จ">
                            <button class="admin-btn admin-btn-secondary admin-btn-sm" onclick='viewReceipt(<?= json_encode($order, JSON_UNESCAPED_UNICODE) ?>)' style="padding: 0.3rem 0.6rem; font-size: 0.78rem; display: inline-flex; gap: 0.25rem; align-items: center; cursor: pointer; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); background-color: var(--secondary-color); font-weight: 500; font-family: inherit;">
                                🧾 ใบเสร็จ
                            </button>
                        </td>
                        <td style="text-align: right;" data-label="กู้คืนรายการ">
                            <a href="archive_order.php?id=<?= $order['id'] ?>&archive=0" onclick="return confirm('คุณต้องการดึงคำสั่งซื้อนี้กลับไปหน้าแรกใช่หรือไม่?')" class="admin-btn admin-btn-secondary admin-btn-sm" style="padding: 0.3rem 0.65rem; font-size: 0.78rem; display: inline-flex; gap: 0.25rem; align-items: center; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); background-color: var(--secondary-color); font-weight: 500; font-family: inherit; text-decoration: none;" title="ดึงข้อมูลกลับ">
                                📤 กู้คืนรายการ
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="11" style="text-align:center; color: var(--text-muted); padding: 3rem 0;">ไม่มีข้อมูลประวัติคำสั่งซื้อที่เสร็จสิ้น/ย้ายมาเก็บไว้</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 3. Register Admin Card -->
<div class="admin-card" id="register-admin-card" style="display: none; max-width: 500px; margin: 0 auto 2rem auto; padding: 2.5rem 2rem; border-radius: 12px; border: 1.5px solid var(--border-color); background-color: var(--card-bg);">
    <h3 style="font-family: var(--font-serif); font-size: 1.6rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.6rem;">
        👥 สมัครสมาชิกแอดมินใหม่
    </h3>
    <p style="font-size: 0.82rem; color: var(--text-muted); text-align: center; margin-bottom: 2rem;">เพิ่มบัญชีผู้ดูแลระบบชุดใหม่เพื่อร่วมดูแลร้านหลังบ้าน</p>
    
    <?php if($reg_error): ?>
        <div style="background-color: #fef2f2; color: #c81e1e; border: 1px solid #fbd5d5; padding: 0.75rem 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 500; text-align: center;">
            ⚠️ <?= htmlspecialchars($reg_error) ?>
        </div>
    <?php endif; ?>
    
    <?php if($reg_success): ?>
        <div style="background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; padding: 0.75rem 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.5rem; font-size: 0.85rem; font-weight: 500; text-align: center;">
            ✅ <?= htmlspecialchars($reg_success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="admin-reg-form">
        <input type="hidden" name="register_admin_index" value="1">
        <div class="admin-form-group" style="text-align: left; margin-bottom: 1.2rem;">
            <label style="display:block; margin-bottom: 0.5rem; font-weight:600; font-size: 0.85rem; color: var(--text-main);">ชื่อผู้ใช้งาน (Username) *</label>
            <input type="text" name="reg_username" class="admin-form-control" placeholder="ระบุชื่อแอดมินใหม่" required autocomplete="username" style="width: 100%; box-sizing: border-box; padding: 0.7rem; border-radius: 8px; border: 1.5px solid var(--border-color); background-color: var(--secondary-color); font-family: inherit;">
        </div>
        <div class="admin-form-group" style="text-align: left; margin-bottom: 1.2rem;">
            <label style="display:block; margin-bottom: 0.5rem; font-weight:600; font-size: 0.85rem; color: var(--text-main);">รหัสผ่าน (Password) *</label>
            <input type="password" name="reg_password" class="admin-form-control" placeholder="••••••••" required autocomplete="new-password" style="width: 100%; box-sizing: border-box; padding: 0.7rem; border-radius: 8px; border: 1.5px solid var(--border-color); background-color: var(--secondary-color); font-family: inherit;">
        </div>
        <div class="admin-form-group" style="text-align: left; margin-bottom: 1.8rem;">
            <label style="display:block; margin-bottom: 0.5rem; font-weight:600; font-size: 0.85rem; color: var(--text-main);">ยืนยันรหัสผ่าน *</label>
            <input type="password" name="reg_confirm_password" class="admin-form-control" placeholder="••••••••" required autocomplete="new-password" style="width: 100%; box-sizing: border-box; padding: 0.7rem; border-radius: 8px; border: 1.5px solid var(--border-color); background-color: var(--secondary-color); font-family: inherit;">
        </div>
        <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 0.95rem; font-weight: 600; border-radius: 8px; cursor: pointer; border: none; background-color: var(--primary-color); color: white; transition: background-color 0.2s;">
            ลงทะเบียนแอดมินใหม่ 🎂
        </button>
    </form>
</div>

<script>
function switchTab(tab) {
    const activeCard = document.getElementById('active-orders-card');
    const archivedCard = document.getElementById('archived-orders-card');
    const registerCard = document.getElementById('register-admin-card');
    
    const activeBtn = document.getElementById('tab-active-btn');
    const archivedBtn = document.getElementById('tab-archived-btn');
    const registerBtn = document.getElementById('tab-register-admin-btn');
    
    // Hide all
    activeCard.style.display = 'none';
    archivedCard.style.display = 'none';
    if (registerCard) registerCard.style.display = 'none';
    
    // Reset buttons
    activeBtn.style.color = 'var(--text-light)';
    activeBtn.style.borderBottomColor = 'transparent';
    archivedBtn.style.color = 'var(--text-light)';
    archivedBtn.style.borderBottomColor = 'transparent';
    if (registerBtn) {
        registerBtn.style.color = 'var(--text-light)';
        registerBtn.style.borderBottomColor = 'transparent';
    }
    
    if (tab === 'active') {
        activeCard.style.display = 'block';
        activeBtn.style.color = 'var(--primary-hover)';
        activeBtn.style.borderBottomColor = 'var(--primary-color)';
    } else if (tab === 'archived') {
        archivedCard.style.display = 'block';
        archivedBtn.style.color = 'var(--primary-hover)';
        archivedBtn.style.borderBottomColor = 'var(--primary-color)';
    } else if (tab === 'register-admin') {
        if (registerCard) registerCard.style.display = 'block';
        if (registerBtn) {
            registerBtn.style.color = 'var(--primary-hover)';
            registerBtn.style.borderBottomColor = 'var(--primary-color)';
        }
    }
}

// Automatically switch to the register tab if form was posted with messages
<?php if (isset($_POST['register_admin_index'])): ?>
document.addEventListener("DOMContentLoaded", function() {
    switchTab('register-admin');
});
<?php endif; ?>
</script>

<!-- Receipt Modal -->
<div id="receiptModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: white; width: 100%; max-width: 500px; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden; animation: receiptFadeIn 0.25s ease;">
        <!-- Modal Header -->
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background-color: var(--secondary-color);">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">🧾 ใบเสร็จรับเงิน / ใบสั่งสินค้า</h3>
            <span onclick="closeReceipt()" style="cursor: pointer; font-size: 1.4rem; font-weight: bold; color: var(--text-light); line-height: 1; transition: var(--transition);" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-light)'">&times;</span>
        </div>
        
        <!-- Printable Receipt Content -->
        <div id="receiptPrintArea" style="padding: 1.5rem; overflow-y: auto; max-height: 70vh; font-family: var(--font-thai); color: #2e2e2e; line-height: 1.6;">
            <!-- Brand header -->
            <div style="text-align: center; margin-bottom: 1.25rem;">
                <h2 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; margin: 0;">NightCake<span style="color: var(--primary-color);">.</span></h2>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0.2rem 0 0;">Minimalist Cake & Birthday Event Specialist</p>
            </div>
            
            <hr style="border: 0; border-top: 1px dashed var(--border-color); margin: 1rem 0;">
            
            <!-- Details Metadata -->
            <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 0.4rem; font-size: 0.8rem; margin-bottom: 1rem;">
                <div><span style="color: var(--text-light);">รหัสคำสั่งซื้อ:</span> <strong id="r-order-id">#</strong></div>
                <div style="text-align: right;"><span style="color: var(--text-light);">วันที่สั่ง:</span> <span id="r-order-date"></span></div>
                <div><span style="color: var(--text-light);">การชำระเงิน:</span> <span id="r-order-payment"></span></div>
                <div style="text-align: right;"><span style="color: var(--text-light);">สถานะ:</span> <span id="r-order-status"></span></div>
            </div>
            
            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1rem 0;">
            
            <!-- Customer Delivery Details -->
            <div style="font-size: 0.8rem; margin-bottom: 1.2rem; background: var(--secondary-color); padding: 0.8rem; border-radius: 8px; border: 1px solid var(--border-color);">
                <strong style="display: block; margin-bottom: 0.4rem; font-size: 0.85rem; color: var(--text-main);">📍 ข้อมูลลูกค้าและการจัดส่ง</strong>
                <div style="margin-bottom: 0.2rem;"><span style="color: var(--text-light);">ชื่อลูกค้า:</span> <span id="r-customer-name" style="font-weight: 500;"></span></div>
                <div style="margin-bottom: 0.2rem;"><span style="color: var(--text-light);">เบอร์โทรศัพท์:</span> <span id="r-customer-phone" style="font-weight: 500;"></span></div>
                <div style="margin-top: 0.4rem; line-height: 1.4;"><span style="color: var(--text-light);">ที่อยู่จัดส่ง:</span> <span id="r-customer-address"></span></div>
            </div>
            
            <!-- Items Table -->
            <div style="margin-bottom: 1.25rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                    <thead>
                        <tr style="border-bottom: 1.5px solid var(--text-main); font-weight: 600;">
                            <th style="padding: 0.4rem 0; text-align: left; background: none; width: auto;">รายการเค้ก</th>
                            <th style="padding: 0.4rem 0; text-align: center; background: none; width: 60px;">จำนวน</th>
                            <th style="padding: 0.4rem 0; text-align: right; background: none; width: 90px;">รวม</th>
                        </tr>
                    </thead>
                    <tbody id="r-items-body">
                        <!-- Dynamic items -->
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 1.5px solid var(--text-main); font-weight: bold; font-size: 0.9rem;">
                            <td colspan="2" style="padding: 0.6rem 0 0; text-align: left; color: var(--text-main);">ยอดชำระทั้งสิ้น (Total)</td>
                            <td id="r-total-price" style="padding: 0.6rem 0 0; text-align: right; color: var(--text-main); font-size: 0.95rem;">฿0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Additional instructions / notes -->
            <div id="r-details-container" style="font-size: 0.78rem; background: #fffbeb; border: 1px solid #fde68a; padding: 0.6rem 0.8rem; border-radius: 8px; color: #b45309; display: none; margin-top: 0.8rem;">
                <strong>📝 รายละเอียดเขียนหน้าเค้กเพิ่มเติม:</strong>
                <p id="r-details-text" style="margin: 0.2rem 0 0; line-height: 1.4;"></p>
            </div>
            
            <!-- Print stamp footer -->
            <div style="text-align: center; margin-top: 2rem; font-size: 0.72rem; color: var(--text-light); line-height: 1.4;">
                <p style="margin: 0; font-weight: 500;">ขอบคุณที่ร่วมแบ่งปันวันสำคัญกับเรานะคะ 💖</p>
                <p style="margin: 0.1rem 0 0;">NightCake - อร่อย นุ่ม หวานน้อย ครีมสดแท้</p>
            </div>
        </div>
        
        <!-- Modal Footer buttons -->
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.6rem; justify-content: flex-end; background-color: var(--secondary-color);">
            <button onclick="printReceipt()" class="admin-btn admin-btn-primary" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.5rem 1rem; font-size: 0.8rem; border-radius: var(--border-radius-sm); cursor: pointer; font-family: inherit;">
                🖨️ พิมพ์ใบเสร็จ
            </button>
            <button onclick="closeReceipt()" class="admin-btn admin-btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.8rem; border-radius: var(--border-radius-sm); cursor: pointer; border: 1px solid var(--border-color); background-color: white; font-family: inherit;">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<style>
@keyframes receiptFadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

@media print {
    /* Hide all wrapper page elements during print */
    .admin-sidebar,
    .admin-header,
    .stats-grid,
    .tabs-navigation,
    .admin-card,
    #receiptModal > div > div:first-child,
    #receiptModal > div > div:last-child {
        display: none !important;
    }
    
    /* Ensure outer page layout and containers are printable and unclipped */
    body, html, .admin-wrapper, .admin-main, .admin-body {
        background: white !important;
        color: black !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
        height: auto !important;
    }
    
    /* Display modal container as regular block in print */
    #receiptModal {
        display: block !important;
        position: static !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
        z-index: auto !important;
    }
    
    #receiptModal > div {
        max-width: 100% !important;
        width: 100% !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    #receiptPrintArea {
        max-height: none !important;
        overflow: visible !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>

<script>
function viewReceipt(order) {
    document.getElementById('r-order-id').innerText = '#' + order.id;
    document.getElementById('r-order-date').innerText = order.created_at;
    document.getElementById('r-customer-name').innerText = order.customer_name;
    document.getElementById('r-customer-phone').innerText = order.phone;
    document.getElementById('r-customer-address').innerText = order.address || '-';
    
    // Payment method mapping
    let paymentText = order.payment_method === 'cod' ? '💵 เก็บปลายทาง (COD)' : '📱 โอนเงินผ่านระบบ PromptPay';
    document.getElementById('r-order-payment').innerText = paymentText;
    
    // Status mapping
    let statusText = 'รอตรวจสอบ';
    let statusColor = '#f59e0b';
    if (order.status === 'confirmed') { statusText = 'ยืนยันสั่งซื้อแล้ว'; statusColor = '#2563eb'; }
    else if (order.status === 'shipping') { statusText = 'กำลังนำส่งสินค้า'; statusColor = '#06b6d4'; }
    else if (order.status === 'completed') { statusText = 'จัดส่งสำเร็จแล้ว'; statusColor = '#10b981'; }
    else if (order.status === 'cancelled') { statusText = 'ยกเลิกคำสั่งซื้อ'; statusColor = '#dc2626'; }
    
    const statusSpan = document.getElementById('r-order-status');
    statusSpan.innerText = statusText;
    statusSpan.style.cssText = `background: ${statusColor}15; color: ${statusColor}; padding: 0.2rem 0.5rem; border-radius: 50px; font-size: 0.7rem; font-weight: 600; border: 1px solid ${statusColor}30;`;

    // Populate order items
    const itemsBody = document.getElementById('r-items-body');
    itemsBody.innerHTML = '';
    
    let items = [];
    try {
        items = JSON.parse(order.items);
    } catch(e) {
        items = [];
    }
    
    if (Array.isArray(items)) {
        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--border-color)';
            tr.innerHTML = `
                <td style="padding: 0.5rem 0; text-align: left;">${item.name}</td>
                <td style="padding: 0.5rem 0; text-align: center;">${item.qty}</td>
                <td style="padding: 0.5rem 0; text-align: right;">฿${(item.price * item.qty).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            `;
            itemsBody.appendChild(tr);
        });
    }
    
    document.getElementById('r-total-price').innerText = '฿' + parseFloat(order.total_price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Handle customer notes
    const detailsContainer = document.getElementById('r-details-container');
    if (order.details && order.details.trim() !== '') {
        document.getElementById('r-details-text').innerText = order.details;
        detailsContainer.style.display = 'block';
    } else {
        detailsContainer.style.display = 'none';
    }
    
    // Open Modal
    document.getElementById('receiptModal').style.display = 'flex';
}

function closeReceipt() {
    document.getElementById('receiptModal').style.display = 'none';
}

function printReceipt() {
    window.print();
}
</script>

<?php
include_once 'footer.php';
?>
