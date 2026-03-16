<?php
session_start();

// 檢查登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die('無效使用者');
}

require_once 'config/database.php';

try {
    $pdo = getDatabaseConnection();
    
    // 取得該使用者的所有訂單（按時間倒序）
    $stmt = $pdo->prepare("
        SELECT id, total_amount, shipping_fee, discount, status, created_at
        FROM orders 
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 取得每筆訂單的商品明細
    $order_items = [];
    foreach ($orders as $order) {
        $stmt = $pdo->prepare("
            SELECT oi.quantity, oi.price, p.name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order['id']]);
        $order_items[$order['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log('訂單查詢失敗: ' . $e->getMessage());
    $orders = [];
    $order_items = [];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>我的訂單 | ArtCanvas 電繪板專賣店</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        #fixed-bg { display: none; }
        .orders-section {
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .orders-header {
            background: var(--primary);
            color: white;
            padding: 1.8rem 2rem;
            text-align: center;
        }
        .orders-header h1 {
            font-size: 1.8rem;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .no-orders {
            text-align: center;
            padding: 3rem 1rem;
            color: #666;
        }
        .order-card {
            border-bottom: 1px solid #eee;
            padding: 1.8rem 2rem;
        }
        .order-card:last-child {
            border-bottom: none;
        }
        .order-summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
            gap: 0.8rem;
        }
        .order-id {
            font-weight: bold;
            color: var(--primary-dark);
        }
        .order-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .order-items {
            margin: 1.2rem 0;
            padding-left: 1.5rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }
        .order-totals {
            display: flex;
            justify-content: flex-end;
            gap: 1.5rem;
            margin-top: 1rem;
            font-size: 1.05rem;
        }
        .cancel-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .cancel-btn:hover {
            background: #c82333;
        }
        @media (max-width: 768px) {
            .order-summary, .order-totals {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .order-totals {
                margin-top: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- 頁首 -->
    <header>
        <div class="logo">
            <a href="index.php"><img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌"></a>
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php#home">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="member.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
    </header>

    <section class="orders-section">
        <div class="orders-container">
            <div class="orders-header">
                <h1>我的訂單紀錄</h1>
            </div>

            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <p>您尚未有訂單紀錄</p>
                    <a href="products.php" style="display: inline-block; margin-top: 1rem; color: var(--primary); text-decoration: underline;">前往選購商品</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-summary">
                            <div>
                                <span class="order-id">訂單編號：<?php echo htmlspecialchars($order['id']); ?></span><br>
                                <small>下單時間：<?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div>
                                <span class="order-status status-<?php echo htmlspecialchars($order['status']); ?>">
                                    <?php
                                    $status_text = [
                                        'pending' => '待付款',
                                        'paid' => '已付款',
                                        'shipped' => '已出貨',
                                        'completed' => '已完成',
                                        'cancelled' => '已取消'
                                    ];
                                    echo $status_text[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-items">
                            <?php foreach ($order_items[$order['id']] as $item): ?>
                                <div class="order-item">
                                    <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                                    <span>NT$ <?php echo number_format($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-totals">
                            <div>
                                <div>商品總額：NT$ <?php echo number_format($order['total_amount'] + $order['shipping_fee'] - $order['discount']); ?></div>
                                <div>運費：<?php echo ($order['shipping_fee'] == 0) ? '免運費' : 'NT$ ' . $order['shipping_fee']; ?></div>
                                <div>優惠折扣：- NT$ <?php echo number_format($order['discount']); ?></div>
                            </div>
                            <div style="text-align: right; font-weight: bold;">
                                實付總額：<br>NT$ <?php echo number_format($order['total_amount']); ?>
                            </div>
                        </div>

                        <?php if ($order['status'] === 'pending'): ?>
                            <form method="POST" action="cancel_order.php" style="margin-top: 1rem;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" class="cancel-btn" onclick="return confirm('確定要取消此訂單嗎？')">
                                    取消訂單
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="logo">
            <img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌" />
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php#home">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="member.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店</p>
    </footer>

    <script>
        const header = document.querySelector("header");
        window.addEventListener("scroll", () => {
            header.style.boxShadow = window.scrollY > 0 
                ? "0 4px 12px rgba(0, 0, 0, 0.15)" 
                : "none";
        });
    </script>
</body>
</html>