<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = $input['product_id'] ?? null;

    if ($product_id) {
        $stmt = $pdo->prepare("SELECT photo FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['photo']) {
            $photo_path = $_SERVER['DOCUMENT_ROOT'] . $product['photo'];

            if (file_exists($photo_path)) {
                unlink($photo_path);
            }

            $stmt = $pdo->prepare("UPDATE products SET photo = 'photos/default_product_image.jpg' WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Photo not found or product does not have a photo']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
