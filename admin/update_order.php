<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if(isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    
    // Only allow valid statuses
    $allowed = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
    if(in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Send Line Bot Notification (LINE Messaging API) when status changes
        if(!empty($line_bot_token) && !empty($line_user_id)) {
            $labels = [
                'pending'   => '⏳ ปรับสถานะเป็นรอตรวจสอบ',
                'confirmed' => '✅ ยืนยันคำสั่งซื้อแล้ว',
                'shipping'  => '🚚 กำลังจัดส่ง',
                'completed' => '🎉 จัดส่งสำเร็จ',
                'cancelled' => '❌ ยกเลิกคำสั่งซื้อ'
            ];

            if(isset($labels[$status])) {
                // Fetch order details
                $stmt2 = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $stmt2->execute([$id]);
                $order = $stmt2->fetch(PDO::FETCH_ASSOC);

                if($order) {
                    $message = $labels[$status] . "\n";
                    $message .= "---------------------------\n";
                    $message .= "คำสั่งซื้อ #" . $order['id'] . "\n";
                    $message .= "ลูกค้า: " . $order['customer_name'] . "\n";
                    $message .= "เบอร์โทร: " . $order['phone'] . "\n";
                    $message .= "ยอดรวม: ฿" . number_format($order['total_price'], 2) . "\n";

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
                    curl_exec($ch);
                    curl_close($ch);
                }
            }
        }
    }
}

header("Location: index.php?status_update=success");
exit;
