<?php
include_once 'header.php';

$success_msg = '';
$error_msg = '';

// Handle save settings request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_theme'])) {
    $bg_color = trim($_POST['bg_color'] ?? '#fdfdfd');
    $primary_color = trim($_POST['primary_color'] ?? '#f7a8b8');
    $text_color = trim($_POST['text_color'] ?? '#2e2e2e');
    $muted_text_color = trim($_POST['muted_text_color'] ?? '#757575');
    
    $hero_h2 = trim($_POST['hero_h2'] ?? '');
    $hero_h1 = trim($_POST['hero_h1'] ?? '');
    $hero_p = trim($_POST['hero_p'] ?? '');
    
    // Read local slides state
    if (isset($_POST['theme_slides'])) {
        $decoded = json_decode($_POST['theme_slides'], true);
        if (is_array($decoded)) {
            $theme_hero_slides = $decoded;
        }
    }
    
    // Validate color codes
    if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $bg_color)) { $bg_color = '#fdfdfd'; }
    if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $primary_color)) { $primary_color = '#f7a8b8'; }
    if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $text_color)) { $text_color = '#2e2e2e'; }
    if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $muted_text_color)) { $muted_text_color = '#757575'; }

    try {
        $pdo->beginTransaction();

        // 1. Save color settings
        $colors_to_save = [
            'theme_bg_color' => $bg_color,
            'theme_primary_color' => $primary_color,
            'theme_text_color' => $text_color,
            'theme_muted_text_color' => $muted_text_color
        ];
        $stmt_col = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach ($colors_to_save as $key => $val) {
            $stmt_col->execute([$key, $val, $val]);
        }
        $theme_bg_color = $bg_color;
        $theme_primary_color = $primary_color;
        $theme_text_color = $text_color;
        $theme_muted_text_color = $muted_text_color;

        // 2. Save text and icon settings
        $texts_to_save = [
            'theme_hero_h2' => $hero_h2,
            'theme_hero_h1' => $hero_h1,
            'theme_hero_p' => $hero_p,
            'theme_f1_icon' => trim($_POST['f1_icon'] ?? '🎂'),
            'theme_f1_title' => trim($_POST['f1_title'] ?? 'สั่งล่วงหน้าสดใหม่'),
            'theme_f1_desc' => trim($_POST['f1_desc'] ?? 'สั่งจองล่วงหน้า เพื่อรับเค้กทำสดใหม่วันต่อวัน ตกแต่งด้วยความใส่ใจประณีตสำหรับวันสำคัญของคุณ'),
            'theme_f2_icon' => trim($_POST['f2_icon'] ?? '🍓'),
            'theme_f2_title' => trim($_POST['f2_title'] ?? 'วัตถุดิบพรีเมียม'),
            'theme_f2_desc' => trim($_POST['f2_desc'] ?? 'เนื้อเค้กเนียนนุ่ม ครีมสดแท้นำเข้า หวานน้อย กลมกล่อม ละมุนลิ้น ถูกปากทุกคนในครอบครัว'),
            'theme_f3_icon' => trim($_POST['f3_icon'] ?? '🚚'),
            'theme_f3_title' => trim($_POST['f3_title'] ?? 'บริการส่งเดลิเวอรี่'),
            'theme_f3_desc' => trim($_POST['f3_desc'] ?? 'บริการจัดส่งถึงบ้านทั่วกรุงเทพฯ และปริมณฑล ด้วยยานพาหนะควบคุมอุณหภูมิ เพื่อรักษาคุณภาพความสดใหม่'),
            'theme_menu_title' => trim($_POST['menu_title'] ?? 'เมนูไนท์เค้กยอดนิยม'),
            'theme_menu_subtitle' => trim($_POST['menu_subtitle'] ?? '🎂 ฉลองวันพิเศษของคุณด้วยไนท์เค้กมินิมอล บริการเขียนหน้าเค้กฟรี! สั่งล่วงหน้าและจัดส่งเดลิเวอรี่ทั่วกรุงเทพฯ'),
            'theme_announcement_icon' => trim($_POST['ann_icon'] ?? '🎂'),
            'theme_announcement_text' => trim($_POST['ann_text'] ?? 'ฉลองวันพิเศษของคุณด้วยไนท์เค้กมินิมอล บริการเขียนหน้าเค้กฟรี! สั่งล่วงหน้าและจัดส่งเดลิเวอรี่ทั่วกรุงเทพฯ'),
            'theme_logo_text' => trim($_POST['logo_text'] ?? 'NightCake'),
            'theme_delivery_h2' => trim($_POST['delivery_h2'] ?? 'ส่งความสุขได้ง่ายๆ<br>พร้อมบริการเดลิเวอรี่จัดส่งรวดเร็ว'),
            'theme_delivery_p' => trim($_POST['delivery_p'] ?? 'ไนท์เค้กคัดสรรสิ่งที่ดีที่สุดเพื่อตอบโจทย์ทุกงานเลี้ยงวันเกิด วันครบรอบ หรือกิจกรรมในครอบครัว ด้วยเค้กน่ารักๆ ตกแต่งเขียนข้อความได้ฟรีตามที่คุณต้องการ'),
            'theme_delivery_phone' => trim($_POST['delivery_phone'] ?? '094-492-2299'),
            'theme_delivery_line' => trim($_POST['delivery_line'] ?? '@NIGHTCAKE'),
            'theme_delivery_hours' => trim($_POST['delivery_hours'] ?? '09:00 น. - 20:00 น. ทุกวัน'),
            'theme_delivery_btn' => trim($_POST['delivery_btn'] ?? 'แอดไลน์สอบถามข้อมูลเพิ่มเติม'),
            'theme_footer_desc' => trim($_POST['footer_desc'] ?? 'ความสุขก้อนใหญ่ ดีไซน์มินิมอล อบด้วยความใส่ใจและวัตถุดิบชั้นเลิศเพื่อคนสำคัญของคุณในทุกๆ วันพิเศษ'),
            'theme_f_menu_title' => trim($_POST['f_menu_title'] ?? 'เมนูเค้ก'),
            'theme_f_menu_l1' => trim($_POST['f_menu_l1'] ?? 'เค้กมินิมอลพาสเทล'),
            'theme_f_menu_l2' => trim($_POST['f_menu_l2'] ?? 'เค้กมะพร้าวครีมสด'),
            'theme_f_menu_l3' => trim($_POST['f_menu_l3'] ?? 'เค้กช็อกโกแลตฟัดจ์ในตำนาน'),
            'theme_f_menu_l4' => trim($_POST['f_menu_l4'] ?? 'คอลเลกชันเค้กวันเกิด'),
            'theme_f_branch_title' => trim($_POST['f_branch_title'] ?? 'สาขายอดนิยม'),
            'theme_f_branch_l1' => trim($_POST['f_branch_l1'] ?? 'สาขา สยามสแควร์'),
            'theme_f_branch_l2' => trim($_POST['f_branch_l2'] ?? 'สาขา เซ็นทรัล ลาดพร้าว'),
            'theme_f_branch_l3' => trim($_POST['f_branch_l3'] ?? 'สาขา เมกา บางนา'),
            'theme_f_branch_l4' => trim($_POST['f_branch_l4'] ?? 'สาขา สามย่าน มิตรทาวน์'),
            'theme_f_contact_title' => trim($_POST['f_contact_title'] ?? 'การติดต่อ'),
            'theme_f_contact_l1' => trim($_POST['f_contact_l1'] ?? 'Line: @NIGHTCAKE'),
            'theme_f_contact_l2' => trim($_POST['f_contact_l2'] ?? 'โทร: 094-492-2299'),
            'theme_f_contact_l3' => trim($_POST['f_contact_l3'] ?? 'Facebook: NightCake'),
            'theme_f_contact_l4' => trim($_POST['f_contact_l4'] ?? 'Instagram: @nightcake'),
            'theme_f_copyright' => trim($_POST['f_copyright'] ?? '© 2026 NightCake Shop. All Rights Reserved.'),
            'theme_f_tagline' => trim($_POST['f_tagline'] ?? 'Minimalist Cake & Birthday Event Specialist')
        ];
        $stmt_text = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach ($texts_to_save as $key => $val) {
            $stmt_text->execute([$key, $val, $val]);
        }
        $theme_hero_h2 = $hero_h2;
        $theme_hero_h1 = $hero_h1;
        $theme_hero_p = $hero_p;
        $theme_f1_icon = $texts_to_save['theme_f1_icon'];
        $theme_f1_title = $texts_to_save['theme_f1_title'];
        $theme_f1_desc = $texts_to_save['theme_f1_desc'];
        $theme_f2_icon = $texts_to_save['theme_f2_icon'];
        $theme_f2_title = $texts_to_save['theme_f2_title'];
        $theme_f2_desc = $texts_to_save['theme_f2_desc'];
        $theme_f3_icon = $texts_to_save['theme_f3_icon'];
        $theme_f3_title = $texts_to_save['theme_f3_title'];
        $theme_f3_desc = $texts_to_save['theme_f3_desc'];
        $theme_menu_title = $texts_to_save['theme_menu_title'];
        $theme_menu_subtitle = $texts_to_save['theme_menu_subtitle'];
        $theme_announcement_icon = $texts_to_save['theme_announcement_icon'];
        $theme_announcement_text = $texts_to_save['theme_announcement_text'];
        $theme_logo_text = $texts_to_save['theme_logo_text'];
        $theme_delivery_h2 = $texts_to_save['theme_delivery_h2'];
        $theme_delivery_p = $texts_to_save['theme_delivery_p'];
        $theme_delivery_phone = $texts_to_save['theme_delivery_phone'];
        $theme_delivery_line = $texts_to_save['theme_delivery_line'];
        $theme_delivery_hours = $texts_to_save['theme_delivery_hours'];
        $theme_delivery_btn = $texts_to_save['theme_delivery_btn'];
        $theme_footer_desc = $texts_to_save['theme_footer_desc'];
        $theme_f_menu_title = $texts_to_save['theme_f_menu_title'];
        $theme_f_menu_l1 = $texts_to_save['theme_f_menu_l1'];
        $theme_f_menu_l2 = $texts_to_save['theme_f_menu_l2'];
        $theme_f_menu_l3 = $texts_to_save['theme_f_menu_l3'];
        $theme_f_menu_l4 = $texts_to_save['theme_f_menu_l4'];
        $theme_f_branch_title = $texts_to_save['theme_f_branch_title'];
        $theme_f_branch_l1 = $texts_to_save['theme_f_branch_l1'];
        $theme_f_branch_l2 = $texts_to_save['theme_f_branch_l2'];
        $theme_f_branch_l3 = $texts_to_save['theme_f_branch_l3'];
        $theme_f_branch_l4 = $texts_to_save['theme_f_branch_l4'];
        $theme_f_contact_title = $texts_to_save['theme_f_contact_title'];
        $theme_f_contact_l1 = $texts_to_save['theme_f_contact_l1'];
        $theme_f_contact_l2 = $texts_to_save['theme_f_contact_l2'];
        $theme_f_contact_l3 = $texts_to_save['theme_f_contact_l3'];
        $theme_f_contact_l4 = $texts_to_save['theme_f_contact_l4'];
        $theme_f_copyright = $texts_to_save['theme_f_copyright'];
        $theme_f_tagline = $texts_to_save['theme_f_tagline'];

        // 3. Handle multiple image uploads if new files are sent (append to slides)
        if (isset($_FILES['hero_image'])) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $files = $_FILES['hero_image'];
            $file_count = is_array($files['name']) ? count($files['name']) : 1;
            
            for ($i = 0; $i < $file_count; $i++) {
                $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                if ($error === UPLOAD_ERR_OK) {
                    $file_name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                    $file_tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    $allowed_exts = ['png', 'jpg', 'jpeg', 'gif'];
                    if (in_array($file_ext, $allowed_exts)) {
                        $new_file_name = 'hero_slide_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                        $dest_path = $upload_dir . $new_file_name;
                        
                        if (move_uploaded_file($file_tmp, $dest_path)) {
                            $relative_path = 'uploads/' . $new_file_name;
                            $theme_hero_slides[] = $relative_path;
                        }
                    }
                }
            }
        }

        // 4. Save final slides list array as JSON in settings
        $encoded_slides = json_encode($theme_hero_slides);
        $stmt_slides = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('theme_hero_slides', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt_slides->execute([$encoded_slides, $encoded_slides]);

        // Also save first slide as theme_hero_image default
        if (!empty($theme_hero_slides)) {
            $first_slide = $theme_hero_slides[0];
            $stmt_hero = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('theme_hero_image', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt_hero->execute([$first_slide, $first_slide]);
            $theme_hero_image = $first_slide;
        } else {
            $stmt_hero = $pdo->prepare("DELETE FROM settings WHERE setting_key = 'theme_hero_image'");
            $stmt_hero->execute();
            $theme_hero_image = '';
        }

        // 5. Handle delivery image upload and store in separate folder
        if (isset($_FILES['delivery_image']) && $_FILES['delivery_image']['error'] === UPLOAD_ERR_OK) {
            $delivery_upload_dir = '../uploads/delivery/';
            if (!is_dir($delivery_upload_dir)) {
                mkdir($delivery_upload_dir, 0755, true);
            }
            
            $file = $_FILES['delivery_image'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['png', 'jpg', 'jpeg', 'gif'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = 'delivery_img_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $dest_path = $delivery_upload_dir . $new_file_name;
                
                if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                    $theme_delivery_image = 'uploads/delivery/' . $new_file_name;
                    $stmt_delivery_img = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('theme_delivery_image', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt_delivery_img->execute([$theme_delivery_image, $theme_delivery_image]);
                }
            }
        }

        $pdo->commit();
        $success_msg = 'บันทึกการปรับแต่งธีมของคุณสำเร็จแล้ว! 🎉';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_msg = 'เกิดข้อผิดพลาดในการบันทึกธีม: ' . $e->getMessage();
    }
}
?>

<div class="customizer-layout">
    <!-- Left Panel: Controls -->
    <div class="customizer-panel">
        <h3 class="customizer-panel-title">🎨 ปรับแต่งสี & หน้าปกเรียลไทม์</h3>
        <p class="customizer-panel-desc">คุณสามารถแก้ไขสีพื้นหลังของร้านค้าและอัปโหลดภาพหน้าปกใหม่ และเห็นผลลัพธ์ในพรีวิวด้านขวาแบบเรียลไทม์ทันทีก่อนกดบันทึก</p>

        <?php if(!empty($success_msg)): ?>
            <div class="customizer-alert success">
                ✅ <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_msg)): ?>
            <div class="customizer-alert error">
                ❌ <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="customize_theme.php" enctype="multipart/form-data" class="customizer-form" id="theme-form">
            <!-- Hidden Fields for WYSIWYG Live Text Editing -->
            <input type="hidden" id="logo_text_input" name="logo_text" value="<?= htmlspecialchars($theme_logo_text) ?>">
            <input type="hidden" id="hero_h2_input" name="hero_h2" value="<?= htmlspecialchars($theme_hero_h2) ?>">
            <input type="hidden" id="hero_h1_input" name="hero_h1" value="<?= htmlspecialchars($theme_hero_h1) ?>">
            <input type="hidden" id="hero_p_input" name="hero_p" value="<?= htmlspecialchars($theme_hero_p) ?>">
            
            <!-- Hidden Fields for WYSIWYG Features & Icons Live Editing -->
            <input type="hidden" id="f1_icon_input" name="f1_icon" value="<?= htmlspecialchars($theme_f1_icon) ?>">
            <input type="hidden" id="f1_title_input" name="f1_title" value="<?= htmlspecialchars($theme_f1_title) ?>">
            <input type="hidden" id="f1_desc_input" name="f1_desc" value="<?= htmlspecialchars($theme_f1_desc) ?>">
            
            <input type="hidden" id="f2_icon_input" name="f2_icon" value="<?= htmlspecialchars($theme_f2_icon) ?>">
            <input type="hidden" id="f2_title_input" name="f2_title" value="<?= htmlspecialchars($theme_f2_title) ?>">
            <input type="hidden" id="f2_desc_input" name="f2_desc" value="<?= htmlspecialchars($theme_f2_desc) ?>">
            
            <input type="hidden" id="f3_icon_input" name="f3_icon" value="<?= htmlspecialchars($theme_f3_icon) ?>">
            <input type="hidden" id="f3_title_input" name="f3_title" value="<?= htmlspecialchars($theme_f3_title) ?>">
            <input type="hidden" id="f3_desc_input" name="f3_desc" value="<?= htmlspecialchars($theme_f3_desc) ?>">
            <input type="hidden" id="menu_title_input" name="menu_title" value="<?= htmlspecialchars($theme_menu_title) ?>">
            <input type="hidden" id="menu_subtitle_input" name="menu_subtitle" value="<?= htmlspecialchars($theme_menu_subtitle) ?>">
            <input type="hidden" id="ann_icon_input" name="ann_icon" value="<?= htmlspecialchars($theme_announcement_icon) ?>">
            <input type="hidden" id="ann_text_input" name="ann_text" value="<?= htmlspecialchars($theme_announcement_text) ?>">
            <input type="hidden" id="delivery_h2_input" name="delivery_h2" value="<?= htmlspecialchars($theme_delivery_h2) ?>">
            <input type="hidden" id="delivery_p_input" name="delivery_p" value="<?= htmlspecialchars($theme_delivery_p) ?>">
            <input type="hidden" id="delivery_phone_input" name="delivery_phone" value="<?= htmlspecialchars($theme_delivery_phone) ?>">
            <input type="hidden" id="delivery_line_input" name="delivery_line" value="<?= htmlspecialchars($theme_delivery_line) ?>">
            <input type="hidden" id="delivery_hours_input" name="delivery_hours" value="<?= htmlspecialchars($theme_delivery_hours) ?>">
            <input type="hidden" id="delivery_btn_input" name="delivery_btn" value="<?= htmlspecialchars($theme_delivery_btn) ?>">
            <input type="hidden" id="footer_desc_input" value="<?= htmlspecialchars($theme_footer_desc) ?>">
            <input type="hidden" id="f_menu_title_input" name="f_menu_title" value="<?= htmlspecialchars($theme_f_menu_title) ?>">
            <input type="hidden" id="f_menu_l1_input" name="f_menu_l1" value="<?= htmlspecialchars($theme_f_menu_l1) ?>">
            <input type="hidden" id="f_menu_l2_input" name="f_menu_l2" value="<?= htmlspecialchars($theme_f_menu_l2) ?>">
            <input type="hidden" id="f_menu_l3_input" name="f_menu_l3" value="<?= htmlspecialchars($theme_f_menu_l3) ?>">
            <input type="hidden" id="f_menu_l4_input" name="f_menu_l4" value="<?= htmlspecialchars($theme_f_menu_l4) ?>">
            <input type="hidden" id="f_branch_title_input" name="f_branch_title" value="<?= htmlspecialchars($theme_f_branch_title) ?>">
            <input type="hidden" id="f_branch_l1_input" name="f_branch_l1" value="<?= htmlspecialchars($theme_f_branch_l1) ?>">
            <input type="hidden" id="f_branch_l2_input" name="f_branch_l2" value="<?= htmlspecialchars($theme_f_branch_l2) ?>">
            <input type="hidden" id="f_branch_l3_input" name="f_branch_l3" value="<?= htmlspecialchars($theme_f_branch_l3) ?>">
            <input type="hidden" id="f_branch_l4_input" name="f_branch_l4" value="<?= htmlspecialchars($theme_f_branch_l4) ?>">
            <input type="hidden" id="f_contact_title_input" name="f_contact_title" value="<?= htmlspecialchars($theme_f_contact_title) ?>">
            <input type="hidden" id="f_contact_l1_input" name="f_contact_l1" value="<?= htmlspecialchars($theme_f_contact_l1) ?>">
            <input type="hidden" id="f_contact_l2_input" name="f_contact_l2" value="<?= htmlspecialchars($theme_f_contact_l2) ?>">
            <input type="hidden" id="f_contact_l3_input" name="f_contact_l3" value="<?= htmlspecialchars($theme_f_contact_l3) ?>">
            <input type="hidden" id="f_contact_l4_input" name="f_contact_l4" value="<?= htmlspecialchars($theme_f_contact_l4) ?>">
            <input type="hidden" id="f_copyright_input" name="f_copyright" value="<?= htmlspecialchars($theme_f_copyright) ?>">
            <input type="hidden" id="f_tagline_input" name="f_tagline" value="<?= htmlspecialchars($theme_f_tagline) ?>">
            <input type="hidden" id="delivery_image_input" name="delivery_image" value="<?= htmlspecialchars($theme_delivery_image) ?>">
            <input type="file" id="delivery_image_file" name="delivery_image" style="display: none;" onchange="updateLiveDeliveryImage(this)" accept="image/*">

            <!-- WYSIWYG Tip Banner -->
            <div style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 0.8rem; border-radius: var(--border-radius-sm); font-size: 0.75rem; line-height: 1.5;">
                💡 <strong>เคล็ดลับนักแต่งธีม:</strong> คุณสามารถ**คลิกแก้ไขตัวหนังสือ**บนหน้าจอพรีวิวด้านขวาได้โดยตรง! ระบบจะจดจำค่าที่คุณพิมพ์ในแบบเรียลไทม์
            </div>

            <!-- Shop Logo Text Input (Left Panel Edit instead of Preview click refresh) -->
            <div class="customizer-field" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <label for="logo_text_panel_input" style="font-weight: 600;">ชื่อร้านค้า / โลโก้ (Shop Name / Logo Text)</label>
                <input type="text" id="logo_text_panel_input" style="width: 100%; border: 1.5px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 0.6rem; font-family: inherit; font-size: 0.9rem; box-sizing: border-box;" value="<?= htmlspecialchars($theme_logo_text) ?>" placeholder="NightCake">
                <small class="field-help" style="color: var(--text-light); font-size: 0.75rem; margin-top: 0.3rem; display: block;">พิมพ์เปลี่ยนชื่อโลโก้หลักด้านบนซ้ายของเว็บไซต์ โดยจะอัปเดตแบบเรียลไทม์ฝั่งขวา</small>
            </div>

            <!-- Footer Content Customization Section -->
            <div class="customizer-field" style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
                <label style="font-weight: bold; font-size: 1rem; color: var(--primary-hover); display: block; margin-bottom: 0.8rem;">📝 ข้อความท้ายเว็บไซต์ (Footer Content)</label>
                
                <!-- Menu Column -->
                <div style="margin-bottom: 1.2rem; background: #fafafa; padding: 0.8rem; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <label style="font-weight: 600; display: block; margin-bottom: 0.4rem; font-size: 0.85rem;">คอลัมน์ที่ 1: เมนูหลัก</label>
                    <input type="text" name="f_menu_title" id="f_menu_title_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_menu_title) ?>" placeholder="เมนูเค้ก">
                    <input type="text" name="f_menu_l1" id="f_menu_l1_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_menu_l1) ?>" placeholder="เค้กมินิมอลพาสเทล">
                    <input type="text" name="f_menu_l2" id="f_menu_l2_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_menu_l2) ?>" placeholder="เค้กมะพร้าวครีมสด">
                    <input type="text" name="f_menu_l3" id="f_menu_l3_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_menu_l3) ?>" placeholder="เค้กช็อกโกแลตฟัดจ์ในตำนาน">
                    <input type="text" name="f_menu_l4" id="f_menu_l4_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box;" value="<?= htmlspecialchars($theme_f_menu_l4) ?>" placeholder="คอลเลกชันเค้กวันเกิด">
                </div>

                <!-- Branch Column -->
                <div style="margin-bottom: 1.2rem; background: #fafafa; padding: 0.8rem; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <label style="font-weight: 600; display: block; margin-bottom: 0.4rem; font-size: 0.85rem;">คอลัมน์ที่ 2: สาขายอดนิยม</label>
                    <input type="text" name="f_branch_title" id="f_branch_title_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_branch_title) ?>" placeholder="สาขายอดนิยม">
                    <input type="text" name="f_branch_l1" id="f_branch_l1_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_branch_l1) ?>" placeholder="สาขา สยามสแควร์">
                    <input type="text" name="f_branch_l2" id="f_branch_l2_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_branch_l2) ?>" placeholder="สาขา เซ็นทรัล ลาดพร้าว">
                    <input type="text" name="f_branch_l3" id="f_branch_l3_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_branch_l3) ?>" placeholder="สาขา เมกา บางนา">
                    <input type="text" name="f_branch_l4" id="f_branch_l4_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box;" value="<?= htmlspecialchars($theme_f_branch_l4) ?>" placeholder="สาขา สามย่าน มิตรทาวน์">
                </div>

                <!-- Contact Column -->
                <div style="margin-bottom: 1.2rem; background: #fafafa; padding: 0.8rem; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <label style="font-weight: 600; display: block; margin-bottom: 0.4rem; font-size: 0.85rem;">คอลัมน์ที่ 3: ข้อมูลการติดต่อ</label>
                    <input type="text" name="f_contact_title" id="f_contact_title_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_contact_title) ?>" placeholder="การติดต่อ">
                    <input type="text" name="f_contact_l1" id="f_contact_l1_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_contact_l1) ?>" placeholder="Line: @NIGHTCAKE">
                    <input type="text" name="f_contact_l2" id="f_contact_l2_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_contact_l2) ?>" placeholder="โทร: 094-492-2299">
                    <input type="text" name="f_contact_l3" id="f_contact_l3_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_contact_l3) ?>" placeholder="Facebook: NightCake">
                    <input type="text" name="f_contact_l4" id="f_contact_l4_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box;" value="<?= htmlspecialchars($theme_f_contact_l4) ?>" placeholder="Instagram: @nightcake">
                </div>

                <!-- Footer Description -->
                <div style="margin-bottom: 1.2rem; background: #fafafa; padding: 0.8rem; border-radius: 8px; border: 1px dashed var(--border-color);">
                    <label style="font-weight: 600; display: block; margin-bottom: 0.4rem; font-size: 0.85rem;">คำอธิบายร้านค้าท้ายเว็บ (Footer Description)</label>
                    <textarea name="footer_desc" id="footer_desc_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; resize: vertical; line-height: 1.4;" rows="3" placeholder="ความสุขก้อนใหญ่ ดีไซน์มินิมอล อบด้วยความใส่ใจ..."><?= htmlspecialchars($theme_footer_desc) ?></textarea>
                </div>

                <!-- Copyright & Tagline -->
                <div style="background: #fafafa; padding: 0.8rem; border-radius: 8px; border: 1px dashed var(--border-color); margin-bottom: 0.5rem;">
                    <label style="font-weight: 600; display: block; margin-bottom: 0.4rem; font-size: 0.85rem;">แถบลิขสิทธิ์ & สโลแกน (Copyright & Tagline)</label>
                    <input type="text" name="f_copyright" id="f_copyright_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box; margin-bottom: 0.4rem;" value="<?= htmlspecialchars($theme_f_copyright) ?>" placeholder="© 2026 NightCake Shop. All Rights Reserved.">
                    <input type="text" name="f_tagline" id="f_tagline_panel" class="admin-input-text" style="width: 100%; border: 1px solid var(--border-color); border-radius: 4px; padding: 0.4rem 0.6rem; font-family: inherit; font-size: 0.85rem; box-sizing: border-box;" value="<?= htmlspecialchars($theme_f_tagline) ?>" placeholder="Minimalist Cake & Birthday Event Specialist">
                </div>
            </div>

            <!-- 1. Background Color Picker -->
            <div class="customizer-field">
                <label>สีพื้นหลังหน้าเว็บหลัก (Background Color)</label>
                <div class="color-picker-group">
                    <div class="color-picker-header">
                        <div class="color-preview-badge" id="bg_color_badge" style="background-color: <?= htmlspecialchars($theme_bg_color) ?>;"></div>
                        <input type="text" id="bg_color_text" name="bg_color" value="<?= htmlspecialchars($theme_bg_color) ?>" class="color-hex-input" placeholder="#fdfdfd">
                    </div>
                    <div class="hsl-sliders">
                        <div class="slider-row">
                            <span class="slider-label">เฉดสี (Hue)</span>
                            <input type="range" class="hue-slider" min="0" max="360" value="0">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสด (Saturation)</span>
                            <input type="range" class="sat-slider" min="0" max="100" value="100">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสว่าง (Lightness)</span>
                            <input type="range" class="light-slider" min="0" max="100" value="50">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preset Background Palettes -->
            <div class="customizer-field" style="margin-bottom: 1.2rem;">
                <label>จานสีพื้นหลังแนะนำ (Preset Backgrounds)</label>
                <div class="presets-row">
                    <span class="preset-dot" style="background-color: #fdfdfd; border: 1px solid #ddd;" title="Classic Soft White" onclick="applyPreset('#fdfdfd')"></span>
                    <span class="preset-dot" style="background-color: #fcf6f5;" title="Pastel Soft Pinkish" onclick="applyPreset('#fcf6f5')"></span>
                    <span class="preset-dot" style="background-color: #f0f7f4;" title="Pastel Soft Mint" onclick="applyPreset('#f0f7f4')"></span>
                    <span class="preset-dot" style="background-color: #f4f1de;" title="Creamy Vanilla" onclick="applyPreset('#f4f1de')"></span>
                    <span class="preset-dot" style="background-color: #eae2b7;" title="Pastel Warm Yellow" onclick="applyPreset('#eae2b7')"></span>
                    <span class="preset-dot" style="background-color: #e8f1f5;" title="Pastel Ice Blue" onclick="applyPreset('#e8f1f5')"></span>
                </div>
            </div>

            <!-- 2. Primary Theme Color Picker -->
            <div class="customizer-field" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <label>สีหลักของร้านค้า (Primary Theme Color - Accents & Buttons)</label>
                <div class="color-picker-group">
                    <div class="color-picker-header">
                        <div class="color-preview-badge" id="primary_color_badge" style="background-color: <?= htmlspecialchars($theme_primary_color) ?>;"></div>
                        <input type="text" id="primary_color_text" name="primary_color" value="<?= htmlspecialchars($theme_primary_color) ?>" class="color-hex-input" placeholder="#f7a8b8">
                    </div>
                    <div class="hsl-sliders">
                        <div class="slider-row">
                            <span class="slider-label">เฉดสี (Hue)</span>
                            <input type="range" class="hue-slider" min="0" max="360" value="0">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสด (Saturation)</span>
                            <input type="range" class="sat-slider" min="0" max="100" value="100">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสว่าง (Lightness)</span>
                            <input type="range" class="light-slider" min="0" max="100" value="50">
                        </div>
                    </div>
                </div>
                <small class="field-help" style="margin-top: -0.5rem; margin-bottom: 0.8rem; display: block;">ปุ่ม ลิงก์ และจุดเน้นต่าง ๆ จะคำนวณเฉดสีโต้ตอบ (Hover) และพื้นหลังอ่อนโดยอัตโนมัติ</small>
            </div>

            <!-- Preset Primary Colors -->
            <div class="customizer-field" style="margin-bottom: 1.2rem;">
                <label>แนะนำธีมสีหลักร้านเค้ก (Theme Preset Colors)</label>
                <div class="presets-row">
                    <span class="preset-dot" style="background-color: #f7a8b8;" title="Warm Rose Pink" onclick="applyPrimaryPreset('#f7a8b8')"></span>
                    <span class="preset-dot" style="background-color: #a8d5e2;" title="Pastel Sky Blue" onclick="applyPrimaryPreset('#a8d5e2')"></span>
                    <span class="preset-dot" style="background-color: #b7e4c7;" title="Sweet Mint Green" onclick="applyPrimaryPreset('#b7e4c7')"></span>
                    <span class="preset-dot" style="background-color: #e9c46a;" title="Warm Honey Gold" onclick="applyPrimaryPreset('#e9c46a')"></span>
                    <span class="preset-dot" style="background-color: #f4a261;" title="Sweet Peach Orange" onclick="applyPrimaryPreset('#f4a261')"></span>
                    <span class="preset-dot" style="background-color: #c77dff;" title="Lilac Lavender" onclick="applyPrimaryPreset('#c77dff')"></span>
                </div>
            </div>

            <!-- 3. Text and Description Colors -->
            <div class="customizer-field" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <label>สีข้อความหลัก (Main Text Color)</label>
                <div class="color-picker-group">
                    <div class="color-picker-header">
                        <div class="color-preview-badge" id="text_color_badge" style="background-color: <?= htmlspecialchars($theme_text_color) ?>;"></div>
                        <input type="text" id="text_color_text" name="text_color" value="<?= htmlspecialchars($theme_text_color) ?>" class="color-hex-input" placeholder="#2e2e2e">
                    </div>
                    <div class="hsl-sliders">
                        <div class="slider-row">
                            <span class="slider-label">เฉดสี (Hue)</span>
                            <input type="range" class="hue-slider" min="0" max="360" value="0">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสด (Saturation)</span>
                            <input type="range" class="sat-slider" min="0" max="100" value="100">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสว่าง (Lightness)</span>
                            <input type="range" class="light-slider" min="0" max="100" value="50">
                        </div>
                    </div>
                </div>
            </div>

            <div class="customizer-field" style="margin-bottom: 1.2rem;">
                <label>สีคำอธิบาย/ตัวหนังสือรอง (Muted Text Color)</label>
                <div class="color-picker-group">
                    <div class="color-picker-header">
                        <div class="color-preview-badge" id="muted_color_badge" style="background-color: <?= htmlspecialchars($theme_muted_text_color) ?>;"></div>
                        <input type="text" id="muted_color_text" name="muted_text_color" value="<?= htmlspecialchars($theme_muted_text_color) ?>" class="color-hex-input" placeholder="#757575">
                    </div>
                    <div class="hsl-sliders">
                        <div class="slider-row">
                            <span class="slider-label">เฉดสี (Hue)</span>
                            <input type="range" class="hue-slider" min="0" max="360" value="0">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสด (Saturation)</span>
                            <input type="range" class="sat-slider" min="0" max="100" value="100">
                        </div>
                        <div class="slider-row">
                            <span class="slider-label">ความสว่าง (Lightness)</span>
                            <input type="range" class="light-slider" min="0" max="100" value="50">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Hero Image Uploader -->
            <div class="customizer-field" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <label>เพิ่มรูปภาพสไลด์ใหม่ (Add Slide Image)</label>
                
                <label for="hero_image_input" class="custom-file-upload-wrapper">
                    <span class="upload-icon">📷</span>
                    <span class="upload-title-text">เลือกเพิ่มสไลด์เค้กใหม่</span>
                    <span class="upload-filename" id="upload-filename-text">ยังไม่ได้เลือกไฟล์ (รูปภาพใหม่จะถูกเพิ่มเข้าสไลด์)</span>
                </label>
                <input type="file" id="hero_image_input" name="hero_image[]" accept="image/*" multiple onchange="updateLiveHero(this)" style="display: none;">
                <small class="field-help">รูปภาพที่อัปโหลดจะถูกแนบเพิ่มเป็นสไลด์แผ่นต่อๆ ไปโดยไม่ทับรูปภาพเดิม</small>
                
                <!-- New upload preview box restored -->
                <div class="current-hero-preview" id="new-slide-preview-container" style="display: none; margin-top: 0.8rem; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 0.6rem; flex-direction: column; gap: 0.4rem;">
                    <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">รูปภาพที่เลือกใหม่ (ตัวอย่างก่อนบันทึก):</span>
                    <img id="current-hero-img" src="" alt="New Slide Preview" style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);">
                </div>
            </div>

            <!-- 3. Current Slides List Manager -->
            <!-- 3. Current Slides List Manager -->
            <div class="customizer-field" style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-bottom: 0.5rem;">
                <label>รายการรูปภาพสไลด์ทั้งหมด (<span id="slides-count-text"><?= count($theme_hero_slides) ?></span> รูป)</label>
                <div class="slides-manager-list" id="slides-list-container" style="display: flex; flex-direction: column; gap: 0.6rem; max-height: 250px; overflow-y: auto; padding-right: 0.25rem;">
                    <!-- Rendered dynamically by JavaScript to support instant delete -->
                </div>
                <input type="hidden" name="theme_slides" id="theme_slides_input" value="<?= htmlspecialchars(json_encode($theme_hero_slides)) ?>">
            </div>

            <!-- 4. Delivery Info Image Uploader -->
            <div class="customizer-field" style="border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <label>รูปภาพแนะนำการบริการจัดส่ง (Delivery Image)</label>
                
                <div class="custom-file-upload-wrapper" onclick="triggerDeliveryImageUpload()" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.25rem; border: 2px dashed var(--border-color); border-radius: var(--border-radius-md); text-align: center; transition: var(--transition);">
                    <span class="upload-icon" style="font-size: 1.8rem; margin-bottom: 0.4rem;">🚚</span>
                    <span class="upload-title-text" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">เปลี่ยนรูปภาพจัดส่ง</span>
                    <span class="upload-filename" id="delivery-filename-text" style="font-size: 0.75rem; color: var(--text-light); margin-top: 0.2rem;">ยังไม่ได้เลือกรูปใหม่ (จะใช้รูปภาพเดิมจนกว่าจะเปลี่ยน)</span>
                </div>
                <small class="field-help" style="font-size: 0.75rem; color: var(--text-light); line-height: 1.4;">คลิกที่นี่หรือคลิกบนรูปภาพจัดส่ง in หน้าจอพรีวิวด้านขวา เพื่ออัปโหลดรูปภาพบริการจัดส่งใหม่</small>
            </div>

            <!-- Buttons -->
            <div class="customizer-actions">
                <button type="submit" name="save_theme" class="admin-btn admin-btn-primary" style="width: 100%; justify-content: center; font-size: 0.95rem; padding: 0.8rem;">
                    💾 บันทึกการตั้งค่าธีมทั้งหมด
                </button>
                <button type="button" class="admin-btn admin-btn-secondary" style="width: 100%; justify-content: center; font-size: 0.9rem; padding: 0.7rem; margin-top: 0.5rem;" onclick="resetCustomizer()">
                    🔄 ยกเลิก / รีเซ็ตตัวอย่าง
                </button>
            </div>
        </form>
    </div>

    <!-- Right Panel: Desktop Live Iframe View (Index.php) -->
    <div class="customizer-preview-container">
        <div class="preview-header">
            <span class="preview-dot red"></span>
            <span class="preview-dot yellow"></span>
            <span class="preview-dot green"></span>
            <span class="preview-title">ตัวอย่างเว็บไซต์หน้าแรกจริง (Live Desktop Preview) - เป๊ะๆ 100%</span>
        </div>
        <div class="preview-iframe-wrapper">
            <iframe id="preview_iframe" src="../index.php?customizer_preview=1" class="preview-iframe"></iframe>
        </div>
    </div>
