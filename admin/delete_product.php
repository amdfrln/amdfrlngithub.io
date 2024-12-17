<?php
require_once '../config/init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID is required']);
        exit;
    }

    $productId = $data['id'];

    try {
        $product = $db->products->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);
        
        if ($product) {
            // Hapus gambar produk
            foreach ($product->images as $image) {
                $imagePath = __DIR__ . '/../' . getImagePath($image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Hapus produk dari database
            $result = $db->products->deleteOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);

            if ($result->getDeletedCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to delete product');
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 