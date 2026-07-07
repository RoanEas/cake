<?php
require_once 'config.php';

// Redirect to login if user session is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php&require_login=1");
    exit;
}

// Fetch saved addresses from database
$user_id = $_SESSION['user_id'];
$saved_addresses = [];
try {
    $stmt_user = $pdo->prepare("SELECT address FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    if (!empty($user_data['address'])) {
        $saved_addresses = json_decode($user_data['address'], true);
        if (!is_array($saved_addresses)) {
            $saved_addresses = [];
        }
    }
} catch (PDOException $e) {
    // Ignore DB error
}

$initial_address = '';
if (!empty($saved_addresses)) {
    foreach ($saved_addresses as $addr) {
        if ($addr['is_default']) {
            $initial_address = $addr['text'];
        }
    }
    if (empty($initial_address)) {
        $initial_address = $saved_addresses[0]['text'];
    }
} else {
    $initial_address = $_SESSION['user_address'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งซื้อสินค้า | NightCake</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-layout {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 3rem;
            align-items: start;
        }
        .order-summary-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius-md);
            padding: 2rem;
            box-shadow: var(--shadow-subtle);
            border: 1px solid var(--border-color);
        }
        .checkout-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        .checkout-item:last-child {
            border-bottom: none;
        }
        .checkout-item-details {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .checkout-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius-sm);
        }
        .checkout-item-title {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .checkout-item-price {
            font-family: var(--font-serif);
            font-weight: 700;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.2rem;
        }
        .checkout-item-total {
            font-family: var(--font-serif);
            font-weight: 700;
            font-size: 1rem;
        }
        .checkout-total-row {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 1.2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--border-color);
            margin-top: 1rem;
        }
        @media (max-width: 992px) {
            .checkout-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">NightCake<span>.</span></a>
    <nav>
        <a href="index.php">กลับหน้าแรก</a>
    </nav>
</header>

<main class="container">
    <?php if(isset($_GET['order']) && $_GET['order'] == 'invalid_slip'): ?>
        <div class="alert alert-danger" style="margin-bottom: 2rem; background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--border-radius-sm); border: 1px solid #ef4444; font-size: 0.9rem;">
            ⚠️ <strong>การตรวจสอบสลิปผิดพลาด:</strong> <?= htmlspecialchars($_GET['message'] ?? 'ข้อมูลไม่ถูกต้อง') ?>
        </div>
    <?php endif; ?>
    
    <div class="checkout-layout">
        <!-- Left: Checkout Form -->
        <div class="card" style="margin: 0; width: 100%; max-width: 100%;">
            <h2 class="card-title" style="text-align: left;">ข้อมูลการจัดส่งสินค้า</h2>
            <form action="process_order.php" method="POST" id="checkout-form" enctype="multipart/form-data">
                <!-- Hidden inputs for cart data and total price -->
                <input type="hidden" name="cart_data" id="cart-data-input" required>
                <input type="hidden" name="total_price" id="total-price-input" required>
                <input type="hidden" name="qr_code_data" id="qr-code-data-input">

                <div class="form-group">
                    <label>ชื่อ-นามสกุล ผู้รับ</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="ระบุชื่อผู้รับเค้ก" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>เบอร์โทรศัพท์ติดต่อ</label>
                    <input type="tel" name="phone" class="form-control" placeholder="ระบุเบอร์โทรศัพท์" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>" required>
                </div>
                
                <?php if(!empty($saved_addresses)): ?>
                    <div class="form-group">
                        <label>เลือกที่อยู่จัดส่งที่บันทึกไว้</label>
                        <select class="form-control" onchange="useSavedAddress(this)" style="margin-bottom: 0.5rem; background-color: var(--secondary-color); cursor: pointer; border-radius: 8px;">
                            <?php foreach($saved_addresses as $addr): ?>
                                <option value="<?= htmlspecialchars($addr['text']) ?>" <?= $addr['is_default'] ? 'selected' : '' ?>>
                                    🏡 <?= htmlspecialchars($addr['name']) ?> (<?= htmlspecialchars(mb_strimwidth($addr['text'], 0, 45, '...')) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>ที่อยู่จัดส่ง / สถานที่จัดงาน</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="ระบุที่อยู่ บ้านเลขที่ ซอย ถนน แขวง เขต อย่างละเอียด" required><?= htmlspecialchars($initial_address) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>รายละเอียดเพิ่มเติม (เช่น ข้อความเขียนหน้าเค้ก / วันเกิดของใคร / เวลาส่งที่ต้องการ)</label>
                    <textarea name="details" class="form-control" rows="3" placeholder="ระบุข้อความที่ต้องการเขียนบนเค้ก เช่น 'Happy Birthday MOM' หรือรายละเอียดอื่นๆ"></textarea>
                </div>

                <!-- Payment Method Selector -->
                <div class="form-group" style="margin-top: 1.5rem;">
                    <label style="font-weight: 600; display: block; margin-bottom: 0.8rem;">เลือกวิธีการชำระเงิน</label>
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.8rem 1.2rem; border: 1px solid var(--primary-color); border-radius: var(--border-radius-sm); flex: 1; background-color: rgba(247, 168, 184, 0.05); transition: all 0.3s;" id="payment-cod-label">
                            <input type="radio" name="payment_method" value="cod" checked style="accent-color: var(--primary-color);">
                            <span style="font-size: 0.9rem; font-weight: 500;">💵 เก็บเงินปลายทาง (COD)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.8rem 1.2rem; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); flex: 1; background-color: var(--secondary-color); transition: all 0.3s;" id="payment-qr-label">
                            <input type="radio" name="payment_method" value="qrcode" style="accent-color: var(--primary-color);">
                            <span style="font-size: 0.9rem; font-weight: 500;">📱 โอนเงินผ่าน QR Code</span>
                        </label>
                    </div>
                </div>

                <!-- Dynamic QR Code Payment Panel -->
                <div id="qrcode-payment-panel" style="display: none; background-color: #fff8f9; border: 1px dashed var(--primary-color); border-radius: var(--border-radius-md); padding: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
                    <h4 style="color: var(--primary-hover); margin-bottom: 0.5rem; font-weight: 600;">สแกน QR Code เพื่อชำระเงิน</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                        ยอดเงินโอน: <strong id="qr-amount-text" style="color: var(--primary-color); font-size: 1.15rem;">฿0.00</strong>
                        <?php if (!empty($promptpay_name)): ?>
                            <br>ชื่อบัญชี: <strong style="color: var(--text-main);"><?= htmlspecialchars($promptpay_name) ?></strong>
                        <?php endif; ?>
                    </p>
                    
                    <div style="margin: 0 auto 1.2rem; width: 220px; height: 220px; background: #fff; padding: 10px; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-subtle);">
                        <img id="promptpay-qr-img" src="" alt="PromptPay QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <p style="font-size: 0.75rem; color: var(--text-light); margin-bottom: 1.5rem; line-height: 1.5;">
                        * ใช้แอปธนาคารสแกนจ่าย ระบบจะตรวจสอบข้อมูลสลิปที่แนบให้ทันที<br>
                        * หากโอนเงินยอดไม่ตรงหรือใช้สลิปเก่า ระบบจะไม่ผ่านการสั่งซื้อ
                    </p>
                    
                    <div class="form-group" style="text-align: left; margin-bottom: 0;">
                        <label style="font-weight: 600; display: block; margin-bottom: 0.5rem;">แนบรูปหลักฐานการโอนเงิน (สลิป) <span style="color: var(--danger);">*</span></label>
                        <input type="file" name="payment_slip" id="payment-slip-input" class="form-control" accept="image/*" style="padding: 0.5rem;">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.3rem;">รองรับไฟล์สลิปธนาคารทุกประเภท (.png, .jpg, .jpeg)</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-block" style="margin-top: 1rem;">ยืนยันคำสั่งซื้อ</button>
            </form>
        </div>

        <!-- Right: Order Summary -->
        <div class="order-summary-card">
            <h3 style="font-weight: 600; font-size: 1.2rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">สรุปคำสั่งซื้อ</h3>
            
            <div id="checkout-summary-container">
                <!-- Loaded dynamically by JS -->
            </div>
            
            <div class="checkout-total-row">
                <span>ราคารวมทั้งหมด:</span>
                <span id="checkout-grand-total" style="color: var(--primary-color);">฿0.00</span>
            </div>
        </div>
    </div>
    
    <!-- Sweet Loading Overlay for Slip Verification -->
    <div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); z-index: 10000; align-items: center; justify-content: center; flex-direction: column; font-family: inherit;">
        <div class="loading-spinner" style="width: 50px; height: 50px; border: 5px solid var(--secondary-color); border-top-color: var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1.5rem;"></div>
        <h3 style="color: var(--primary-hover); font-weight: 600; margin-bottom: 0.5rem; font-size: 1.1rem;">🍰 กำลังตรวจสอบสลิปโอนเงิน...</h3>
        <p style="color: var(--text-light); font-size: 0.85rem; padding: 0 1rem; text-align: center;">ระบบกำลังตรวจสอบความถูกต้องกับธนาคารโดยตรง กรุณารอสักครู่เพื่อบันทึกคำสั่งซื้อนะคะ</p>
    </div>

    <style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    </style>