</div>

<style>
/* Customizer Page Layout Split */
.customizer-layout {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 1.5rem;
    height: calc(100vh - 120px);
    margin-top: 0.5rem;
}

/* Left panel controls */
.customizer-panel {
    background-color: var(--card-bg);
    border-radius: var(--border-radius-md);
    border: 1px solid var(--border-color);
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    box-shadow: var(--shadow-subtle);
}
.customizer-panel-title {
    font-size: 1.15rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-main);
}
.customizer-panel-desc {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin: 0;
    line-height: 1.5;
}

/* Fields formatting */
.customizer-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.customizer-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.customizer-field label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-main);
}
.field-help {
    font-size: 0.72rem;
    color: var(--text-light);
    line-height: 1.4;
}

/* Color input picker styling */
.color-picker-group {
    background-color: var(--secondary-color);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-sm);
    padding: 0.8rem;
    box-shadow: var(--shadow-subtle);
}
.color-picker-header {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-bottom: 0.8rem;
}
.color-preview-badge {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.2s;
}
.color-preview-badge:hover {
    transform: scale(1.1);
}
.color-hex-input {
    flex-grow: 1;
    border: 1.5px solid var(--border-color);
    border-radius: 8px;
    padding: 0.4rem 0.6rem;
    font-family: monospace;
    font-size: 0.85rem;
    color: var(--text-main);
    background-color: #fff;
    outline: none;
    transition: border-color 0.2s;
}
.color-hex-input:focus {
    border-color: var(--primary-color);
}
.hsl-sliders {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    background-color: #fff;
    border-radius: 8px;
    padding: 0.6rem;
    border: 1px solid var(--border-color);
}
.slider-row {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}
.slider-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    font-weight: 500;
}
/* Style range inputs */
.hsl-sliders input[type="range"] {
    -webkit-appearance: none;
    width: 100%;
    height: 8px;
    border-radius: 4px;
    outline: none;
    background: #ddd;
}
.hsl-sliders input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid var(--primary-color);
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    cursor: pointer;
    transition: transform 0.1s;
}
.hsl-sliders input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.2);
}
/* Rainbow Hue background */
.hue-slider {
    background: linear-gradient(to right, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000) !important;
}

