<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch image path to delete the file
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($product && !empty($product['image'])) {
        if(file_exists("../" . $product['image'])) {
            unlink("../" . $product['image']);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
