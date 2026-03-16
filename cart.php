<?php
session_start();

// 資料庫連線
require_once 'config/database.php';

// 取得使用者ID（如果已登入）
$user_id = null;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $user_id = $_SESSION['user_id'] ?? null;
}

try {
    $pdo = getDatabaseConnection();

    // 從資料庫取得購物車商品（如果已登入）
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
        // 未登入時使用 session 購物車
        $cart_items = $_SESSION['cart'] ?? [];
    }

    // 計算總價
    $total_items = count($cart_items);
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    // 免運費邏輯：訂單金額滿 NT$ 10,000 才免運
    $shipping = ($subtotal >= 10000) ? 0 : 80; // 未滿 10,000 收 NT$ 80 運費
    $discount = 1000; // 預設優惠
    $total = $subtotal + $shipping - $discount;

    // 確保總計不低於 0
    $total = max(0, $total);

} catch (PDOException $e) {
    // 錯誤時使用預設購物車資料
    $cart_items = [
        [
            'cart_id' => 1,
            'product_id' => 1,
            'name' => 'Wacom Cintiq 22',
            'price' => 38500,
            'quantity' => 1,
            'image_path' => './Pictures/huion-kamvas-pro-16.jpg'
        ],
        [
            'cart_id' => 2,
            'product_id' => 2,
            'name' => 'Wacom Pro Pen 3D',
            'price' => 4200,
            'quantity' => 2,
            'image_path' => './Pictures/pexels-photo-416676.jpeg'
        ],
        [
            'cart_id' => 3,
            'product_id' => 3,
            'name' => 'Clip Studio Paint EX',
            'price' => 7900,
            'quantity' => 1,
            'image_path' => './Pictures/huion-kamvas-pro-16.jpg'
        ]
    ];
    $total_items = 3;
    $subtotal = 54800;
    $shipping = 0;
    $discount = 1000;
    $total = 53800;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>購物車 | ArtCanvas 電繪板專賣店</title>
    <meta name="description" content="檢視與管理您的電繪板購物車，享受快速結帳與專業配送服務。" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        /* 隱藏 canvas 背景 */
        #fixed-bg {
            display: none;
        }

        /* 購物車主區塊 */
        .cart-section {
            background-color: var(--light);
            padding: 4rem 1.5rem;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .cart-header h2 {
            color: var(--primary-dark);
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            letter-spacing: -0.7px;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2.5rem;
        }

        @media (max-width: 900px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
        }

        /* 購物車表格 */
        .cart-items {
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 1.8rem;
            overflow: hidden;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th {
            background-color: var(--primary);
            color: white;
            padding: 1rem 0.8rem;
            text-align: left;
            font-weight: 500;
            letter-spacing: -0.3px;
        }

        .cart-table td {
            padding: 1.4rem 0.8rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .cart-table tr:last-child td {
            border-bottom: none;
        }

        .cart-product {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .cart-product-img {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: contain;
            background: #f8f8f8;
            padding: 8px;
        }

        .cart-product-info h4 {
            color: var(--primary-dark);
            margin-bottom: 0.4rem;
            font-size: 1.3rem;
        }

        .cart-product-info p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
        }

        .cart-quantity {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            border: 1px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 50%;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: var(--primary);
            color: white;
        }

        .qty-input {
            width: 48px;
            height: 32px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'LXGW WenKai Mono TC', monospace;
        }

        .cart-price {
            font-weight: 500;
            color: var(--primary-dark);
            font-size: 1.2rem;
        }

        .cart-remove {
            color: #ff4d4d;
            background: none;
            border: none;
            cursor: pointer;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 1rem;
            transition: color 0.2s;
        }

        .cart-remove:hover {
            color: #e60000;
            text-decoration: underline;
        }

        .cart-empty {
            text-align: center;
            padding: 3rem 1rem;
        }

        .cart-empty p {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 1.5rem;
        }

        .btn-continue {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .btn-continue:hover {
            background: var(--primary-dark);
        }

        /* 總計區塊 */
        .cart-summary {
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            sticky: top 20px;
        }

        .summary-title {
            color: var(--primary-dark);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--accent);
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
            margin: 1.6rem 0;
            padding-top: 1.2rem;
            border-top: 2px solid var(--accent);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .promo-code {
            margin: 1.5rem 0;
        }

        .promo-input {
            display: flex;
            gap: 0.8rem;
            margin-top: 0.8rem;
        }

        .promo-input input {
            flex: 1;
            padding: 0.7rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'LXGW WenKai Mono TC', monospace;
        }

        .promo-input button {
            padding: 0.7rem 1rem;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .promo-input button:hover {
            background: #7a5e54;
        }

        .btn-checkout {
            display: block;
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
            margin-top: 1rem;
            letter-spacing: -0.3px;
        }

        .btn-checkout:hover {
            background: #7a5e54;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(141, 110, 99, 0.4);
        }

        .btn-checkout:active {
            transform: translateY(0);
        }

        .cart-note {
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #666;
            line-height: 1.6;
        }

        /* 推薦商品 */
        .recommendations {
            margin-top: 3rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 2rem;
        }

        .recommendations h3 {
            color: var(--primary-dark);
            font-size: 1.6rem;
            margin-bottom: 1.8rem;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .recommendation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.8rem;
        }

        .recommendation-card {
            text-align: center;
            transition: transform 0.3s;
        }

        .recommendation-card:hover {
            transform: translateY(-5px);
        }

        .recommendation-img {
            width: 180px;
            height: 150px;
            object-fit: contain;
            margin: 0 auto 1rem;
            display: block;
        }

        .recommendation-title {
            font-size: 1.1rem;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .recommendation-price {
            color: var(--primary);
            font-weight: 500;
            font-size: 1.15rem;
        }

        .btn-add-to-cart {
            display: inline-block;
            margin-top: 0.8rem;
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-add-to-cart:hover {
            background: var(--primary-dark);
        }

        /* 回應式調整 */
        @media (max-width: 768px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .cart-product {
                flex-direction: column;
                text-align: center;
                gap: 0.8rem;
            }

            .cart-product-img {
                width: 80px;
                height: 80px;
            }
        }

        @media (max-width: 650px) {
            .cart-section {
                padding: 2.5rem 1rem;
            }

            .cart-header h2 {
                font-size: 1.8rem;
            }

            .cart-table th,
            .cart-table td {
                padding: 1rem 0.5rem;
                font-size: 0.95rem;
            }

            .cart-product-img {
                width: 70px;
                height: 70px;
            }

            .cart-product-info h4 {
                font-size: 1.15rem;
            }

            .summary-total {
                font-size: 1.25rem;
            }

            .btn-checkout {
                font-size: 1.15rem;
                padding: 0.9rem;
            }
        }

        /* 圖示尺寸控制 */
        header .logo img,
        footer .logo img {
            width: 48px;
            height: auto;
            max-height: 52px;
            object-fit: contain;
            margin-right: 12px;
        }

        @media (max-width: 650px) {

            header .logo img,
            footer .logo img {
                width: 40px;
                max-height: 44px;
                margin-right: 8px;
            }

            header .logo h1,
            footer .logo h1 {
                font-size: 1.3rem;
            }
        }

        footer .logo {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
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
                <li><a href="<?php echo isset($_SESSION['user_logged_in']) ? 'member.php' : 'login.php'; ?>">會員中心</a>
                </li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
    </header>

    <!-- 購物車主內容 -->
    <section class="cart-section">
        <div class="cart-container">
            <div class="cart-header">
                <h2>您的購物車</h2>
                <p style="color: #666; margin-top: 0.5rem;">目前有 <strong><?php echo $total_items; ?></strong> 項商品</p>
            </div>

            <?php if ($total_items > 0): ?>
                <div class="cart-content">
                    <!-- 購物車商品列表 -->
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>商品</th>
                                    <th>單價</th>
                                    <th>數量</th>
                                    <th>小計</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr data-cart-id="<?php echo $item['cart_id']; ?>">
                                        <td>
                                            <div class="cart-product">
                                                <a href="products.php" class="cart-product-link">
                                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>"
                                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                        class="cart-product-img">
                                                </a>
                                                <div class="cart-product-info">
                                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                                    <p>電繪板專業設備</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="cart-price">NT$ <?php echo number_format($item['price']); ?></td>
                                        <td>
                                            <div class="cart-quantity">
                                                <button class="qty-btn minus"
                                                    data-cart-id="<?php echo $item['cart_id']; ?>">-</button>
                                                <input type="text" class="qty-input" value="<?php echo $item['quantity']; ?>"
                                                    data-cart-id="<?php echo $item['cart_id']; ?>"
                                                    data-product-id="<?php echo $item['product_id']; ?>">
                                                <button class="qty-btn plus"
                                                    data-cart-id="<?php echo $item['cart_id']; ?>">+</button>
                                            </div>
                                        </td>
                                        <td class="cart-price">NT$
                                            <?php echo number_format($item['price'] * $item['quantity']); ?>
                                        </td>
                                        <td>
                                            <button class="cart-remove"
                                                data-cart-id="<?php echo $item['cart_id']; ?>">移除</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 購物車總計 -->
                    <div class="cart-summary">
                        <h3 class="summary-title">訂單摘要</h3>

                        <div class="summary-item">
                            <span>商品總額</span>
                            <span>NT$ <?php echo number_format($subtotal); ?></span>
                        </div>

                        <div class="summary-item">
                            <span>運費</span>
                            <span>
                                <?php echo ($subtotal >= 10000) ? '免運費' : 'NT$ 80'; ?>
                            </span>
                        </div>

                        <div class="promo-code">
                            <div class="summary-item">
                                <span>優惠折扣</span>
                                <span>- NT$ <?php echo number_format($discount); ?></span>
                            </div>
                            <div class="promo-input">
                                <input type="text" placeholder="輸入優惠碼" id="promo-code">
                                <button type="button" id="apply-promo">套用</button>
                            </div>
                        </div>

                        <div class="summary-total">
                            <span>總計</span>
                            <span>NT$ <?php echo number_format($total); ?></span>
                        </div>

                        <button class="btn-checkout" id="checkout-btn">前往結帳</button>

                        <p class="cart-note">
                            💡 訂單金額滿 NT$ 10,000 即享免運費<br>
                            💡 所有商品享三年全面保固服務<br>
                            💡 結帳前可使用紅利點數折抵
                        </p>
                    </div>
                </div>

                <!-- 推薦商品 -->
                <div class="recommendations">
                    <h3>您可能需要的配件</h3>
                    <div class="recommendation-grid">
                        <div class="recommendation-card">
                            <img src="./Pictures/wacom-intuos-pro-thumb.jpg" alt="專業繪圖架" class="recommendation-img">
                            <div class="recommendation-title">專業繪圖架</div>
                            <div class="recommendation-price">NT$ 2,800</div>
                            <button class="btn-add-to-cart" data-product-id="2">加入購物車</button>
                        </div>
                        <div class="recommendation-card">
                            <img src="./Pictures/wacom-intuos-pro.jpg" alt="替換筆尖組" class="recommendation-img">
                            <div class="recommendation-title">Wacom 替換筆尖組</div>
                            <div class="recommendation-price">NT$ 750</div>
                            <button class="btn-add-to-cart" data-product-id="3">加入購物車</button>
                        </div>
                        <div class="recommendation-card">
                            <img src="./Pictures/wacom-one-14-4.webp" alt="收納包" class="recommendation-img">
                            <div class="recommendation-title">專業收納包</div>
                            <div class="recommendation-price">NT$ 1,600</div>
                            <button class="btn-add-to-cart" data-product-id="4">加入購物車</button>
                        </div>
                        <div class="recommendation-card">
                            <img src="./Pictures/xp-pen-artist-thumb.jpg" alt="防刮保護膜" class="recommendation-img">
                            <div class="recommendation-title">防刮保護膜</div>
                            <div class="recommendation-price">NT$ 450</div>
                            <button class="btn-add-to-cart" data-product-id="5">加入購物車</button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- 空購物車狀態 -->
                <div class="cart-empty">
                    <p>購物車是空的</p>
                    <a href="products.php" class="btn-continue">繼續選購商品</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 頁尾 -->
    <footer>
        <div class="logo">
            <img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌" />
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php#home">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="<?php echo isset($_SESSION['user_logged_in']) ? 'member.php' : 'login.php'; ?>">會員中心</a>
                </li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
        <section class="links">
            <a href="#" aria-label="Facebook 創作社群"><img src="./Icons/facebook.svg" alt="Facebook 創作社群" /></a>
            <a href="#" aria-label="Instagram 創作分享"><img src="./Icons/instagram.svg" alt="Instagram 創作分享" /></a>
            <a href="#" aria-label="YouTube 教學頻道"><img src="./Icons/youtube.svg" alt="YouTube 教學頻道" /></a>
            <a href="#" aria-label="Line 技術支援"><img src="./Icons/line.svg" alt="Line 技術支援" /></a>
        </section>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店 | 專業數位創作設備認證合作夥伴</p>
    </footer>

    <script>
        // 頭部陰影效果
        const header = document.querySelector("header");
        window.addEventListener("scroll", () => {
            header.style.boxShadow = window.scrollY > 0
                ? "0 4px 12px rgba(0, 0, 0, 0.15)"
                : "none";
        });

        // 數量按鈕功能（AJAX 更新）
        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', function () {
                const cartId = this.dataset.cartId;
                const input = this.parentElement.querySelector('.qty-input');
                let value = parseInt(input.value);

                if (this.textContent === '-' && value > 1) {
                    value--;
                } else if (this.textContent === '+') {
                    value++;
                }

                input.value = value;

                // AJAX 更新購物車數量
                updateCartQuantity(cartId, value);
            });
        });

        // 數量輸入框直接修改
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', function () {
                const cartId = this.dataset.cartId;
                let value = parseInt(this.value);
                if (isNaN(value) || value < 1) {
                    value = 1;
                    this.value = 1;
                }
                updateCartQuantity(cartId, value);
            });
        });

        // 移除商品功能
        document.querySelectorAll('.cart-remove').forEach(button => {
            button.addEventListener('click', function () {
                const cartId = this.dataset.cartId;
                if (confirm('確定要移除此商品嗎？')) {
                    removeCartItem(cartId);
                }
            });
        });

        // 優惠碼功能
        document.getElementById('apply-promo')?.addEventListener('click', function () {
            const code = document.getElementById('promo-code').value.trim();
            if (code) {
                alert(`優惠碼 "${code}" 已成功套用！`);
                // 這裡可以加入 AJAX 套用優惠碼的邏輯
            } else {
                alert('請輸入優惠碼');
            }
        });

        // 結帳按鈕功能
        document.getElementById('checkout-btn')?.addEventListener('click', function () {
            if (confirm('確定要前往結帳嗎？')) {
                // 這裡應導向結帳頁面
                window.location.href = 'checkout.php';
            }
        });

        // 加入推薦商品
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.btn-add-to-cart')?.forEach(button => {
                if (!button.hasAttribute('data-event-bound')) {
                    button.setAttribute('data-event-bound', 'true');
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const productId = this.dataset.productId;
                        const productName = this.closest('.recommendation-card')?.querySelector('.recommendation-title')?.textContent || '商品';

                        if (!productId) {
                            alert('無法取得商品編號');
                            return;
                        }

                        // 使用 fetch 加入購物車
                        fetch('cart_update.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'add_item',
                                product_id: parseInt(productId),
                                quantity: 1
                            })
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    alert(`${productName} 已加入購物車！`);
                                    location.reload(); // 刷新以更新購物車數量
                                } else {
                                    alert('加入失敗：' + (data.message || '未知錯誤'));
                                }
                            })
                            .catch(err => {
                                console.error('加入購物車錯誤:', err);
                                alert('網路錯誤，請稍後再試');
                            });
                    });
                }
            });
        });

        // AJAX 函數：更新購物車數量
        function updateCartQuantity(cartId, quantity) {
            fetch('cart_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_quantity',
                    cart_id: cartId,
                    quantity: quantity
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 重新載入頁面或更新總價
                        location.reload();
                    } else {
                        alert('更新失敗：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('更新失敗，請稍後再試');
                });
        }

        // AJAX 函數：移除商品
        function removeCartItem(cartId) {
            fetch('cart_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove_item',
                    cart_id: cartId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('移除失敗：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('移除失敗，請稍後再試');
                });
        }

        // AJAX 函數：加入購物車
        function addToCart(productId, quantity) {
            return fetch('cart_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add_item',
                    product_id: productId,
                    quantity: quantity
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        return true;
                    } else {
                        throw new Error(data.message);
                    }
                });
        }
    </script>
</body>

</html>