/* Preset circles */
.presets-row {
    display: flex;
    gap: 0.5rem;
}
.preset-dot {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    cursor: pointer;
    transition: var(--transition);
}
.preset-dot:hover {
    transform: scale(1.15);
    box-shadow: 0 0 5px rgba(0,0,0,0.15);
}

/* File input & current thumbnail preview */
.custom-file-upload-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px dashed var(--primary-color);
    padding: 1.5rem 1rem;
    border-radius: var(--border-radius-sm);
    cursor: pointer;
    background-color: rgba(247, 168, 184, 0.02);
    transition: var(--transition);
    text-align: center;
    gap: 0.4rem;
}
.custom-file-upload-wrapper:hover {
    background-color: rgba(247, 168, 184, 0.08);
    border-color: var(--primary-hover);
    transform: translateY(-2.5px);
    box-shadow: 0 4px 15px rgba(247, 168, 184, 0.1);
}
.upload-icon {
    font-size: 1.8rem;
    transition: var(--transition);
}
.custom-file-upload-wrapper:hover .upload-icon {
    transform: scale(1.15) rotate(-5deg);
}
.upload-title-text {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-main);
}
.upload-filename {
    font-size: 0.72rem;
    color: var(--text-light);
    word-break: break-all;
}
.current-hero-preview {
    margin-top: 0.8rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-sm);
    padding: 0.6rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.current-hero-preview span {
    font-size: 0.72rem;
    color: var(--text-muted);
    font-weight: 500;
}
.current-hero-preview img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid var(--border-color);
}

