<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');

    // Constrain rating
    if ($rating < 1) $rating = 1;
    if ($rating > 5) $rating = 5;

    if (!empty($customer_name) && !empty($comment)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (customer_name, rating, comment) VALUES (?, ?, ?)");
            $stmt->execute([$customer_name, $rating, $comment]);
            header("Location: index.php?review_submit=success#reviews-section");
            exit;
        } catch (PDOException $e) {
            // Log error
        }
    }
}
header("Location: index.php?review_submit=error#reviews-section");
exit;
