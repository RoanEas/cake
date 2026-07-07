<?php
include_once 'header.php';

$success_msg = '';
$error_msg = '';

// Save settings logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $bot_token = trim($_POST['line_bot_token'] ?? '');
    $user_id = trim($_POST['line_user_id'] ?? '');
    $pp_id = trim($_POST['promptpay_id'] ?? '');
    $pp_name = trim($_POST['promptpay_name'] ?? '');
    $verify_mode = trim($_POST['slip_verify_mode'] ?? 'manual');
    $api_url = trim($_POST['slip_api_url'] ?? '');
    $api_key = trim($_POST['slip_api_key'] ?? '');
    $api_header = trim($_POST['slip_api_header'] ?? '');
    $recv_kw = trim($_POST['receiver_name_keyword'] ?? '');
    $cb_api_key = trim($_POST['chatbot_api_key'] ?? '');
    
    try {
        $settings_to_save = [
            'line_bot_token' => $bot_token,
            'line_user_id' => $user_id,
            'promptpay_id' => $pp_id,
            'promptpay_name' => $pp_name,
            'slip_verify_mode' => $verify_mode,
            'slip_api_url' => $api_url,
            'slip_api_key' => $api_key,
            'slip_api_header' => $api_header,
            'receiver_name_keyword' => $recv_kw,
            'chatbot_api_key' => $cb_api_key
        ];
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach ($settings_to_save as $key => $val) {
            $stmt->execute([$key, $val, $val]);
        }
        
        $line_bot_token = $bot_token;
        $line_user_id = $user_id;
        $promptpay_id = $pp_id;
        $promptpay_name = $pp_name;
        $slip_verify_mode = $verify_mode;
        $slip_api_url = $api_url;
        $slip_api_key = $api_key;
        $slip_api_header = $api_header;
        $receiver_name_keyword = $recv_kw;
        $chatbot_api_key = $cb_api_key;
        
        $success_msg = 'บันทึกการตั้งค่าทั้งหมดเรียบร้อยแล้ว!';
    } catch (PDOException $e) {
        $error_msg = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
    }
}

// Send test message logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_line'])) {
    if (!empty($line_bot_token) && !empty($line_user_id)) {
        $message = "🔔 ทดสอบระบบการแจ้งเตือนจาก NightCake\n";
        $message .= "---------------------------\n";
        $message .= "LINE Messaging API ของคุณเปิดใช้งานและเชื่อมต่อถูกต้องแล้ว! 🎉\n";
        $message .= "เวลาทดสอบ: " . date('Y-m-d H:i:s') . "\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/bot/message/push");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'to' => $line_user_id,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message
                ]
            ]
        ]));
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $line_bot_token
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $res_json = json_decode($result, true);
        if ($http_code == 200) {
            $success_msg = 'ส่งข้อความทดสอบไปยัง LINE Messaging API เรียบร้อยแล้ว! กรุณาตรวจสอบแอป LINE ของคุณ';
        } else {
            $error_msg = 'ไม่สามารถส่งข้อความได้: ' . ($res_json['message'] ?? 'รหัสผ่าน (Token) หรือ LINE User ID ไม่ถูกต้อง หรือไม่มีการเชื่อมต่อกับ Bot (HTTP ' . $http_code . ')');
        }
    } else {
        $error_msg = 'กรุณากรอกทั้ง LINE Bot Token และ LINE User ID ก่อนทดสอบการแจ้งเตือน';
    }
}
?>