/* Customizer alerts */
.customizer-alert {
    padding: 0.75rem;
    border-radius: var(--border-radius-sm);
    font-size: 0.8rem;
    font-weight: 500;
}
.customizer-alert.success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}
.customizer-alert.error {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

/* Action button area */
.customizer-actions {
    margin-top: 0.5rem;
    border-top: 1px solid var(--border-color);
    padding-top: 1rem;
}

/* Right panel: Iframe container */
.customizer-preview-container {
    background-color: #fff;
    border-radius: var(--border-radius-md);
    border: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: var(--shadow-subtle);
}
.preview-header {
    background-color: #f7f7f7;
    padding: 0.6rem 1rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.preview-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
.preview-dot.red { background-color: #ff5f56; }
.preview-dot.yellow { background-color: #ffbd2e; }
.preview-dot.green { background-color: #27c93f; }
.preview-title {
    font-size: 0.75rem;
    color: var(--text-light);
    font-weight: 500;
    margin-left: 0.6rem;
}

/* Live Iframe Wrapper */
.preview-iframe-wrapper {
    flex-grow: 1;
    height: 100%;
}
.preview-iframe {
    width: 100%;
    height: 100%;
    border: none;
    background-color: #fff;
}
</style>

<script>
// Real-time Preview Script

function hslToHex(h, s, l) {
    s /= 100;
    l /= 100;
    let c = (1 - Math.abs(2 * l - 1)) * s;
    let x = c * (1 - Math.abs((h / 60) % 2 - 1));
    let m = l - c / 2;
    let r = 0, g = 0, b = 0;
    if (0 <= h && h < 60) { r = c; g = x; b = 0; }
    else if (60 <= h && h < 120) { r = x; g = c; b = 0; }
    else if (120 <= h && h < 180) { r = 0; g = c; b = x; }
    else if (180 <= h && h < 240) { r = 0; g = x; b = c; }
    else if (240 <= h && h < 300) { r = x; g = 0; b = c; }
    else if (300 <= h && h < 360) { r = c; g = 0; b = x; }
    r = Math.round((r + m) * 255).toString(16).padStart(2, '0');
    g = Math.round((g + m) * 255).toString(16).padStart(2, '0');
    b = Math.round((b + m) * 255).toString(16).padStart(2, '0');
    return `#${r}${g}${b}`;
}

function hexToHsl(hex) {
    let shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
    hex = hex.replace(shorthandRegex, function(m, r, g, b) {
        return r + r + g + g + b + b;
    });
    let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!result) return { h: 0, s: 100, l: 50 };
    let r = parseInt(result[1], 16) / 255;
    let g = parseInt(result[2], 16) / 255;
    let b = parseInt(result[3], 16) / 255;
    let max = Math.max(r, g, b), min = Math.min(r, g, b);
    let h, s, l = (max + min) / 2;
    if (max === min) {
        h = s = 0;
    } else {
        let d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r: h = (g - b) / d + (g < b ? 6 : 0); break;
            case g: h = (b - r) / d + 2; break;
            case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
    }
    return {
        h: Math.round(h * 360),
        s: Math.round(s * 100),
        l: Math.round(l * 100)
    };
}

function updateLiveBg(color) {
    const badge = document.getElementById('bg_color_badge');
    const text = document.getElementById('bg_color_text');
    if (badge) badge.style.backgroundColor = color;
    if (text) text.value = color;
    const iframe = document.getElementById('preview_iframe');
    if (iframe) {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc) {
            iframeDoc.documentElement.style.setProperty('--bg-color', color, 'important');
            iframeDoc.body.style.backgroundColor = color;
        }
    }
}

function applyPreset(color) {
    const textInput = document.getElementById('bg_color_text');
    if (textInput) {
        textInput.value = color;
        textInput.dispatchEvent(new Event('input'));
    }
}

// Sync primary theme color
function updateLivePrimary(color) {
    const badge = document.getElementById('primary_color_badge');
    const text = document.getElementById('primary_color_text');
    if (badge) badge.style.backgroundColor = color;
    if (text) text.value = color;
    const iframe = document.getElementById('preview_iframe');
    if (iframe) {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc) {
            iframeDoc.documentElement.style.setProperty('--primary-color', color, 'important');
        }
    }
}

function applyPrimaryPreset(color) {
    const textInput = document.getElementById('primary_color_text');
    if (textInput) {
        textInput.value = color;
        textInput.dispatchEvent(new Event('input'));
    }
}

function updateLiveTextCol(color) {
    const badge = document.getElementById('text_color_badge');
    const text = document.getElementById('text_color_text');
    if (badge) badge.style.backgroundColor = color;
    if (text) text.value = color;
    const iframe = document.getElementById('preview_iframe');
    if (iframe) {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc) {
            iframeDoc.documentElement.style.setProperty('--text-main', color, 'important');
            iframeDoc.body.style.color = color;
        }
    }
}

function updateLiveMutedCol(color) {
    const badge = document.getElementById('muted_color_badge');
    const text = document.getElementById('muted_color_text');
    if (badge) badge.style.backgroundColor = color;
    if (text) text.value = color;
    const iframe = document.getElementById('preview_iframe');
    if (iframe) {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc) {
            iframeDoc.documentElement.style.setProperty('--text-muted', color, 'important');
        }
    }
}