</main>

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById('checkout-summary-container');
    const totalElem = document.getElementById('checkout-grand-total');
    const cartInput = document.getElementById('cart-data-input');
    const priceInput = document.getElementById('total-price-input');
    const form = document.getElementById('checkout-form');

    // UI Elements for Payment Method Toggle
    const codRadio = document.querySelector('input[name="payment_method"][value="cod"]');
    const qrRadio = document.querySelector('input[name="payment_method"][value="qrcode"]');
    const qrPanel = document.getElementById('qrcode-payment-panel');
    const qrImg = document.getElementById('promptpay-qr-img');
    const qrAmountText = document.getElementById('qr-amount-text');
    const slipInput = document.getElementById('payment-slip-input');
    const codLabel = document.getElementById('payment-cod-label');
    const qrLabel = document.getElementById('payment-qr-label');

    let cart = JSON.parse(localStorage.getItem('cake_cart')) || [];

    if (cart.length === 0) {
        alert("ไม่มีสินค้าในตะกร้าสินค้า กรุณาเลือกซื้อสินค้าก่อนครับ");
        window.location.href = "index.php";
        return;
    }

    // Render summary list
    let html = '';
    let grandTotal = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.qty;
        grandTotal += itemTotal;

        html += `
            <div class="checkout-item">
                <div class="checkout-item-details">
                    <img src="${item.img}" class="checkout-item-img" alt="${item.name}">
                    <div>
                        <div class="checkout-item-title">${item.name}</div>
                        <div class="checkout-item-price">฿${parseFloat(item.price).toLocaleString('th-TH', {minimumFractionDigits: 2})} x ${item.qty}</div>
                    </div>
                </div>
                <div class="checkout-item-total">฿${itemTotal.toLocaleString('th-TH', {minimumFractionDigits: 2})}</div>
            </div>
        `;
    });

    container.innerHTML = html;
    totalElem.innerText = '฿' + grandTotal.toLocaleString('th-TH', {minimumFractionDigits: 2});

    // Populate hidden input values
    cartInput.value = JSON.stringify(cart);
    priceInput.value = grandTotal;

    function togglePaymentPanel() {
        if (qrRadio.checked) {
            qrPanel.style.display = 'block';
            slipInput.required = true;
            qrImg.src = 'get_qr.php?amount=' + grandTotal;
            qrAmountText.innerText = '฿' + grandTotal.toLocaleString('th-TH', {minimumFractionDigits: 2});
            
            qrLabel.style.borderColor = 'var(--primary-color)';
            qrLabel.style.backgroundColor = 'rgba(247, 168, 184, 0.05)';
            codLabel.style.borderColor = 'var(--border-color)';
            codLabel.style.backgroundColor = 'var(--secondary-color)';
        } else {
            qrPanel.style.display = 'none';
            slipInput.required = false;
            slipInput.value = ''; // Reset file input
            
            codLabel.style.borderColor = 'var(--primary-color)';
            codLabel.style.backgroundColor = 'rgba(247, 168, 184, 0.05)';
            qrLabel.style.borderColor = 'var(--border-color)';
            qrLabel.style.backgroundColor = 'var(--secondary-color)';
        }
    }

    // Client-side QR Code Reader with jsQR (with dimension scaling optimization)
    slipInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        const qrInput = document.getElementById('qr-code-data-input');
        qrInput.value = ''; // Reset previous
        
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");
                
                // Scale dimensions down to a maximum of 1000px to optimize scanning speed and browser memory usage
                const maxDim = 1000;
                let w = img.width;
                let h = img.height;
                if (w > maxDim || h > maxDim) {
                    if (w > h) {
                        h = Math.round((h * maxDim) / w);
                        w = maxDim;
                    } else {
                        w = Math.round((w * maxDim) / h);
                        h = maxDim;
                    }
                }
                
                canvas.width = w;
                canvas.height = h;
                ctx.drawImage(img, 0, 0, w, h);
                
                try {
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    if (code) {
                        qrInput.value = code.data;
                        console.log("Successfully scanned slip QR Code payload: ", code.data);
                    } else {
                        console.log("No QR Code found in the uploaded image. Backend will use image-upload fallback.");
                    }
                } catch (err) {
                    console.error("Error reading image data: ", err);
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    codRadio.addEventListener('change', togglePaymentPanel);
    qrRadio.addEventListener('change', togglePaymentPanel);

    // Initial check on load
    togglePaymentPanel();

    // Attach form submit handler to verify inputs and show loading state
    form.addEventListener('submit', (e) => {
        // Double check inputs are set
        cartInput.value = localStorage.getItem('cake_cart');
        
        // Show loading spinner if it is PromptPay payment method
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        if (selectedMethod === 'qrcode') {
            document.getElementById('loading-overlay').style.display = 'flex';
        }
    });
});

function useSavedAddress(select) {
    const textarea = document.querySelector('textarea[name="address"]');
    if (textarea) {
        textarea.value = select.value;
    }
}
</script>
</body>
</html>
