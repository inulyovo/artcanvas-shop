<?php
session_start();

// 已登入者導向會員中心
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
    <title>創作者註冊 | ArtCanvas 電繪板專賣店</title>
    <meta name="description" content="加入ArtCanvas創作者社群，建立個人創作空間，展示您的數位藝術作品。" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        /* 隱藏 canvas 背景 */
        #fixed-bg { display: none; }

        /* 註冊容器 */
        .register-section {
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }

        .register-container {
            background: white;
            padding: 2.8rem;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 520px;
            text-align: center;
        }

        .register-container h2 {
            color: var(--primary-dark);
            margin-bottom: 2rem;
            font-size: 1.8rem;
            letter-spacing: -0.7px;
        }

        .register-form input {
            width: 100%;
            padding: 0.9rem 1rem;
            margin-bottom: 1.4rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 1.1rem;
            letter-spacing: -0.3px;
            box-sizing: border-box;
        }

        .register-form button {
            width: 100%;
            padding: 0.95rem;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            letter-spacing: -0.4px;
        }

        .register-form button:hover {
            background-color: #7a5e54;
            transform: translateY(-2px);
        }

        .register-form button:active {
            transform: translateY(0);
        }

        .register-links {
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
        }

        .register-links a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .register-links a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* 創作者ID 提示 */
        .creator-id-hint {
            background: #f8f9fa;
            border-left: 3px solid var(--primary);
            padding: 0.8rem;
            margin-bottom: 1.4rem;
            border-radius: 0 8px 8px 0;
            text-align: left;
            font-size: 0.9rem;
            color: #666;
        }

        /* 響應式 */
        @media (max-width: 650px) {
            .register-container {
                padding: 2rem;
            }
            .register-container h2 {
                font-size: 1.6rem;
            }
            .register-form button {
                font-size: 1.15rem;
            }
            .register-links {
                flex-direction: column;
                gap: 0.8rem;
                align-items: center;
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
                <li><a href="login.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
    </header>

    <!-- 錯誤訊息顯示 -->
    <?php if (isset($_SESSION['register_error'])): ?>
    <div style="max-width: 520px; margin: 0 auto 1.5rem; background: #fff3f3; border-left: 4px solid #dc3545; padding: 1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <p style="color: #dc3545; margin: 0; font-size: 0.95rem; font-family: 'LXGW WenKai Mono TC', monospace;">
            <?php echo htmlspecialchars($_SESSION['register_error']); ?>
        </p>
    </div>
    <?php unset($_SESSION['register_error']); ?>
    <?php endif; ?>

    <!-- 註冊主體 -->
    <section class="register-section">
        <div class="register-container">
            <h2>創作者註冊</h2>
            <form class="register-form" id="registerForm" action="register_process.php" method="POST">
                <input type="text" name="username" placeholder="顯示名稱（公開顯示）" required 
                       value="<?php echo isset($_SESSION['reg_username']) ? htmlspecialchars($_SESSION['reg_username']) : ''; ?>" />
                
                <input type="email" name="email" placeholder="電子信箱" required 
                       value="<?php echo isset($_SESSION['reg_email']) ? htmlspecialchars($_SESSION['reg_email']) : ''; ?>" />
                
                <div class="creator-id-hint">
                    <strong>創作者ID：</strong>將成為您的個人作品頁面網址<br>
                    例：<code>artcanvas.com/creator/your_id</code><br>
                    只能包含英文字母、數字、底線，長度 3-20 字元
                </div>
                <input type="text" name="creator_id" placeholder="創作者ID（唯一且不可更改）" required 
                       value="<?php echo isset($_SESSION['reg_creator_id']) ? htmlspecialchars($_SESSION['reg_creator_id']) : ''; ?>" />
                
                <input type="password" name="password" placeholder="密碼（至少6個字元）" required />
                <input type="password" name="password_confirm" placeholder="確認密碼" required />
                
                <button type="submit">立即註冊</button>
            </form>
            <div class="register-links">
                <a href="login.php">已有帳戶？立即登入</a>
                <a href="forgot_password.php">忘記密碼？</a>
            </div>
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

        // 前端表單驗證
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = this.password.value;
            const passwordConfirm = this.password_confirm.value;
            const creatorId = this.creator_id.value;
            
            // 密碼長度檢查
            if (password.length < 6) {
                e.preventDefault();
                alert('密碼至少需 6 個字元。');
                return false;
            }
            
            // 密碼確認檢查
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('密碼與確認密碼不一致。');
                return false;
            }
            
            // 創作者ID格式檢查
            const creatorIdRegex = /^[a-zA-Z0-9_]{3,20}$/;
            if (!creatorIdRegex.test(creatorId)) {
                e.preventDefault();
                alert('創作者ID只能包含英文字母、數字、底線，長度需為3-20字元。');
                return false;
            }
        });
    </script>
</body>
</html>