// Controller for custom color slider inputs
document.addEventListener("DOMContentLoaded", () => {
    // Sync logo text panel input to hidden field and iframe logo span
    const logoPanelInput = document.getElementById('logo_text_panel_input');
    const logoHiddenInput = document.getElementById('logo_text_input');
    if (logoPanelInput && logoHiddenInput) {
        logoPanelInput.addEventListener('input', (e) => {
            const val = e.target.value;
            logoHiddenInput.value = val;
            const iframe = document.getElementById('preview_iframe');
            if (iframe) {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                const logoEl = iframeDoc.getElementById('editable-logo-text');
                if (logoEl) {
                    logoEl.innerText = val;
                }
                const footerLogoEl = iframeDoc.getElementById('editable-footer-logo-text');
                if (footerLogoEl) {
                    footerLogoEl.innerText = val;
                }
            }
        });
    }

    const footerFields = [
        'footer_desc',
        'f_menu_title', 'f_menu_l1', 'f_menu_l2', 'f_menu_l3', 'f_menu_l4',
        'f_branch_title', 'f_branch_l1', 'f_branch_l2', 'f_branch_l3', 'f_branch_l4',
        'f_contact_title', 'f_contact_l1', 'f_contact_l2', 'f_contact_l3', 'f_contact_l4',
        'f_copyright', 'f_tagline'
    ];
    footerFields.forEach(field => {
        const panelInput = document.getElementById(`${field}_panel`);
        const hiddenInput = document.getElementById(`${field}_input`);
        if (panelInput && hiddenInput) {
            panelInput.addEventListener('input', (e) => {
                const val = e.target.value;
                hiddenInput.value = val;
                const iframe = document.getElementById('preview_iframe');
                if (iframe) {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    const el = iframeDoc.getElementById(`${field}_el`);
                    if (el) {
                        el.innerText = val;
                    }
                }
            });
        }
    });

    const colorKeys = [
        { key: 'bg_color', updateFn: updateLiveBg },
        { key: 'primary_color', updateFn: updateLivePrimary },
        { key: 'text_color', updateFn: updateLiveTextCol },
        { key: 'muted_color', updateFn: updateLiveMutedCol }
    ];
    
    colorKeys.forEach(({ key, updateFn }) => {
        const badge = document.getElementById(`${key}_badge`);
        const textInput = document.getElementById(`${key}_text`);
        const groupEl = badge.closest('.color-picker-group');
        const hueInput = groupEl.querySelector('.hue-slider');
        const satInput = groupEl.querySelector('.sat-slider');
        const lightInput = groupEl.querySelector('.light-slider');
        
        function syncSlidersFromHex(hex) {
            const hsl = hexToHsl(hex);
            hueInput.value = hsl.h;
            satInput.value = hsl.s;
            lightInput.value = hsl.l;
            updateSliderGradients(hsl.h, hsl.s, hsl.l);
        }
        
        function updateSliderGradients(h, s, l) {
            satInput.style.background = `linear-gradient(to right, hsl(${h}, 0%, ${l}%), hsl(${h}, 100%, ${l}%))`;
            lightInput.style.background = `linear-gradient(to right, #000, hsl(${h}, ${s}%, 50%), #fff)`;
        }
        
        function onSliderInput() {
            const h = parseInt(hueInput.value);
            const s = parseInt(satInput.value);
            const l = parseInt(lightInput.value);
            
            const hex = hslToHex(h, s, l);
            textInput.value = hex;
            badge.style.backgroundColor = hex;
            
            updateSliderGradients(h, s, l);
            updateFn(hex);
        }
        
        hueInput.addEventListener('input', onSliderInput);
        satInput.addEventListener('input', onSliderInput);
        lightInput.addEventListener('input', onSliderInput);
        
        textInput.addEventListener('input', (e) => {
            const val = e.target.value;
            if (/^#[0-9a-fA-F]{3,6}$/.test(val)) {
                badge.style.backgroundColor = val;
                syncSlidersFromHex(val);
                updateFn(val);
            }
        });
        
        // Initial load sync
        syncSlidersFromHex(textInput.value);
    });
});

