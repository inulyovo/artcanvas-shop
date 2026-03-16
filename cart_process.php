<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    $_SESSION['login_error'] = '請先登入才能使用購物車功能。';
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

try {
    $pdo = getDatabaseConnection();
    $user_id = $_SESSION['user_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    // 驗證輸入
    if ($product_id <= 0 || $quantity <= 0) {
        throw new Exception('無效的商品或數量');
    }

    // 檢查商品是否存在且上架
    $check = $pdo->prepare("SELECT id, price FROM products WHERE id = ? AND status = 'active'");
    $check->execute([$product_id]);
    $product = $check->fetch();
    if (!$product) {
        throw new Exception('商品不存在或已下架');
    }

    // 檢查是否已存在於購物車
    $exists = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $exists->execute([$user_id, $product_id]);
    $row = $exists->fetch();

    if ($row) {
        // 更新數量
        $new_qty = $row['quantity'] + $quantity;
        $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->execute([$new_qty, $row['id']]);
    } else {
        // 新增商品
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->execute([$user_id, $product_id, $quantity]);
    }

    // 成功訊息
    $_SESSION['cart_message'] = '商品已成功加入購物車！';
    header("Location: cart.php");
    exit();

} catch (Exception $e) {
    $_SESSION['cart_error'] = $e->getMessage();
    header("Location: products.php");
    exit();
} catch (PDOException $e) {
    $_SESSION['cart_error'] = '系統忙碌中，請稍後再試。';
    error_log("Cart Error: " . $e->getMessage());
    header("Location: products.php");
    exit();
}
?>