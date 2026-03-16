<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $pdo = getDatabaseConnection();
    
    // 取得使用者ID
    $user_id = null;
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => '請先登入']);
        exit();
    }
    
    switch ($action) {
        case 'update_quantity':
            $cart_id = (int)$input['cart_id'];
            $quantity = (int)$input['quantity'];
            
            if ($quantity < 1) {
                throw new Exception('數量必須大於0');
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE id = :cart_id AND user_id = :user_id");
            $stmt->execute([
                'quantity' => $quantity,
                'cart_id' => $cart_id,
                'user_id' => $user_id
            ]);
            break;
            
        case 'remove_item':
            $cart_id = (int)$input['cart_id'];
            $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :cart_id AND user_id = :user_id");
            $stmt->execute([
                'cart_id' => $cart_id,
                'user_id' => $user_id
            ]);
            break;
            
        case 'add_item':
            $product_id = (int)$input['product_id'];
            $quantity = (int)$input['quantity'];
            
            // 檢查商品是否存在
            $check = $pdo->prepare("SELECT id FROM products WHERE id = :product_id AND status = 'active'");
            $check->execute(['product_id' => $product_id]);
            if (!$check->fetch()) {
                throw new Exception('商品不存在');
            }
            
            // 檢查是否已存在
            $exists = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $exists->execute([
                'user_id' => $user_id,
                'product_id' => $product_id
            ]);
            $row = $exists->fetch();
            
            if ($row) {
                // 更新數量
                $new_qty = $row['quantity'] + $quantity;
                $update = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE id = :cart_id");
                $update->execute([
                    'quantity' => $new_qty,
                    'cart_id' => $row['id']
                ]);
            } else {
                // 新增商品
                $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
                $insert->execute([
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity
                ]);
            }
            break;
            
        default:
            throw new Exception('無效的操作');
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤']);
}
?>