window.triggerDeliveryImageUpload = function() {
    const fileInput = document.getElementById('delivery_image_file');
    if (fileInput) {
        fileInput.click();
    }
};

function updateLiveDeliveryImage(input) {
    const filenameText = document.getElementById('delivery-filename-text');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (filenameText) {
            filenameText.innerText = 'เลือกแล้ว: ' + file.name;
            filenameText.style.color = 'var(--primary-hover)';
            filenameText.style.fontWeight = '600';
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const iframe = document.getElementById('preview_iframe');
            if (iframe) {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                const img = iframeDoc.getElementById('editable-delivery-image');
                if (img) {
                    img.src = e.target.result;
                }
            }
        };
        reader.readAsDataURL(file);
    } else {
        if (filenameText) {
            filenameText.innerText = 'ยังไม่ได้เลือกรูปใหม่ (จะใช้รูปภาพเดิมจนกว่าจะเปลี่ยน)';
            filenameText.style.color = 'var(--text-light)';
            filenameText.style.fontWeight = 'normal';
        }
    }
}

function updateLiveHero(input) {
    const files = input.files;
    const filenameText = document.getElementById('upload-filename-text');
    const previewContainer = document.getElementById('new-slide-preview-container');
    const previewImg = document.getElementById('current-hero-img');
    
    // Remove old temp slides in iframe if any exist
    const iframe = document.getElementById('preview_iframe');
    let iframeDoc = null;
    let sliderWrapper = null;
    let dotsContainer = null;
    if (iframe) {
        iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc) {
            sliderWrapper = iframeDoc.querySelector('.slider-wrapper');
            dotsContainer = iframeDoc.querySelector('.slider-dots');
            
            // Remove previous temp items
            const oldTemps = iframeDoc.querySelectorAll('[id^="temp-preview-slide"]');
            oldTemps.forEach(t => t.remove());
            
            const oldTempDots = iframeDoc.querySelectorAll('[id^="temp-preview-dot"]');
            oldTempDots.forEach(d => d.remove());
        }
    }

    if (files && files.length > 0) {
        // Update filename text and style to show count
        filenameText.innerText = 'เลือกแล้ว: ' + files.length + ' ไฟล์';
        filenameText.style.color = 'var(--primary-hover)';
        filenameText.style.fontWeight = '600';

        // Show the first selected file in left panel preview thumbnail
        const firstReader = new FileReader();
        firstReader.onload = function(e) {
            if (previewImg && previewContainer) {
                previewImg.src = e.target.result;
                previewContainer.style.display = 'flex';
            }
        };
        firstReader.readAsDataURL(files[0]);

        // Process all files to add temp slides to iframe
        if (sliderWrapper && iframeDoc) {
            let processedCount = 0;
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create a new slide div
                    const newSlide = iframeDoc.createElement('div');
                    newSlide.className = 'slide';
                    newSlide.id = 'temp-preview-slide-' + i;
                    
                    // Set image
                    const img = iframeDoc.createElement('img');
                    img.src = e.target.result;
                    newSlide.appendChild(img);
                    
                    // Append to wrapper
                    sliderWrapper.appendChild(newSlide);
                    
                    // Add a temporary dot
                    if (dotsContainer) {
                        const newDot = iframeDoc.createElement('span');
                        newDot.className = 'dot';
                        newDot.id = 'temp-preview-dot-' + i;
                        dotsContainer.appendChild(newDot);
                    }
                    
                    processedCount++;
                    
                    // Once all selected files are read, recalculate layout and slide to show
                    if (processedCount === files.length) {
                        const allSlides = sliderWrapper.querySelectorAll('.slide');
                        sliderWrapper.style.width = `${allSlides.length * 100}%`;
                        allSlides.forEach(s => {
                            s.style.width = `${100 / allSlides.length}%`;
                        });
                        
                        if (iframe.contentWindow) {
                            iframe.contentWindow.slides = iframeDoc.querySelectorAll('.hero-slider .slide');
                            iframe.contentWindow.dots = iframeDoc.querySelectorAll('.hero-slider .dot');
                            if (typeof iframe.contentWindow.showSlide === 'function') {
                                iframe.contentWindow.showSlide(allSlides.length - 1);
                            }
                        }
                    }
                };
                reader.readAsDataURL(files[i]);
            }
        }
    } else {
        filenameText.innerText = 'ยังไม่ได้เลือกไฟล์ (รูปภาพใหม่จะถูกเพิ่มเข้าสไลด์)';
        filenameText.style.color = 'var(--text-light)';
        filenameText.style.fontWeight = 'normal';
        if (previewContainer) {
            previewContainer.style.display = 'none';
        }
        
        // Reset slider wrapper in iframe back to original state
        if (sliderWrapper && iframeDoc) {
            const allSlides = sliderWrapper.querySelectorAll('.slide');
            sliderWrapper.style.width = `${allSlides.length * 100}%`;
            allSlides.forEach(s => {
                s.style.width = `${100 / allSlides.length}%`;
            });
            
            if (iframe.contentWindow) {
                iframe.contentWindow.slides = iframeDoc.querySelectorAll('.hero-slider .slide');
                iframe.contentWindow.dots = iframeDoc.querySelectorAll('.hero-slider .dot');
                if (typeof iframe.contentWindow.showSlide === 'function') {
                    iframe.contentWindow.showSlide(0);
                }
            }
        }
    }
}

