<?php
/**
 * PromptPay EMVCo QR Code Payload Generator
 */

if (!function_exists('promptpay_crc16')) {
    function promptpay_crc16($data) {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ ($x)) & 0xFFFF;
        }
        return sprintf('%04X', $crc);
    }
}

if (!function_exists('generate_promptpay_payload')) {
    function generate_promptpay_payload($target, $amount) {
        // Clean non-digits
        $target = preg_replace('/\D/', '', $target);
        
        $pp_info = '';
        if (strlen($target) === 13) {
            // National ID / Tax ID
            $pp_info = '0016A000000677010111' . '0213' . $target;
        } else {
            // Phone number. Convert 08x to international format 00668x
            if (strpos($target, '0') === 0) {
                $target = '0066' . substr($target, 1);
            }
            $target = str_pad($target, 13, '0', STR_PAD_LEFT);
            $pp_info = '0016A000000677010111' . '0113' . $target;
        }
        
        $merchant_info = '29' . sprintf('%02d', strlen($pp_info)) . $pp_info;
        
        $payload = '000201'; // Version
        $payload .= '010211'; // Type: 11 for static/reusable
        $payload .= $merchant_info;
        $payload .= '5303764'; // Currency code (THB)
        
        if ($amount > 0) {
            $amount_str = number_format($amount, 2, '.', '');
            $payload .= '54' . sprintf('%02d', strlen($amount_str)) . $amount_str;
        }
        
        $payload .= '5802TH'; // Country code
        $payload .= '6304'; // CRC16 tag
        
        $payload .= promptpay_crc16($payload);
        return $payload;
    }
}
?>
