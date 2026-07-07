<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Clear product relations first
    $stmt = $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
    $stmt->execute([$id]);
    
    // Delete the category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: categories.php");
exit;