function resetCustomizer() {
    // Reload iframe to restore original settings from DB
    const iframe = document.getElementById('preview_iframe');
    if (iframe) {
        iframe.src = iframe.src;
    }
    
    // Restore inputs to default values
    const originalBg = '<?= htmlspecialchars($theme_bg_color) ?>';
    const bgTxt = document.getElementById('bg_color_text');
    if (bgTxt) { bgTxt.value = originalBg; bgTxt.dispatchEvent(new Event('input')); }
    
    const originalPrimary = '<?= htmlspecialchars($theme_primary_color) ?>';
    const prTxt = document.getElementById('primary_color_text');
    if (prTxt) { prTxt.value = originalPrimary; prTxt.dispatchEvent(new Event('input')); }
    
    const originalText = '<?= htmlspecialchars($theme_text_color) ?>';
    const txTxt = document.getElementById('text_color_text');
    if (txTxt) { txTxt.value = originalText; txTxt.dispatchEvent(new Event('input')); }
    
    const originalMuted = '<?= htmlspecialchars($theme_muted_text_color) ?>';
    const mtTxt = document.getElementById('muted_color_text');
    if (mtTxt) { mtTxt.value = originalMuted; mtTxt.dispatchEvent(new Event('input')); }
    
    document.getElementById('hero_image_input').value = '';
    document.getElementById('logo_text_input').value = '<?= addslashes($theme_logo_text) ?>';
    const logoPanel = document.getElementById('logo_text_panel_input');
    if (logoPanel) { logoPanel.value = '<?= addslashes($theme_logo_text) ?>'; }
    
    document.getElementById('hero_h2_input').value = '<?= addslashes($theme_hero_h2) ?>';
    document.getElementById('hero_h1_input').value = '<?= addslashes($theme_hero_h1) ?>';
    document.getElementById('hero_p_input').value = '<?= addslashes($theme_hero_p) ?>';
    
    document.getElementById('f1_icon_input').value = '<?= addslashes($theme_f1_icon) ?>';
    document.getElementById('f1_title_input').value = '<?= addslashes($theme_f1_title) ?>';
    document.getElementById('f1_desc_input').value = '<?= addslashes($theme_f1_desc) ?>';
    
    document.getElementById('f2_icon_input').value = '<?= addslashes($theme_f2_icon) ?>';
    document.getElementById('f2_title_input').value = '<?= addslashes($theme_f2_title) ?>';
    document.getElementById('f2_desc_input').value = '<?= addslashes($theme_f2_desc) ?>';
    
    document.getElementById('f3_icon_input').value = '<?= addslashes($theme_f3_icon) ?>';
    document.getElementById('f3_title_input').value = '<?= addslashes($theme_f3_title) ?>';
    document.getElementById('f3_desc_input').value = '<?= addslashes($theme_f3_desc) ?>';
    document.getElementById('menu_title_input').value = '<?= addslashes($theme_menu_title) ?>';
    document.getElementById('menu_subtitle_input').value = '<?= addslashes($theme_menu_subtitle) ?>';
    
    document.getElementById('ann_icon_input').value = '<?= addslashes($theme_announcement_icon) ?>';
    document.getElementById('ann_text_input').value = '<?= addslashes($theme_announcement_text) ?>';
    document.getElementById('delivery_h2_input').value = '<?= addslashes($theme_delivery_h2) ?>';
    document.getElementById('delivery_p_input').value = '<?= addslashes($theme_delivery_p) ?>';
    document.getElementById('delivery_phone_input').value = '<?= addslashes($theme_delivery_phone) ?>';
    document.getElementById('delivery_line_input').value = '<?= addslashes($theme_delivery_line) ?>';
    document.getElementById('delivery_hours_input').value = '<?= addslashes($theme_delivery_hours) ?>';
    document.getElementById('delivery_btn_input').value = '<?= addslashes($theme_delivery_btn) ?>';
    document.getElementById('footer_desc_input').value = '<?= addslashes($theme_footer_desc) ?>';
    document.getElementById('f_menu_title_input').value = '<?= addslashes($theme_f_menu_title) ?>';
    document.getElementById('f_menu_l1_input').value = '<?= addslashes($theme_f_menu_l1) ?>';
    document.getElementById('f_menu_l2_input').value = '<?= addslashes($theme_f_menu_l2) ?>';
    document.getElementById('f_menu_l3_input').value = '<?= addslashes($theme_f_menu_l3) ?>';
    document.getElementById('f_menu_l4_input').value = '<?= addslashes($theme_f_menu_l4) ?>';
    
    document.getElementById('f_branch_title_input').value = '<?= addslashes($theme_f_branch_title) ?>';
    document.getElementById('f_branch_l1_input').value = '<?= addslashes($theme_f_branch_l1) ?>';
    document.getElementById('f_branch_l2_input').value = '<?= addslashes($theme_f_branch_l2) ?>';
    document.getElementById('f_branch_l3_input').value = '<?= addslashes($theme_f_branch_l3) ?>';
    document.getElementById('f_branch_l4_input').value = '<?= addslashes($theme_f_branch_l4) ?>';
    
    document.getElementById('f_contact_title_input').value = '<?= addslashes($theme_f_contact_title) ?>';
    document.getElementById('f_contact_l1_input').value = '<?= addslashes($theme_f_contact_l1) ?>';
    document.getElementById('f_contact_l2_input').value = '<?= addslashes($theme_f_contact_l2) ?>';
    document.getElementById('f_contact_l3_input').value = '<?= addslashes($theme_f_contact_l3) ?>';
    document.getElementById('f_contact_l4_input').value = '<?= addslashes($theme_f_contact_l4) ?>';
    
    document.getElementById('f_copyright_input').value = '<?= addslashes($theme_f_copyright) ?>';
    document.getElementById('f_tagline_input').value = '<?= addslashes($theme_f_tagline) ?>';
    
    // Panel inputs
    if (document.getElementById('f_menu_title_panel')) { document.getElementById('f_menu_title_panel').value = '<?= addslashes($theme_f_menu_title) ?>'; }
    if (document.getElementById('f_menu_l1_panel')) { document.getElementById('f_menu_l1_panel').value = '<?= addslashes($theme_f_menu_l1) ?>'; }
    if (document.getElementById('f_menu_l2_panel')) { document.getElementById('f_menu_l2_panel').value = '<?= addslashes($theme_f_menu_l2) ?>'; }
    if (document.getElementById('f_menu_l3_panel')) { document.getElementById('f_menu_l3_panel').value = '<?= addslashes($theme_f_menu_l3) ?>'; }
    if (document.getElementById('f_menu_l4_panel')) { document.getElementById('f_menu_l4_panel').value = '<?= addslashes($theme_f_menu_l4) ?>'; }
    
    if (document.getElementById('f_branch_title_panel')) { document.getElementById('f_branch_title_panel').value = '<?= addslashes($theme_f_branch_title) ?>'; }
    if (document.getElementById('f_branch_l1_panel')) { document.getElementById('f_branch_l1_panel').value = '<?= addslashes($theme_f_branch_l1) ?>'; }
    if (document.getElementById('f_branch_l2_panel')) { document.getElementById('f_branch_l2_panel').value = '<?= addslashes($theme_f_branch_l2) ?>'; }
    if (document.getElementById('f_branch_l3_panel')) { document.getElementById('f_branch_l3_panel').value = '<?= addslashes($theme_f_branch_l3) ?>'; }
    if (document.getElementById('f_branch_l4_panel')) { document.getElementById('f_branch_l4_panel').value = '<?= addslashes($theme_f_branch_l4) ?>'; }
    
    if (document.getElementById('f_contact_title_panel')) { document.getElementById('f_contact_title_panel').value = '<?= addslashes($theme_f_contact_title) ?>'; }
    if (document.getElementById('f_contact_l1_panel')) { document.getElementById('f_contact_l1_panel').value = '<?= addslashes($theme_f_contact_l1) ?>'; }
    if (document.getElementById('f_contact_l2_panel')) { document.getElementById('f_contact_l2_panel').value = '<?= addslashes($theme_f_contact_l2) ?>'; }
    if (document.getElementById('f_contact_l3_panel')) { document.getElementById('f_contact_l3_panel').value = '<?= addslashes($theme_f_contact_l3) ?>'; }
    if (document.getElementById('f_contact_l4_panel')) { document.getElementById('f_contact_l4_panel').value = '<?= addslashes($theme_f_contact_l4) ?>'; }
    
    if (document.getElementById('f_copyright_panel')) { document.getElementById('f_copyright_panel').value = '<?= addslashes($theme_f_copyright) ?>'; }
    if (document.getElementById('f_tagline_panel')) { document.getElementById('f_tagline_panel').value = '<?= addslashes($theme_f_tagline) ?>'; }
    if (document.getElementById('footer_desc_panel')) { document.getElementById('footer_desc_panel').value = '<?= addslashes($theme_footer_desc) ?>'; }
    
    // Reset uploader display filename
    const filenameText = document.getElementById('upload-filename-text');
    filenameText.innerText = 'ยังไม่ได้เลือกไฟล์ (รูปภาพใหม่จะถูกเพิ่มเข้าสไลด์)';
    filenameText.style.color = 'var(--text-light)';
    filenameText.style.fontWeight = 'normal';
    
    // Hide panel preview thumbnail
    const previewContainer = document.getElementById('new-slide-preview-container');
    if (previewContainer) {
        previewContainer.style.display = 'none';
    }

    // Reset delivery image
    document.getElementById('delivery_image_file').value = '';
    const deliveryFilenameText = document.getElementById('delivery-filename-text');
    if (deliveryFilenameText) {
        deliveryFilenameText.innerText = 'ยังไม่ได้เลือกรูปใหม่ (จะใช้รูปภาพเดิมจนกว่าจะเปลี่ยน)';
        deliveryFilenameText.style.color = 'var(--text-light)';
        deliveryFilenameText.style.fontWeight = 'normal';
    }
}

