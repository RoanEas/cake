<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $archive = isset($_GET['archive']) ? intval($_GET['archive']) : 1;
    
    $stmt = $pdo->prepare("UPDATE orders SET is_archived = ? WHERE id = ?");
    $stmt->execute([$archive, $id]);
}

header("Location: index.php?archive_update=success");
exit;
