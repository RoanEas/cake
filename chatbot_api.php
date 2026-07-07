<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get user message and history
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);
$user_message = $input['message'] ?? '';
$chat_history = $input['history'] ?? []; // Array of ['role' => 'user'/'assistant', 'content' => '...']

if (empty($user_message)) {
    echo json_encode(['reply' => 'สวัสดีค่ะ! มีอะไรให้น้องไนท์บอทช่วยเหลือเกี่ยวกับเค้กวันนี้ไหมคะ? 🍰']);
    exit;
}

// Fetch current products list from DB to feed the AI
$products_text = "";
try {
    $stmt = $pdo->query("SELECT name, price, description, category FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($products)) {
        $products_text = "รายการเค้กปัจจุบันในร้าน:\n";
        foreach ($products as $p) {
            $cat_thai = 'เค้กทั่วไป';
            if ($p['category'] === 'minimalist') $cat_thai = 'เค้กมินิมอล';
            elseif ($p['category'] === 'coconut') $cat_thai = 'เค้กมะพร้าวและผลไม้';
            elseif ($p['category'] === 'chocolate') $cat_thai = 'เค้กช็อกโกแลตฟัดจ์';
            
            $products_text .= "- " . $p['name'] . " (ราคา: ฿" . number_format($p['price'], 2) . ") - " . $p['description'] . " [หมวดหมู่: $cat_thai]\n";
        }
    } else {
        $products_text = "ขณะนี้ยังไม่มีเมนูเค้กในฐานข้อมูลหลัก\n";
    }
} catch (PDOException $e) {
    $products_text = "ไม่สามารถเชื่อมต่อฐานข้อมูลเมนูเค้กได้ชั่วคราว\n";
}

// Define System Instruction for NightBot
$system_instruction = "คุณคือ 'น้องไนท์บอท (NightBot)' ผู้ช่วยอัจฉริยะประเภทร้านค้าเบเกอรี่ออนไลน์ ของร้าน 'NightCake (ไนท์เค้ก)' ";
$system_instruction .= "คุณมีหน้าที่แนะนำเมนูเค้กวันเกิด เค้กมินิมอลครีมสดแท้ ให้ข้อมูลราคา วิธีการสั่งซื้อ และบริการจัดส่งอย่างเป็นมิตร สุภาพ มีระดับ และน่ารักเป็นกันเองในภาษาไทย ใช้คำพูดแทนตัวเองว่า 'น้องไนท์บอท' หรือ 'น้องไนท์' และลงท้ายด้วยคำว่า 'ค่ะ/นะคะ' เสมอ\n\n";

$system_instruction .= "ข้อมูลสำคัญของร้าน NightCake:\n";
$system_instruction .= "- จุดเด่น: ใช้ครีมสดแท้รสละมุน หวานน้อย อร่อยกลมกล่อม ตกแต่งประณีตสไตล์เกาหลีมินิมอล เหมาะสำหรับงานวันเกิดและวันสำคัญ เขียนหน้าเค้กฟรี!\n";
$system_instruction .= "- บริการจัดส่ง: บริการส่งเดลิเวอรี่ควบคุมอุณหภูมิทั่วกรุงเทพฯ และปริมณฑล หรือมารับเค้กเองได้ที่สาขา\n";
$system_instruction .= "- สาขายอดนิยม: สยามสแควร์, เซ็นทรัล ลาดพร้าว, เมกา บางนา, สามย่าน มิตรทาวน์\n";
$system_instruction .= "- เวลาทำการ: 09:00 น. - 20:00 น. ทุกวัน\n";
$system_instruction .= "- การติดต่อ: โทร 094-492-2299, Line ID: @NIGHTCAKE\n";
$system_instruction .= "- วิธีสั่งซื้อ: ลูกค้าสามารถเลือกเค้กที่ต้องการจากหน้าเว็บ ใส่ตะกร้าสินค้า แล้วกดดำเนินการชำระเงิน เลือกได้ทั้งแบบเก็บเงินปลายทาง (COD) และโอนเงินผ่านพร้อมเพย์ QR Code\n\n";

$system_instruction .= $products_text . "\n";
$system_instruction .= "คำแนะนำในการตอบ:\n";
$system_instruction .= "1. ตอบให้กระชับ อบอุ่น สุภาพ และใช้ emoji น่ารักๆ ที่เกี่ยวกับเค้กหรือของหวาน (เช่น 🍰, 🎂, 🍓, ✨, 💖, 🧁)\n";
$system_instruction .= "2. แนะนำเค้กที่ตรงความต้องการของลูกค้าอิงจากรายการเมนูจริงด้านบนเสมอ บอกราคาให้ชัดเจน\n";
$system_instruction .= "3. ถ้าลูกค้าถามถึงเค้กที่ไม่มีในรายการ ให้ตอบสุภาพว่าตอนนี้ยังไม่มี แต่แนะนำเมนูอื่นที่ใกล้เคียงแทน\n";
$system_instruction .= "4. หากลูกค้าถามเรื่องสั่งซื้อ ให้แนะนำให้กดสั่งผ่านหน้าเว็บเพื่อความสะดวกรวดเร็ว หรือแอดไลน์ @NIGHTCAKE เพื่อให้แอดมินดูแลเป็นพิเศษ\n";

// OpenRouter API Setup
$api_key = $chatbot_api_key;
$url = 'https://openrouter.ai/api/v1/chat/completions';

// Format message history for API
$api_messages = [
    ['role' => 'system', 'content' => $system_instruction]
];

// Append limited chat history (up to last 10 messages to keep context size clean)
$recent_history = array_slice($chat_history, -10);
foreach ($recent_history as $msg) {
    if (isset($msg['role']) && isset($msg['content'])) {
        // Map roles correctly for OpenRouter
        $role = ($msg['role'] === 'assistant') ? 'assistant' : 'user';
        $api_messages[] = ['role' => $role, 'content' => $msg['content']];
    }
}

// Append new user message
$api_messages[] = ['role' => 'user', 'content' => $user_message];

$payload = [
    'model' => 'google/gemini-2.5-flash',
    'messages' => $api_messages,
    'temperature' => 0.7,
    'max_tokens' => 800
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$headers = [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json',
    'HTTP-Referer: http://localhost/cake', // Site URL for OpenRouter ranking
    'X-Title: NightCake Assistant'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 && !empty($response)) {
    $res_data = json_decode($response, true);
    $reply = $res_data['choices'][0]['message']['content'] ?? '';
    if (!empty($reply)) {
        echo json_encode(['reply' => $reply]);
    } else {
        echo json_encode(['reply' => 'ขอโทษด้วยนะคะ น้องไนท์มึนหัวนิดหน่อย รบกวนถามอีกครั้งได้ไหมคะ? 🥺🍰']);
    }
} else {
    // Log error for debugging if needed, output standard backup message
    echo json_encode([
        'reply' => 'ขออภัยด้วยนะคะ พอดีสัญญาณขัดข้องชั่วคราว คุณลูกค้าสามารถโทรสอบถามได้ที่เบอร์ 094-492-2299 หรือแอดไลน์ @NIGHTCAKE เพื่อคุยกับพี่แอดมินตัวจริงได้เลยค่ะ! 💖🎂',
        'debug_code' => $http_code,
        'debug_response' => json_decode($response, true)
    ]);
}
exit;
