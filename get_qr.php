<?php
require_once 'config.php';
require_once 'promptpay_helper.php';

$amount = floatval($_GET['amount'] ?? 0);

// Load PromptPay settings
$pp_id = '';
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'promptpay_id'");
    $stmt->execute();
    $pp_id = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Ignore
}

if (!empty($pp_id)) {
    // Generate PromptPay EMVCo payload
    $payload = generate_promptpay_payload($pp_id, $amount);
    
    // Redirect to QR Server API to generate image
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($payload);
    header("Location: " . $qr_url);
    exit;
} else {
    // Fallback: Generate QR code with instructions
    $fallback_text = "Please set PromptPay ID in admin settings!";
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($fallback_text);
    header("Location: " . $qr_url);
    exit;
}
?>
