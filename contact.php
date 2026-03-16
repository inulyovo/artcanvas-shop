<?php
session_start();

// 檢查是否已登入（用於預填資料）
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$username = $is_logged_in ? ($_SESSION['username'] ?? '') : '';
$email = $is_logged_in ? ($_SESSION['email'] ?? '') : '';

// 錯誤/成功訊息
$contact_error = $_SESSION['contact_error'] ?? null;
$contact_success = $_SESSION['contact_success'] ?? null;
$contact_data = $_SESSION['contact_data'] ?? [];

// 清除 session 訊息
unset($_SESSION['contact_error'], $_SESSION['contact_success']);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>技術支援 | ArtCanvas 電繪板專賣店</title>
    <meta name="description" content="有任何技術問題或購買諮詢？歡迎透過電話、Line 或表單與我們聯繫。專業技術團隊將為您提供即時支援！" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        #fixed-bg { display: none; }
        .contact-section {
            background-color: var(--light);
            padding: 4rem 1.5rem;
        }
        .contact-content {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 2.5rem;
        }
        .contact-info, .contact-form {
            flex: 1 1 300px;
            background: white;
            padding: 2.2rem;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        .contact-info h2, .contact-form h2 {
            color: var(--primary);
            margin-bottom: 1.6rem;
            font-size: 1.7rem;
            letter-spacing: -0.5px;
        }
        .contact-item {
            margin-bottom: 1.4rem;
            line-height: 1.7;
        }
        .contact-item h4 {
            color: var(--secondary);
            margin-bottom: 0.4rem;
            font-size: 1.25rem;
        }
        
        /* ====== 新增：訊息提示樣式 ====== */
        .message-alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 0.95rem;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        /* ================================= */

        .contact-form input,
        .contact-form textarea,
        .contact-form select {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.2rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 1.05rem;
            box-sizing: border-box;
        }
        .contact-form textarea {
            min-height: 120px;
            resize: vertical;
        }
        .contact-form button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 8px;
            font-size: 1.15rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        .contact-form button:hover {
            background-color: #1e3a29;
        }
        @media (max-width: 650px) {
            .contact-section { padding: 2.5rem 1rem; }
            .contact-info, .contact-form {
                padding: 1.8rem;
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
        
        @media (min-width: 900px) {
            header .logo img,
            footer .logo img {
                width: 56px;
                max-height: 60px;
            }
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
    <header>
        <div class="logo">
            <a href="index.php"><img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌"></a>
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="login.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
    </header>

    <section class="contact-section">
        <div class="contact-content">
            <div class="contact-info">
                <h2>技術支援資訊</h2>
                <div class="contact-item">
                    <h4>服務時間</h4>
                    <p>週一至週日 09:00 - 22:00<br>（技術支援 24 小時線上回覆）</p>
                </div>
                <div class="contact-item">
                    <h4>客服專線</h4>
                    <p>02-8765-4321<br>（技術支援按 1，銷售諮詢按 2）</p>
                </div>
                <div class="contact-item">
                    <h4>Line 官方帳號</h4>
                    <p>@artcanvas（點擊加入）<br>
                    <a href="https://line.me/ti/p/@artcanvas" target="_blank" style="color: var(--primary); text-decoration: underline;">https://line.me/ti/p/@artcanvas</a>
                    </p>
                    <p style="margin-top: 8px; font-size: 0.95rem; color: var(--secondary);">
                         Line 客服可提供螢幕共享技術支援，即時解決驅動問題
                    </p>
                </div>
                <div class="contact-item">
                    <h4>技術支援信箱</h4>
                    <p>support@artcanvas.tw<br>（硬體故障、驅動問題專用）</p>
                </div>
                <div class="contact-item">
                    <h4>直營體驗門市</h4>
                    <p>台北市信義區創作路 456 號 2F<br>（提供免費試用與技術諮詢，請提前預約）</p>
                </div>
            </div>

            <div class="contact-form">
                <h2>技術諮詢表單</h2>
                
                <!-- ====== 新增：成功/錯誤訊息顯示 ====== -->
                <?php if ($contact_success): ?>
                <div class="message-alert message-success">
                    <?php echo htmlspecialchars($contact_success); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($contact_error): ?>
                <div class="message-alert message-error">
                    <?php echo $contact_error; ?>
                </div>
                <?php endif; ?>
                <!-- ====================================== -->

                <form id="contactForm" action="contact_process.php" method="POST">
                    <input type="text" name="name" placeholder="您的姓名/創作者暱稱 *" required 
                           value="<?php echo htmlspecialchars($contact_data['name'] ?? $username); ?>" />
                    <input type="email" name="email" placeholder="電子信箱 *" required 
                           value="<?php echo htmlspecialchars($contact_data['email'] ?? $email); ?>" />
                    <input type="tel" name="phone" placeholder="聯絡電話（選填）" 
                           value="<?php echo htmlspecialchars($contact_data['phone'] ?? ''); ?>" />
                    <select name="product" required>
                        <option value="">請選擇您的產品型號 *</option>
                        <option value="intuos" <?php echo (isset($contact_data['product']) && $contact_data['product'] === 'intuos') ? 'selected' : ''; ?>>Wacom Intuos 系列</option>
                        <option value="cintiq" <?php echo (isset($contact_data['product']) && $contact_data['product'] === 'cintiq') ? 'selected' : ''; ?>>Wacom Cintiq 系列</option>
                        <option value="xp-pen" <?php echo (isset($contact_data['product']) && $contact_data['product'] === 'xp-pen') ? 'selected' : ''; ?>>XP-Pen 系列</option>
                        <option value="huion" <?php echo (isset($contact_data['product']) && $contact_data['product'] === 'huion') ? 'selected' : ''; ?>>Huion 系列</option>
                        <option value="ipad" <?php echo (isset($contact_data['product']) && $contact_data['product'] === 'ipad') ? 'selected' : ''; ?>>iPad + Apple Pencil</option>
                        <option value="other" <?php echo (isset($contact_data['product']) && $contact_data['product'] === 'other') ? 'selected' : ''; ?>>其他品牌</option>
                    </select>
                    <textarea name="message" placeholder="請描述您遇到的技術問題或需求 *" required><?php echo htmlspecialchars($contact_data['message'] ?? ''); ?></textarea>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 1.2rem;">
                        <strong>注意：</strong>若遇到驅動安裝或軟體相容問題，請提供作業系統版本與軟體名稱，將有助於快速解決問題。
                    </p>
                    <button type="submit">提交諮詢</button>
                </form>
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
                <li><a href="index.php">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="login.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
        <section class="links">
            <a href="#" aria-label="Facebook"><img src="./Icons/facebook.svg" alt="Facebook 創作社群" /></a>
            <a href="#" aria-label="Instagram"><img src="./Icons/instagram.svg" alt="Instagram 創作分享" /></a>
            <a href="#" aria-label="YouTube"><img src="./Icons/youtube.svg" alt="YouTube 教學頻道" /></a>
            <a href="#" aria-label="Line"><img src="./Icons/line.svg" alt="Line 技術支援" /></a>
        </section>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店 | 專業數位創作設備認證合作夥伴</p>
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