// WYSIWYG text synchronization function called by preview iframe
window.updateLiveText = function(key, html) {
    const input = document.getElementById(key + '_input');
    if (input) {
        input.value = html;
    }
    const panelInput = document.getElementById(key + '_panel');
    if (panelInput) {
        panelInput.value = html;
    } else if (key === 'logo_text') {
        const logoPanelInput = document.getElementById('logo_text_panel_input');
        if (logoPanelInput) {
            logoPanelInput.value = html;
        }
    }
};

// Client-side instant slide deletion & local state management
let currentSlides = <?= json_encode($theme_hero_slides) ?>;

function renderSlidesList() {
    const container = document.getElementById('slides-list-container');
    const countText = document.getElementById('slides-count-text');
    if (!container) return;
    
    container.innerHTML = '';
    countText.innerText = currentSlides.length;
    
    currentSlides.forEach((slidePath, index) => {
        const item = document.createElement('div');
        item.style.cssText = "display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border-color); padding: 0.4rem 0.6rem; border-radius: var(--border-radius-sm); background-color: var(--secondary-color); margin-bottom: 0.4rem;";
        
        item.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.6rem; min-width: 0;">
                <img src="../${slidePath}" style="width: 42px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color); flex-shrink: 0;">
                <span style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">สไลด์ที่ ${index + 1}</span>
            </div>
            <button type="button" onclick="deleteSlideLocal(${index})" class="admin-btn admin-btn-secondary admin-btn-sm" style="color: #dc3545; border-color: #fecaca; background-color: #fef2f2; padding: 0.25rem 0.5rem; font-size: 0.72rem; cursor: pointer; flex-shrink: 0; line-height: 1;">ลบ</button>
        `;
        container.appendChild(item);
    });
    
    // Update hidden field value for POST submission
    const input = document.getElementById('theme_slides_input');
    if (input) {
        input.value = JSON.stringify(currentSlides);
    }
}

function deleteSlideLocal(index) {
    if (!confirm(`คุณต้องการลบรูปสไลด์ที่ ${index + 1} ใช่หรือไม่?`)) return;
    
    // Remove from array
    currentSlides.splice(index, 1);
    
    // Re-render list
    renderSlidesList();
    
    // Update iframe preview dynamically
    const iframe = document.getElementById('preview_iframe');
    if (iframe) {
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc) {
            const sliderWrapper = iframeDoc.querySelector('.slider-wrapper');
            const dotsContainer = iframeDoc.querySelector('.slider-dots');
            if (sliderWrapper) {
                // Find all slide elements and remove the selected one
                const slides = sliderWrapper.querySelectorAll('.slide');
                if (slides[index]) {
                    slides[index].remove();
                }
                
                // Re-calculate slider widths
                const remainingSlides = sliderWrapper.querySelectorAll('.slide');
                sliderWrapper.style.width = `${remainingSlides.length * 100}%`;
                remainingSlides.forEach(s => {
                    s.style.width = `${100 / remainingSlides.length}%`;
                });
                
                // Remove corresponding dot
                if (dotsContainer) {
                    const dots = dotsContainer.querySelectorAll('.dot');
                    if (dots[index]) {
                        dots[index].remove();
                    }
                }
                
                // Re-query in iframe context
                if (iframe.contentWindow) {
                    iframe.contentWindow.slides = iframeDoc.querySelectorAll('.hero-slider .slide');
                    iframe.contentWindow.dots = iframeDoc.querySelectorAll('.hero-slider .dot');
                    if (typeof iframe.contentWindow.showSlide === 'function') {
                        iframe.contentWindow.showSlide(0); // Reset slider to first slide
                    }
                }
            }
        }
    }
}

// Call on load
document.addEventListener("DOMContentLoaded", () => {
    renderSlidesList();
});
</script>

<?php
include_once 'footer.php';
?>
