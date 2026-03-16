<?php
session_start();
if (!isset($_SESSION['order_success'])) {
    header('Location: index.php');
    exit;
}
$order = $_SESSION['order_success'];
unset($_SESSION['order_success']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>訂單成立</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 5rem; }
        .success { color: green; font-size: 2rem; margin: 2rem 0; }
    </style>
</head>
<body>
    <h1> 訂單成立！</h1>
    <div class="success">
        感謝您的購買！<br>
        訂單編號：<?php echo htmlspecialchars($order['order_id']); ?><br>
        總金額：NT$ <?php echo number_format($order['total']); ?>
    </div>
    <p>我們將盡快為您處理訂單。</p>
    <a href="index.php">返回首頁</a>
</body>
</html>