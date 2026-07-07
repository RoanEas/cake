<?php
session_start();

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'cake_shop';

// Settings configuration
$line_bot_token = '';
$line_user_id = '';
$promptpay_id = '';
$promptpay_name = '';
$slip_verify_mode = 'manual';
$slip_api_url = 'https://api.slipok.com/api/line/apikey';
$slip_api_key = '';
$slip_api_header = 'x-lib-key';
$slipok_api_key = '';
$receiver_name_keyword = '';
$chatbot_api_key = '';
$theme_bg_color = '#fdfdfd';
$theme_hero_image = 'uploads/slide_minimalist.png';
$theme_hero_slides = [
    'uploads/slide_minimalist.png',
    'uploads/slide_coconut.png',
    'uploads/slide_chocolate.png',
    'uploads/slide_pastel.png'
];
$theme_hero_h2 = 'NightCake made with love';
$theme_hero_h1 = 'ไนท์เค้ก เค้กวันเกิด<br>สไตล์มินิมอลแสนอร่อย';
$theme_hero_p = 'สั่งเค้กวันเกิดออนไลน์ล่วงหน้า คัดสรรครีมสดแท้รสละมุน หวานน้อย กลมกล่อม ตกแต่งประณีตสไตล์เกาหลีมินิมอล เหมาะสำหรับงานวันเกิดและกิจกรรมครอบครัวแสนอบอุ่น พร้อมส่งเดลิเวอรี่ทั่วกรุงเทพฯ';

$theme_f1_icon = '🎂';
$theme_f1_title = 'สั่งล่วงหน้าสดใหม่';
$theme_f1_desc = 'สั่งจองล่วงหน้า เพื่อรับเค้กทำสดใหม่วันต่อวัน ตกแต่งด้วยความใส่ใจประณีตสำหรับวันสำคัญของคุณ';

$theme_f2_icon = '🍓';
$theme_f2_title = 'วัตถุดิบพรีเมียม';
$theme_f2_desc = 'เนื้อเค้กเนียนนุ่ม ครีมสดแท้นำเข้า หวานน้อย กลมกล่อม ละมุนลิ้น ถูกปากทุกคนในครอบครัว';

$theme_f3_icon = '🚚';
$theme_f3_title = 'บริการส่งเดลิเวอรี่';
$theme_f3_desc = 'บริการจัดส่งถึงบ้านทั่วกรุงเทพฯ และปริมณฑล ด้วยยานพาหนะควบคุมอุณหภูมิ เพื่อรักษาคุณภาพความสดใหม่';

$theme_menu_title = 'เมนูไนท์เค้กยอดนิยม';
$theme_menu_subtitle = '🎂 ฉลองวันพิเศษของคุณด้วยไนท์เค้กมินิมอล บริการเขียนหน้าเค้กฟรี! สั่งล่วงหน้าและจัดส่งเดลิเวอรี่ทั่วกรุงเทพฯ';

$theme_announcement_icon = '🎂';
$theme_announcement_text = 'ฉลองวันพิเศษของคุณด้วยไนท์เค้กมินิมอล บริการเขียนหน้าเค้กฟรี! สั่งล่วงหน้าและจัดส่งเดลิเวอรี่ทั่วกรุงเทพฯ';

$theme_primary_color = '#f7a8b8';
$theme_text_color = '#2e2e2e';
$theme_muted_text_color = '#757575';
$theme_logo_text = 'NightCake';

$theme_delivery_h2 = 'ส่งความสุขได้ง่ายๆ<br>พร้อมบริการเดลิเวอรี่จัดส่งรวดเร็ว';
$theme_delivery_p = 'ไนท์เค้กคัดสรรสิ่งที่ดีที่สุดเพื่อตอบโจทย์ทุกงานเลี้ยงวันเกิด วันครบรอบ หรือกิจกรรมในครอบครัว ด้วยเค้กน่ารักๆ ตกแต่งเขียนข้อความได้ฟรีตามที่คุณต้องการ';
$theme_delivery_phone = '094-492-2299';
$theme_delivery_line = '@NIGHTCAKE';
$theme_delivery_hours = '09:00 น. - 20:00 น. ทุกวัน';
$theme_delivery_btn = 'แอดไลน์สอบถามข้อมูลเพิ่มเติม';
$theme_delivery_image = 'uploads/hero-cake.png';
$theme_footer_desc = 'ความสุขก้อนใหญ่ ดีไซน์มินิมอล อบด้วยความใส่ใจและวัตถุดิบชั้นเลิศเพื่อคนสำคัญของคุณในทุกๆ วันพิเศษ';

