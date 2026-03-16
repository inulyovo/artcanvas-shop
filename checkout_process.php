<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403);
    die('未登入');
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(400);
    die('無效使用者');
}

require_once 'config/database.php';

try {
    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

    // 1. 取得購物車商品
    $stmt = $pdo->prepare("
        SELECT c.quantity, p.id as product_id, p.name, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = :user_id AND p.status = 'active'
    ");
    $stmt->execute(['user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        throw new Exception('購物車為空');
    }

    // 2. 計算金額（與前端一致）
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));
    $shipping = ($subtotal >= 10000) ? 0 : 80;
    $discount = 1000;
    $total = max(0, $subtotal + $shipping - $discount);

    // 3. 驗證表單資料
    $required_fields = ['recipient_name', 'phone', 'address', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception('請填寫所有必填欄位');
        }
    }

    // 4. 新增訂單主檔
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, total_amount, shipping_fee, discount, 
            recipient_name, phone, address, payment_method, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $user_id, $total, $shipping, $discount,
        $_POST['recipient_name'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['payment_method']
    ]);
    $order_id = $pdo->lastInsertId();

    // 5. 新增訂單明細
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($cart_items as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // 6. 清空購物車
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $pdo->commit();

    // 7. 跳轉至成功頁面
    $_SESSION['order_success'] = [
        'order_id' => $order_id,
        'total' => $total
    ];
    header('Location: order_success.php');
    exit;

} catch (Exception $e) {
    $pdo->rollback();
    error_log('訂單失敗: ' . $e->getMessage());
    $_SESSION['error_message'] = '訂單建立失敗：' . $e->getMessage();
    header('Location: checkout.php');
    exit;
}
?>