<div class="admin-card" style="max-width: 700px; margin: 2rem auto;">
    <div class="admin-card-title">
        <span>⚙️ ตั้งค่าระบบการชำระเงินและการแจ้งเตือน</span>
    </div>
    
    <?php if(!empty($success_msg)): ?>
        <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #10b981;">
            ✅ <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>
    
    <?php if(!empty($error_msg)): ?>
        <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: var(--border-radius-sm); margin-bottom: 1.5rem; font-size: 0.9rem; border: 1px solid #ef4444;">
            ❌ <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="settings.php" class="admin-form">
        <!-- Section 1: LINE Messaging API -->
        <h3 style="font-size: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1.2rem; color: var(--primary-color);">📢 ระบบแจ้งเตือนผ่านแอป LINE (LINE Messaging API / LINE Bot)</h3>
        
        <div style="background-color: #fef3c7; border: 1px solid #f59e0b; color: #b45309; padding: 0.8rem; border-radius: 8px; font-size: 0.8rem; margin-bottom: 1.2rem; line-height: 1.4;">
            ⚠️ <strong>หมายเหตุสำคัญ</strong>: LINE ได้ยกเลิกบริการ <em>LINE Notify</em> อย่างเป็นทางการแล้ว (ตั้งแต่ 31 มี.ค. 2025) ระบบจึงได้อัปเกรดมาเชื่อมต่อผ่าน <strong>LINE Messaging API (LINE Bot)</strong> ซึ่งทำหน้าที่ส่งข้อความฟรีทดแทนได้อย่างมีประสิทธิภาพ 100% ครับ!
        </div>

        <div class="admin-form-group">
            <label for="line_bot_token">LINE Bot Channel Access Token</label>
            <input type="text" id="line_bot_token" name="line_bot_token" class="admin-form-control" 
                   value="<?= htmlspecialchars($line_bot_token) ?>" 
                   placeholder="กรอก Channel Access Token (Long-lived) ของบอท" style="font-family: monospace;">
        </div>

        <div class="admin-form-group">
            <label for="line_user_id">LINE User ID ของคุณ (รหัสผู้รับแจ้งเตือน)</label>
            <input type="text" id="line_user_id" name="line_user_id" class="admin-form-control" 
                   value="<?= htmlspecialchars($line_user_id) ?>" 
                   placeholder="เช่น U123456789abcdef0123456789abcdef" style="font-family: monospace;">
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem; line-height: 1.5;">
                * สมัครสร้างบอทฟรีได้ที่ <a href="https://developers.line.biz/" target="_blank" style="color: var(--primary-color); text-decoration: underline;">LINE Developers Console</a><br>
                * รหัส User ID สามารถดูได้จากแท็บบัญชีผู้พัฒนาของคุณใน LINE Developers หรือใช้บอทช่วยอ่านค่า User ID ของคุณ
            </small>
        </div>

        <!-- Section 2: PromptPay Settings -->
        <h3 style="font-size: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1.2rem; color: var(--primary-color);">💳 ตั้งค่าบัญชีรับเงิน (PromptPay QR Code)</h3>
        <div class="admin-form-group">
            <label for="promptpay_id">หมายเลขพร้อมเพย์ (เบอร์มือถือ หรือ เลขประจำตัวเสียภาษี)</label>
            <input type="text" id="promptpay_id" name="promptpay_id" class="admin-form-control" 
                   value="<?= htmlspecialchars($promptpay_id) ?>" 
                   placeholder="เช่น 0812345678 หรือ 1234567890123" style="font-family: monospace;">
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem; line-height: 1.5;">
                * จำเป็นสำหรับการสร้าง QR Code พร้อมเพย์ตามยอดชำระจริงในหน้าชำระเงินของลูกค้า
            </small>
        </div>
        <div class="admin-form-group">
            <label for="promptpay_name">ชื่อบัญชีรับเงิน (แสดงผลบนหน้าจอชำระเงิน)</label>
            <input type="text" id="promptpay_name" name="promptpay_name" class="admin-form-control" 
                   value="<?= htmlspecialchars($promptpay_name) ?>" 
                   placeholder="เช่น บจก. ไนท์เค้ก เบเกอรี่ หรือ นายสมชาย ดีใจ">
        </div>

        <!-- Section 3: Payment Slip Verification -->
        <h3 style="font-size: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1.2rem; color: var(--primary-color);">🤖 ระบบตรวจสอบสลิปโอนเงินอัตโนมัติ (Slip Verification API)</h3>
        
        <div class="admin-form-group">
            <label for="slip_verify_mode">เลือกผู้ให้บริการตรวจสอบสลิป</label>
            <select id="slip_verify_mode" name="slip_verify_mode" class="admin-form-control" onchange="toggleSlipMode(this.value)">
                <option value="manual" <?= $slip_verify_mode === 'manual' ? 'selected' : '' ?>>✍️ ตรวจสอบด้วยมือ (ปิดระบบตรวจสลิปอัตโนมัติ)</option>
                <option value="slipok" <?= $slip_verify_mode === 'slipok' ? 'selected' : '' ?>>⚡ SlipOK (ตรวจสลิปด้วยภาพ)</option>
                <option value="slip2go" <?= $slip_verify_mode === 'slip2go' ? 'selected' : '' ?>>🚀 Slip2Go (ตรวจสลิปผ่าน QR Code หรือรูปภาพ)</option>
                <option value="custom" <?= $slip_verify_mode === 'custom' ? 'selected' : '' ?>>⚙️ Custom (ระบุผู้ให้บริการเอง)</option>
            </select>
        </div>

        <div id="slip-settings-panel" style="<?= $slip_verify_mode === 'manual' ? 'display: none;' : '' ?>">
            <div class="admin-form-group">
                <label for="slip_api_url">API Endpoint URL</label>
                <input type="text" id="slip_api_url" name="slip_api_url" class="admin-form-control" 
                       value="<?= htmlspecialchars($slip_api_url) ?>" 
                       placeholder="เช่น https://connect.slip2go.com/api/verify-slip/qr-code/info" style="font-family: monospace;">
            </div>

            <div class="admin-form-group">
                <label for="slip_api_header">API Key Header Name (ชื่อฟิลด์ในส่วนหัว)</label>
                <input type="text" id="slip_api_header" name="slip_api_header" class="admin-form-control" 
                       value="<?= htmlspecialchars($slip_api_header) ?>" 
                       placeholder="เช่น x-lib-key สำหรับ SlipOK, หรือ Authorization สำหรับ Slip2Go" style="font-family: monospace;">
            </div>

            <div class="admin-form-group">
                <label for="slip_api_key">API Key / Secret Token (รหัสคีย์ผู้ใช้งาน)</label>
                <input type="text" id="slip_api_key" name="slip_api_key" class="admin-form-control" 
                       value="<?= htmlspecialchars($slip_api_key) ?>" 
                       placeholder="กรอก API Key / Token ที่ได้รับจากผู้ให้บริการ" style="font-family: monospace;">
            </div>
        </div>

        <div class="admin-form-group">
            <label for="receiver_name_keyword">คีย์เวิร์ดตรวจสอบชื่อบัญชีผู้รับโอนบนสลิป (Receiver Name Keyword)</label>
            <input type="text" id="receiver_name_keyword" name="receiver_name_keyword" class="admin-form-control" 
                   value="<?= htmlspecialchars($receiver_name_keyword) ?>" 
                   placeholder="เช่น NIGHTCAKE หรือ SOMCHAI (ภาษาอังกฤษตัวพิมพ์ใหญ่ตามที่แสดงบนสลิป)">
            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem; line-height: 1.5;">
                * ใช้เช็กชื่อผู้รับในสลิปจริง ป้องกันการนำสลิปที่โอนไปบัญชีอื่นมาแนบในระบบ
            </small>
        </div>

        <script>
        function toggleSlipMode(val) {
            const panel = document.getElementById('slip-settings-panel');
            const urlInput = document.getElementById('slip_api_url');
            const headerInput = document.getElementById('slip_api_header');
            
            if (val === 'manual') {
                panel.style.display = 'none';
            } else {
                panel.style.display = 'block';
                if (val === 'slipok') {
                    urlInput.value = 'https://api.slipok.com/api/line/apikey';
                    headerInput.value = 'x-lib-key';
                } else if (val === 'slip2go') {
                    urlInput.value = 'https://connect.slip2go.com/api/verify-slip/qr-code/info';
                    headerInput.value = 'Authorization';
                }
            }
        }
        </script>
        
        <!-- Section 4: Chatbot Settings -->
        <div style="border-top: 1px solid var(--border-color); margin-top: 2rem; padding-top: 2rem;">
            <h3 style="margin-bottom: 1.2rem; font-size: 1.1rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">🤖 ตั้งค่าปัญญาประดิษฐ์บอท (Chatbot AI)</h3>
            <div class="admin-form-group">
                <label for="chatbot_api_key">OpenRouter API Key (คีย์เข้าถึง AI ของน้องไนท์บอท)</label>
                <input type="password" id="chatbot_api_key" name="chatbot_api_key" class="admin-form-control" 
                       value="<?= htmlspecialchars($chatbot_api_key ?? '') ?>" 
                       placeholder="กรอก OpenRouter API Key (sk-or-...)" style="font-family: monospace;">
                <small style="color: var(--text-muted); display: block; margin-top: 0.5rem; line-height: 1.5;">
                    * น้องไนท์บอทเชื่อมต่อโมเดลผ่าน OpenRouter.ai หากปล่อยว่างไว้ ระบบจะดึงคีย์เริ่มต้นมาทำงานโดยอัตโนมัติครับ
                </small>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 2.5rem;">
            <button type="submit" name="save_settings" class="admin-btn admin-btn-primary">
                💾 บันทึกการตั้งค่าทั้งหมด
            </button>
            <button type="submit" name="test_line" class="admin-btn admin-btn-secondary" onclick="return confirm('ยืนยันส่งข้อความทดสอบไปยัง LINE?')">
                🔔 ทดสอบส่ง LINE
            </button>
        </div>
    </form>
</div>

<?php
include_once 'footer.php';
?>