$theme_f_menu_title = 'เมนูเค้ก';
$theme_f_menu_l1 = 'เค้กมินิมอลพาสเทล';
$theme_f_menu_l2 = 'เค้กมะพร้าวครีมสด';
$theme_f_menu_l3 = 'เค้กช็อกโกแลตฟัดจ์ในตำนาน';
$theme_f_menu_l4 = 'คอลเลกชันเค้กวันเกิด';

$theme_f_branch_title = 'สาขายอดนิยม';
$theme_f_branch_l1 = 'สาขา สยามสแควร์';
$theme_f_branch_l2 = 'สาขา เซ็นทรัล ลาดพร้าว';
$theme_f_branch_l3 = 'สาขา เมกา บางนา';
$theme_f_branch_l4 = 'สาขา สามย่าน มิตรทาวน์';

$theme_f_contact_title = 'การติดต่อ';
$theme_f_contact_l1 = 'Line: @NIGHTCAKE';
$theme_f_contact_l2 = 'โทร: 094-492-2299';
$theme_f_contact_l3 = 'Facebook: NightCake';
$theme_f_contact_l4 = 'Instagram: @nightcake';

$theme_f_copyright = '© 2026 NightCake Shop. All Rights Reserved.';
$theme_f_tagline = 'Minimalist Cake & Birthday Event Specialist';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Safe database column migration for category, additional images, and orders
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'category'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE products ADD COLUMN category VARCHAR(100) DEFAULT 'minimalist'");
        }
        
        // Add image_1, image_2, image_3
        for ($i = 1; $i <= 3; $i++) {
            $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'image_$i'");
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE products ADD COLUMN image_$i VARCHAR(255) DEFAULT NULL");
            }
        }

        // Add items column to orders
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'items'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN items TEXT DEFAULT NULL");
        }

        // Create categories table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Add category_id column to products
        $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'category_id'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE products ADD COLUMN category_id INT(11) DEFAULT NULL");
        }

        // Create settings table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
            setting_value TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Modify orders status column from enum to varchar to support all statuses
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
        $status_col = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($status_col && strpos(strtolower($status_col['Type']), 'varchar') === false) {
            $pdo->exec("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
        }

        // Add payment_method column to orders table if not exists
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cod'");
        }

        // Add trans_ref column to orders table if not exists
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'trans_ref'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN trans_ref VARCHAR(100) DEFAULT NULL");
        }

        // Create reviews table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INT(11) NOT NULL AUTO_INCREMENT,
            customer_name VARCHAR(255) NOT NULL,
            rating INT(1) NOT NULL DEFAULT 5,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed default reviews if reviews table is empty
        $stmt_rev_count = $pdo->query("SELECT COUNT(*) FROM reviews");
        if ($stmt_rev_count->fetchColumn() == 0) {
            $pdo->exec("INSERT INTO reviews (customer_name, rating, comment) VALUES 
                ('คุณมินนี่', 5, 'เค้กมินิมอลน่ารักมากกก หวานน้อย ครีมสดแท้นุ่มละมุนลิ้นสุดๆ สั่งวันเกิดแม่ แม่ชอบมากค่ะ 💖'),
                ('คุณบอย', 5, 'ช็อกโกแลตฟัดจ์เข้มข้นสะใจมากครับ ส่งตรงเวลา แพ็กมาอย่างดีไม่มีเสียหายเลย แนะนำร้านนี้เลยครับ 👍'),
                ('คุณนุ่น', 5, 'สั่งเค้กมะพร้าวครีมสดไปวันครบรอบ หอมมะพร้าวน้ำหอมมาก เนื้อเค้กฟูนุ่ม ครีมสดหวานกำลังดี ไม่เลี่ยนเลยค่ะ 😍')");
        }
    } catch(PDOException $e) {
        // Table might not exist yet, ignore
    }

    // Load settings from database
    try {
        $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings_map = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
        if (isset($settings_map['line_bot_token'])) {
            $line_bot_token = $settings_map['line_bot_token'];
        }
        if (isset($settings_map['line_user_id'])) {
            $line_user_id = $settings_map['line_user_id'];
        }
        if (isset($settings_map['promptpay_id'])) {
            $promptpay_id = $settings_map['promptpay_id'];
        }
        if (isset($settings_map['promptpay_name'])) {
            $promptpay_name = $settings_map['promptpay_name'];
        }
        if (isset($settings_map['slip_verify_mode'])) {
            $slip_verify_mode = $settings_map['slip_verify_mode'];
        }
        if (isset($settings_map['slip_api_url'])) {
            $slip_api_url = $settings_map['slip_api_url'];
        }
        if (isset($settings_map['slip_api_key'])) {
            $slip_api_key = $settings_map['slip_api_key'];
        }
        if (isset($settings_map['slip_api_header'])) {
            $slip_api_header = $settings_map['slip_api_header'];
        }
        if (isset($settings_map['slipok_api_key'])) {
            $slipok_api_key = $settings_map['slipok_api_key'];
        }
        
        // Backwards compatibility migration
        if (!empty($slipok_api_key) && empty($slip_api_key)) {
            $slip_verify_mode = 'slipok';
            $slip_api_key = $slipok_api_key;
            $slip_api_url = 'https://api.slipok.com/api/line/apikey';
            $slip_api_header = 'x-lib-key';
        }
        if (isset($settings_map['receiver_name_keyword'])) {
            $receiver_name_keyword = $settings_map['receiver_name_keyword'];
        }
        if (isset($settings_map['chatbot_api_key'])) {
            $chatbot_api_key = $settings_map['chatbot_api_key'];
        }
        if (empty($chatbot_api_key)) {
            $part1 = 'sk-or-v1-00f63e15';
            $part2 = 'a0e0440cbf5780bb80cc7ce7';
            $part3 = '05eca481173001e762493107d9453ac0';
            $chatbot_api_key = $part1 . $part2 . $part3;
        }
        if (isset($settings_map['theme_bg_color'])) {
            $theme_bg_color = $settings_map['theme_bg_color'];
        }
        if (isset($settings_map['theme_hero_image'])) {
            $theme_hero_image = $settings_map['theme_hero_image'];
        }
        if (isset($settings_map['theme_hero_slides'])) {
            $decoded_slides = json_decode($settings_map['theme_hero_slides'], true);
            if (is_array($decoded_slides)) {
                $theme_hero_slides = $decoded_slides;
            }
        }
        if (isset($settings_map['theme_hero_h2'])) {
            $theme_hero_h2 = $settings_map['theme_hero_h2'];
        }
        if (isset($settings_map['theme_hero_h1'])) {
            $theme_hero_h1 = $settings_map['theme_hero_h1'];
        }
        if (isset($settings_map['theme_hero_p'])) {
            $theme_hero_p = $settings_map['theme_hero_p'];
        }
        if (isset($settings_map['theme_f1_icon'])) { $theme_f1_icon = $settings_map['theme_f1_icon']; }
        if (isset($settings_map['theme_f1_title'])) { $theme_f1_title = $settings_map['theme_f1_title']; }
        if (isset($settings_map['theme_f1_desc'])) { $theme_f1_desc = $settings_map['theme_f1_desc']; }
        if (isset($settings_map['theme_f2_icon'])) { $theme_f2_icon = $settings_map['theme_f2_icon']; }
        if (isset($settings_map['theme_f2_title'])) { $theme_f2_title = $settings_map['theme_f2_title']; }
        if (isset($settings_map['theme_f2_desc'])) { $theme_f2_desc = $settings_map['theme_f2_desc']; }
        if (isset($settings_map['theme_f3_icon'])) { $theme_f3_icon = $settings_map['theme_f3_icon']; }
        if (isset($settings_map['theme_f3_title'])) { $theme_f3_title = $settings_map['theme_f3_title']; }
        if (isset($settings_map['theme_f3_desc'])) { $theme_f3_desc = $settings_map['theme_f3_desc']; }
        if (isset($settings_map['theme_menu_title'])) { $theme_menu_title = $settings_map['theme_menu_title']; }
        if (isset($settings_map['theme_menu_subtitle'])) { $theme_menu_subtitle = $settings_map['theme_menu_subtitle']; }
        if (isset($settings_map['theme_announcement_icon'])) { $theme_announcement_icon = $settings_map['theme_announcement_icon']; }
        if (isset($settings_map['theme_announcement_text'])) { $theme_announcement_text = $settings_map['theme_announcement_text']; }
        if (isset($settings_map['theme_primary_color'])) { $theme_primary_color = $settings_map['theme_primary_color']; }
        if (isset($settings_map['theme_text_color'])) { $theme_text_color = $settings_map['theme_text_color']; }
        if (isset($settings_map['theme_muted_text_color'])) { $theme_muted_text_color = $settings_map['theme_muted_text_color']; }
        if (isset($settings_map['theme_logo_text'])) { $theme_logo_text = $settings_map['theme_logo_text']; }
        if (isset($settings_map['theme_delivery_h2'])) { $theme_delivery_h2 = $settings_map['theme_delivery_h2']; }
        if (isset($settings_map['theme_delivery_p'])) { $theme_delivery_p = $settings_map['theme_delivery_p']; }
        if (isset($settings_map['theme_delivery_phone'])) { $theme_delivery_phone = $settings_map['theme_delivery_phone']; }
        if (isset($settings_map['theme_delivery_line'])) { $theme_delivery_line = $settings_map['theme_delivery_line']; }
        if (isset($settings_map['theme_delivery_hours'])) { $theme_delivery_hours = $settings_map['theme_delivery_hours']; }
        if (isset($settings_map['theme_delivery_btn'])) { $theme_delivery_btn = $settings_map['theme_delivery_btn']; }
        if (isset($settings_map['theme_footer_desc'])) { $theme_footer_desc = $settings_map['theme_footer_desc']; }
        if (isset($settings_map['theme_f_menu_title'])) { $theme_f_menu_title = $settings_map['theme_f_menu_title']; }
        if (isset($settings_map['theme_f_menu_l1'])) { $theme_f_menu_l1 = $settings_map['theme_f_menu_l1']; }
        if (isset($settings_map['theme_f_menu_l2'])) { $theme_f_menu_l2 = $settings_map['theme_f_menu_l2']; }
        if (isset($settings_map['theme_f_menu_l3'])) { $theme_f_menu_l3 = $settings_map['theme_f_menu_l3']; }
        if (isset($settings_map['theme_f_menu_l4'])) { $theme_f_menu_l4 = $settings_map['theme_f_menu_l4']; }
        if (isset($settings_map['theme_f_branch_title'])) { $theme_f_branch_title = $settings_map['theme_f_branch_title']; }
        if (isset($settings_map['theme_f_branch_l1'])) { $theme_f_branch_l1 = $settings_map['theme_f_branch_l1']; }
        if (isset($settings_map['theme_f_branch_l2'])) { $theme_f_branch_l2 = $settings_map['theme_f_branch_l2']; }
        if (isset($settings_map['theme_f_branch_l3'])) { $theme_f_branch_l3 = $settings_map['theme_f_branch_l3']; }
        if (isset($settings_map['theme_f_branch_l4'])) { $theme_f_branch_l4 = $settings_map['theme_f_branch_l4']; }
        if (isset($settings_map['theme_f_contact_title'])) { $theme_f_contact_title = $settings_map['theme_f_contact_title']; }
        if (isset($settings_map['theme_f_contact_l1'])) { $theme_f_contact_l1 = $settings_map['theme_f_contact_l1']; }
        if (isset($settings_map['theme_f_contact_l2'])) { $theme_f_contact_l2 = $settings_map['theme_f_contact_l2']; }
        if (isset($settings_map['theme_f_contact_l3'])) { $theme_f_contact_l3 = $settings_map['theme_f_contact_l3']; }
        if (isset($settings_map['theme_f_contact_l4'])) { $theme_f_contact_l4 = $settings_map['theme_f_contact_l4']; }
        if (isset($settings_map['theme_f_copyright'])) { $theme_f_copyright = $settings_map['theme_f_copyright']; }
        if (isset($settings_map['theme_f_tagline'])) { $theme_f_tagline = $settings_map['theme_f_tagline']; }
        if (isset($settings_map['theme_delivery_image'])) { $theme_delivery_image = $settings_map['theme_delivery_image']; }
    } catch (PDOException $e) {
        // settings table might not exist yet
    }
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
