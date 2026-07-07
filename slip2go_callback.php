<?php
require_once 'config.php';

// Retrieve the request body (JSON)
$input_data = file_get_contents('php://input');
$res = json_decode($input_data, true);

if (!$res) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

// Write to a log file for debugging
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}
file_put_contents('uploads/slip2go_callback_log.txt', date('Y-m-d H:i:s') . " - Payload: " . $input_data . "\n", FILE_APPEND);

$is_success = false;
if (isset($res['success']) && ($res['success'] === true || $res['success'] === 'true')) {
    $is_success = true;
} elseif (isset($res['data']['success']) && ($res['data']['success'] === true || $res['data']['success'] === 'true')) {
    $is_success = true;
}

if ($is_success) {
    // Extract amount
    $slip_amount = 0.0;
    if (isset($res['data']['amount'])) {
        $slip_amount = floatval($res['data']['amount']);
    } elseif (isset($res['amount'])) {
        $slip_amount = floatval($res['amount']);
    }
    
    // Extract transaction reference
    $slip_ref = '';
    if (isset($res['data']['transRef'])) {
        $slip_ref = $res['data']['transRef'];
    } elseif (isset($res['transRef'])) {
        $slip_ref = $res['transRef'];
    }
    
    // Extract receiver name
    $recv_name = '';
    if (isset($res['data']['receiver']['displayName'])) {
        $recv_name = $res['data']['receiver']['displayName'];
    } elseif (isset($res['data']['receiver']['name'])) {
        $recv_name = $res['data']['receiver']['name'];
    } elseif (isset($res['receiver']['name'])) {
        $recv_name = $res['receiver']['name'];
    } elseif (isset($res['receiver']['displayName'])) {
        $recv_name = $res['receiver']['displayName'];
    } elseif (isset($res['receiverName'])) {
        $recv_name = $res['receiverName'];
    }

    if (!empty($slip_ref)) {
        // Look up the order with this transaction reference and status 'pending'
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE trans_ref = ? AND status = 'pending'");
        $stmt->execute([$slip_ref]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order_id = $order['id'];
            $total_price = floatval($order['total_price']);
            $customer_name = $order['customer_name'];
            $phone = $order['phone'];

            $verification_error = '';

            // Verify amount
            if (abs($slip_amount - $total_price) >= 0.01) {
                $verification_error = "ยอดเงินโอนบนสลิปไม่ตรงกับยอดออเดอร์ (สลิปมียอด ฿" . number_format($slip_amount, 2) . " แต่ยอดออเดอร์คือ ฿" . number_format($total_price, 2) . ")";
            }

            // Verify receiver name
            if (empty($verification_error) && !empty($receiver_name_keyword)) {
                $recv_display_upper = strtoupper($recv_name);
                $kw = strtoupper($receiver_name_keyword);
                if (strpos($recv_display_upper, $kw) === false) {
                    $verification_error = "ชื่อบัญชีผู้รับโอนบนสลิปไม่ตรงกับร้านค้า";
                }
            }

            if (empty($verification_error)) {
                // Update order status to confirmed
                $stmt_update = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?");
                $stmt_update->execute([$order_id]);

                // Send LINE notification to admin that payment is completed!
                if (!empty($line_bot_token) && !empty($line_user_id)) {
                    $message = "✅ ยืนยันยอดเงินสำเร็จอัตโนมัติ (ผ่านระบบ Queue Callback) 🎉\n";
                    $message .= "---------------------------\n";
                    $message .= "คำสั่งซื้อ #" . $order_id . "\n";
                    $message .= "ลูกค้า: " . $customer_name . "\n";
                    $message .= "เบอร์โทร: " . $phone . "\n";
                    $message .= "ยอดโอนตรงถ้วน: ฿" . number_format($slip_amount, 2) . "\n";
                    $message .= "เลขอ้างอิง: " . $slip_ref . "\n";

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

                echo json_encode(['success' => true, 'message' => 'Order verified and confirmed successfully']);
                exit;
            } else {
                file_put_contents('uploads/slip2go_callback_log.txt', date('Y-m-d H:i:s') . " - Verification error for Order #$order_id: $verification_error\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => $verification_error]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Pending order with trans_ref not found']);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Slip verification failed or transaction not approved']);
?>
