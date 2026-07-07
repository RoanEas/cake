<?php
require_once 'config.php';

// Unset only customer session variables
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['user_email']);
unset($_SESSION['user_phone']);
unset($_SESSION['user_address']);

// Redirect to home page
header("Location: index.php");
exit;
?>
