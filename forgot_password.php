<?php
session_start();
// 如果已登入，跳轉回會員中心
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: member.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>忘記密碼 | ArtCanvas</title>
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        /* 隱藏 canvas 背景 */
        #fixed-bg {
            display: none;
        }

        /* 登入容器 */
        .login-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }

        .login-container {
            background: white;
            padding: 2.8rem;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 480px;
            text-align: center;
        }

        .login-container h2 {
            color: var(--primary-dark);
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .login-container p {
            color: #666;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .login-form input {
            width: 100%;
            padding: 0.9rem 1rem;
            margin-bottom: 1.4rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 1.1rem;
            box-sizing: border-box;
        }

        .login-form button {
            width: 100%;
            padding: 0.95rem;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-form button:hover {
            background-color: #7a5e54;
        }

        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: var(--primary);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* 訊息樣式 */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: left;
            word-break: break-all;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success a {
            color: #0c5460;
            font-weight: bold;
            text-decoration: underline;
        }

        .alert-success a:hover {
            color: #062c33;
        }

        /* Logo 容器 */
        .logo {
            display: flex;
            align-items: center;
            /* 垂直居中 */
            gap: 12px;
            /* 圖片和文字間距 */
        }

        /* Logo 圖片 */
        .logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            display: block;
            /* 移除 inline 元素的額外空間 */
        }

        /* Logo 文字 */
        .logo h1 {
            font-size: 1.5rem;
            margin: 0;
            /* 移除預設 margin */
            line-height: 1.2;
            /* 調整行高 */
            white-space: nowrap;
            /* 防止文字換行 */
        }

        /* Header 整體 */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* 響應式調整 */
        @media (max-width: 768px) {
            .logo img {
                width: 40px;
                height: 40px;
            }

            .logo h1 {
                font-size: 1.2rem;
            }

            header {
                padding: 0.8rem 1rem;
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
                <li><a href="login.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
    </header>

    <!-- 忘記密碼主體 -->
    <section class="login-section">
        <div class="login-container">
            <h2>忘記密碼</h2>
            <p>請輸入您的註冊電子信箱，我們將發送重置密碼連結給您。</p>

            <?php if (isset($_SESSION['reset_msg'])): ?>
                <div
                    class="alert <?php echo $_SESSION['reset_msg_type'] === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                    <?php
                    // 直接輸出 HTML，不轉義，這樣連結才可以點擊
                    echo $_SESSION['reset_msg'];
                    ?>
                </div>
                <?php unset($_SESSION['reset_msg'], $_SESSION['reset_msg_type']); ?>
            <?php endif; ?>

            <form class="login-form" action="forgot_password_process.php" method="POST">
                <input type="email" name="email" placeholder="註冊電子信箱" required />
                <button type="submit">發送重置連結</button>
            </form>
            <a href="login.php" class="back-link">← 返回登入</a>
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
                <li><a href="login.php">會員中心</a></li>
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
    </script>
</body>

</html>