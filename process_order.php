<?php
require_once 'config.php';

// Redirect to login if user session is not set
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php&require_login=1");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $_POST['customer_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $details = $_POST['details'] ?? '';
    $cart_data = $_POST['cart_data'] ?? '';
    $total_price = $_POST['total_price'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? 'cod';

    // Decode cart data to verify and prepare line message
    $items = json_decode($cart_data, true);

    if(!empty($items) && is_array($items)) {
        
        $verification_error = '';
        $trans_ref = null;
        $payment_slip = null;
        $status = 'pending';

        if ($payment_method === 'qrcode') {
            if (!isset($_FILES['payment_slip']) || $_FILES['payment_slip']['error'] !== UPLOAD_ERR_OK) {
                header("Location: checkout.php?order=invalid_slip&message=" . urlencode("กรุณาแนบไฟล์รูปภาพสลิปโอนเงิน"));
                exit;
            }
            
            // Save file
            $slips_dir = 'uploads/slips/';
            if (!is_dir($slips_dir)) {
                mkdir($slips_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION));
            // Validate extension
            $allowed_exts = ['png', 'jpg', 'jpeg'];
            if (!in_array($file_ext, $allowed_exts)) {
                header("Location: checkout.php?order=invalid_slip&message=" . urlencode("ประเภทไฟล์ไม่ถูกต้อง รองรับเฉพาะรูปภาพ PNG, JPG, JPEG เท่านั้น"));
                exit;
            }

            $unique_filename = 'slip_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $target_file = $slips_dir . $unique_filename;
            
            if (move_uploaded_file($_FILES['payment_slip']['tmp_name'], $target_file)) {
                $payment_slip = $unique_filename;
                
                // Read client-scanned QR code data if present
                $qr_code_data = trim($_POST['qr_code_data'] ?? '');
                
                // Auto validation if Slip API Key is configured
                if ($slip_verify_mode !== 'manual' && !empty($slip_api_key)) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    
                    // Format API key to include Bearer if header name is Authorization
                    $auth_header_value = $slip_api_key;
                    if (strtolower($slip_api_header) === 'authorization') {
                        if (stripos($auth_header_value, 'Bearer ') !== 0) {
                            $auth_header_value = 'Bearer ' . $auth_header_value;
                        }
                    }
                    
                    // Detect if this is a Queue-based API endpoint
                    $is_queue_api = (strpos($slip_api_url, '/api/queue/') !== false || strpos($slip_api_url, '/queue/') !== false);
                    
                    // Generate dynamic callback URL for webhooks
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $callback_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/slip2go_callback.php';
                    $callback_url = str_replace('//slip2go_callback.php', '/slip2go_callback.php', $callback_url);
                    
                    if (!empty($qr_code_data) && $slip_verify_mode === 'slip2go') {
                        // QR Code Payload verification mode
                        curl_setopt($ch, CURLOPT_URL, $slip_api_url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        
                        // Support both nested payload.qrCode and flat qrCode parameters for max compatibility
                        $payload = [
                            'qrCode' => $qr_code_data,
                            'payload' => [
                                'qrCode' => $qr_code_data
                            ]
                        ];
                        if ($is_queue_api) {
                            $payload['callbackUrl'] = $callback_url;
                        }
                        
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                        
                        $headers = [
                            "Content-Type: application/json",
                            $slip_api_header . ": " . $auth_header_value
                        ];
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    } else {
                        // Image upload verification mode (fallback)
                        // If user put qr-code/info but we are uploading an image, automatically swap to qr-image/info
                        $target_url = $slip_api_url;
                        if (strpos($target_url, 'qr-code/info') !== false) {
                            $target_url = str_replace('qr-code/info', 'qr-image/info', $target_url);
                        } elseif (strpos($target_url, 'verify-slip/qr-code') !== false) {
                            $target_url = str_replace('verify-slip/qr-code', 'verify-slip/qr-image', $target_url);
                        }
                        
                        curl_setopt($ch, CURLOPT_URL, $target_url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        
                        $cfile = new CURLFile(realpath($target_file), $_FILES['payment_slip']['type'], $unique_filename);
                        $post_fields = [
                            'files' => $cfile,
                            'file' => $cfile,
                            'image' => $cfile,
                            'log' => 'true'
                        ];
                        if ($is_queue_api) {
                            $post_fields['callbackUrl'] = $callback_url;
                        }
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
                        
                        $headers = [
                            $slip_api_header . ": " . $auth_header_value
                        ];
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    }
                    
                    $response_data = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($http_code === 200 && !empty($response_data)) {
                        $res = json_decode($response_data, true);
                        
                        $is_success = false;
                        if (isset($res['success']) && ($res['success'] === true || $res['success'] === 'true')) {
                            $is_success = true;
                        } elseif (isset($res['data']['success']) && ($res['data']['success'] === true || $res['data']['success'] === 'true')) {
                            $is_success = true;
                        }
                        
                        if ($is_success) {
                            // Extract transaction reference
                            $slip_ref = '';
                            if (isset($res['data']['transRef'])) {
                                $slip_ref = $res['data']['transRef'];
                            } elseif (isset($res['transRef'])) {
                                $slip_ref = $res['transRef'];
                            }
                            
                            if ($is_queue_api) {
                                // For Queue API: skip immediate validation checks and save as pending
                                // The callback script (slip2go_callback.php) will verify details and update status/notify later
                                $status = 'pending';
                                $trans_ref = $slip_ref;
                            } else {
                                // For Synchronous API: verify details immediately
                                // Extract amount
                                $slip_amount = 0.0;
                                if (isset($res['data']['amount'])) {
                                    $slip_amount = floatval($res['data']['amount']);
                                } elseif (isset($res['amount'])) {
                                    $slip_amount = floatval($res['amount']);
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
                                }
                                
                                // Verify amount (allow minor rounding diff)
                                if (abs($slip_amount - floatval($total_price)) >= 0.01) {
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
                                
                                // Verify duplicate slip usage
                                if (empty($verification_error) && !empty($slip_ref)) {
                                    $stmt_dup = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE trans_ref = ?");
                                    $stmt_dup->execute([$slip_ref]);
                                    if ($stmt_dup->fetchColumn() > 0) {
                                        $verification_error = "สลิปนี้เคยถูกนำมาใช้งานในระบบแล้ว ไม่สามารถใช้งานซ้ำได้";
                                    }
                                }
                                
                                if (empty($verification_error)) {
                                    // Verified successfully!
                                    $status = 'confirmed';
                                    $trans_ref = $slip_ref;
                                }
                            }
                        } else {
                            $verification_error = "สแกนหลักฐานไม่สำเร็จ: " . ($res['data']['message'] ?? $res['message'] ?? 'รูปภาพไม่ใช่สลิปโอนเงิน หรือข้อมูลไม่ถูกต้อง');
                        }
                    } else {
                        // API failure -> fallback to manual checking
                        $status = 'pending';
                    }
                    
                    if (!empty($verification_error)) {
                        // Cleanup uploaded file
                        @unlink($target_file);
                        header("Location: checkout.php?order=invalid_slip&message=" . urlencode($verification_error));
                        exit;
                    }
                } else {
                    // Manual verification fallback
                    $status = 'pending';
                }
            } else {
                header("Location: checkout.php?order=invalid_slip&message=" . urlencode("ไม่สามารถบันทึกสลิปโอนเงินในเซิร์ฟเวอร์ได้"));
                exit;
            }
        } else {
            // COD
            $status = 'pending';
        }

        // Insert order into database
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, address, details, total_price, items, payment_method, payment_slip, trans_ref, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt->execute([$customer_name, $phone, $address, $details, $total_price, $cart_data, $payment_method, $payment_slip, $trans_ref, $status])) {
            
            // Send Line Bot Notification (LINE Messaging API)
            if(!empty($line_bot_token) && !empty($line_user_id)) {
                $message = "🍰 มีคำสั่งซื้อใหม่เข้ามาแล้ว!\n";
                $message .= "---------------------------\n";
                $message .= "ลูกค้า: " . $customer_name . "\n";
                $message .= "เบอร์โทร: " . $phone . "\n";
                $message .= "ที่อยู่จัดส่ง: " . $address . "\n";
                if(!empty($details)){
                    $message .= "รายละเอียดเพิ่มเติม: " . $details . "\n";
                }
                $message .= "---------------------------\n";
                $message .= "รายการสินค้า:\n";
                
                foreach ($items as $item) {
                    $item_name = $item['name'] ?? 'ไม่ระบุชื่อสินค้า';
                    $qty = $item['qty'] ?? 1;
                    $price = $item['price'] ?? 0;
                    $item_total = $price * $qty;
                    $message .= "• " . $item_name . " x " . $qty . " (฿" . number_format($item_total, 2) . ")\n";
                }
                
                $message .= "---------------------------\n";
                $message .= "ยอดรวมทั้งหมด: ฿" . number_format($total_price, 2) . "\n";
                
                // Payment Method details
                if ($payment_method === 'cod') {
                    $message .= "การชำระเงิน: เก็บเงินปลายทาง (COD) 💵\n";
                } else {
                    $message .= "การชำระเงิน: โอนเงินผ่าน QR Code 📱\n";
                    if ($status === 'confirmed') {
                        $message .= "ผลตรวจสลิป: ✅ ยืนยันยอดเงินสำเร็จอัตโนมัติ (โอนแล้วเข้าเลย)\n";
                    } else {
                        $message .= "ผลตรวจสลิป: 🔎 รอแอดมินตรวจสอบสลิปด้วยตนเอง\n";
                    }
                    // Slip image URL
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                    $slip_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/uploads/slips/' . $payment_slip;
                    $message .= "ดูรูปสลิป: " . $slip_url . "\n";
                }

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
                curl_close($ch);
            }

            header("Location: index.php?order=success");
            exit;
        }
    }
}
header("Location: index.php?order=error");
exit;
