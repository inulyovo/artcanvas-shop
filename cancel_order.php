<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403);
    exit('未登入');
}

$user_id = $_SESSION['user_id'] ?? null;
$order_id = $_POST['order_id'] ?? null;

if (!$user_id || !$order_id) {
    die('無效請求');
}

require_once 'config/database.php';
try {
    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

    // 確認是該使用者的訂單且狀態為 pending
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$order_id, $user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('無法取消此訂單');
    }

    // 更新狀態
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);

    $pdo->commit();
    header("Location: orders.php?msg=cancelled");
} catch (Exception $e) {
    $pdo->rollback();
    error_log('取消訂單失敗: ' . $e->getMessage());
    header("Location: orders.php?error=1");
}
?>