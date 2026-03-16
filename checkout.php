<?php
session_start();

// 未登入強制跳轉（可選，但建議）
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

require_once 'config/database.php';

try {
    $pdo = getDatabaseConnection();
    
    // 取得購物車商品（與 cart.php 邏輯一致）
    $cart_items = [];
    if ($user_id) {
        $stmt = $pdo->prepare("
            SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image_path
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :user_id AND p.status = 'active'
            ORDER BY c.added_at DESC
        ");
        $stmt->execute(['user_id' => $user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // 若允許未登入結帳，需處理 session 購物車（此處略，因已強制登入）
        $cart_items = [];
    }

    if (empty($cart_items)) {
        header('Location: cart.php');
        exit;
    }

    // 計算金額
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));
    $shipping = ($subtotal >= 10000) ? 0 : 80;
    $discount = 1000; // 可從 session 或優惠碼取得
    $total = max(0, $subtotal + $shipping - $discount);

} catch (PDOException $e) {
    die('資料庫錯誤：' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>結帳 | ArtCanvas 電繪板專賣店</title>
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        /* 隱藏 canvas 背景 */
        #fixed-bg { display: none; }

        .checkout-section {
            background-color: var(--light);
            padding: 3rem 1.5rem;
        }
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .checkout-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .checkout-header h2 {
            color: var(--primary-dark);
            font-size: clamp(1.8rem, 4vw, 2.5rem);
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2.5rem;
        }
        @media (max-width: 900px) {
            .checkout-content { grid-template-columns: 1fr; }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-dark);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 1rem;
        }
        .form-row {
            display: flex;
            gap: 1rem;
        }
        .form-row .form-group {
            flex: 1;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 1.05rem;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin: 1.5rem 0;
            padding-top: 1rem;
            border-top: 2px solid var(--accent);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .btn-place-order {
            width: 100%;
            padding: 1rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.25rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: -0.3px;
        }
        .btn-place-order:hover {
            background: #7a5e54;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(141, 110, 99, 0.4);
        }

        /* 商品明細樣式（簡化版） */
        .order-items {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
        }
        .order-item:last-child { margin-bottom: 0; }
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

    <section class="checkout-section">
        <div class="checkout-container">
            <div class="checkout-header">
                <h2>確認訂單</h2>
                <p style="color: #666;">請填寫配送與付款資訊</p>
            </div>

            <div class="checkout-content">
                <!-- 左側：表單 -->
                <div>
                    <form id="checkoutForm" action="checkout_process.php" method="POST">
                        <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">配送資訊</h3>
                        
                        <div class="form-group">
                            <label for="recipient_name">收件人姓名 *</label>
                            <input type="text" id="recipient_name" name="recipient_name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">手機號碼 *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required pattern="09\d{8}">
                        </div>

                        <div class="form-group">
                            <label for="address">配送地址 *</label>
                            <textarea id="address" name="address" rows="3" class="form-control" required></textarea>
                        </div>

                        <h3 style="margin: 2rem 0 1.5rem; color: var(--primary-dark);">付款方式</h3>
                        <div class="form-group">
                            <select name="payment_method" class="form-control" required>
                                <option value="">請選擇付款方式</option>
                                <option value="credit_card">信用卡</option>
                                <option value="atm">ATM 轉帳</option>
                                <option value="cod">貨到付款</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-place-order">確認並送出訂單</button>
                    </form>
                </div>

                <!-- 右側：訂單摘要 -->
                <div>
                    <div class="order-items">
                        <h4 style="margin-bottom: 1rem; color: var(--primary-dark);">商品明細</h4>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                                <span>NT$ <?php echo number_format($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-summary">
                        <div class="summary-item">
                            <span>商品總額</span>
                            <span>NT$ <?php echo number_format($subtotal); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>運費</span>
                            <span><?php echo ($shipping === 0) ? '免運費' : 'NT$ ' . $shipping; ?></span>
                        </div>
                        <div class="summary-item">
                            <span>優惠折扣</span>
                            <span>- NT$ <?php echo number_format($discount); ?></span>
                        </div>
                        <div class="summary-total">
                            <span>總計</span>
                            <span>NT$ <?php echo number_format($total); ?></span>
                        </div>
                    </div>
                </div>
            </div>
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
        // 表單驗證（可選）
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            if (!/^09\d{8}$/.test(phone)) {
                alert('請輸入正確的手機號碼（09開頭共10碼）');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>