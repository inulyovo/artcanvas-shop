<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// 取得會員基本資料
$username = $_SESSION['username'] ?? '創作者';
$creator_id = $_SESSION['creator_id'] ?? '';
$email = $_SESSION['email'] ?? '';
$join_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>會員中心 | ArtCanvas 電繪板專賣店</title>
    <meta name="description" content="管理您的ArtCanvas帳戶、創作作品、訂單與技術支援。" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        /* 隱藏 canvas 背景 */
        #fixed-bg { display: none; }

        /* 會員中心主容器 */
        .member-section {
            min-height: 90vh;
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }

        .member-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        /* ====== 新增：純色 header 區塊（對應您截圖的深綠色）====== */
        .profile-header {
            background-color: #2E5D40; /* 深綠色：#2E5D40（ArtCanvas 品牌綠） */
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .profile-header h2 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.8px;
            font-weight: 500;
        }

        .profile-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
            font-weight: 300;
        }

        /* 三張資訊卡（對應截圖樣式）*/
        .profile-info {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.8rem;
            flex-wrap: wrap;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 0.9rem 1.4rem;
            border-radius: 12px;
            min-width: 160px;
            text-align: center;
        }

        .info-item strong {
            display: block;
            font-size: 0.85rem;
            opacity: 0.85;
            margin-bottom: 0.2rem;
            font-weight: 400;
        }

        .info-item span {
            display: block;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: -0.2px;
        }

        /* 功能選單 */
        .member-menu {
            padding: 2.5rem 2rem;
        }

        .menu-title {
            color: var(--primary-dark);
            margin-bottom: 2rem;
            font-size: 1.6rem;
            text-align: center;
            letter-spacing: -0.6px;
            font-weight: 500;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.8rem;
        }

        .menu-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.6rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #eaecef;
        }

        .menu-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .menu-card h3 {
            color: var(--primary-dark);
            margin-bottom: 0.7rem;
            font-size: 1.25rem;
            font-weight: 500;
        }

        .menu-card p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.5;
        }

        .menu-card a {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        /* 登出按鈕 */
        .logout-section {
            padding: 2rem;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .logout-btn {
            background-color: #ad1424;
            color: white;
            border: none;
            padding: 0.85rem 2.2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            letter-spacing: -0.3px;
        }

        .logout-btn:hover {
            background-color: #8d222d;
            transform: translateY(-2px);
        }

        /* 響應式調整 */
        @media (max-width: 768px) {
            .profile-header {
                padding: 1.8rem 1.5rem;
            }
            
            .profile-header h2 {
                font-size: 1.8rem;
            }
            
            .profile-info {
                gap: 1rem;
            }
            
            .info-item {
                min-width: 140px;
                padding: 0.7rem 1rem;
            }
            
            .member-menu {
                padding: 2rem 1.5rem;
            }
            
            .menu-title {
                font-size: 1.4rem;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
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

    <!-- 會員中心主體 -->
    <section class="member-section">
        <div class="member-container">
            <!-- ====== 純色 header 區塊（深綠）====== -->
            <div class="profile-header">
                <h2>歡迎回來，<?php echo htmlspecialchars($username); ?>！</h2>
                <p>您的創作旅程，從這裡開始</p>
                
                <div class="profile-info">
                    <div class="info-item">
                        <strong>創作者ID</strong>
                        <span><?php echo htmlspecialchars($creator_id); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>電子信箱</strong>
                        <span><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>加入時間</strong>
                        <span><?php echo $join_date; ?></span>
                    </div>
                </div>
            </div>

            <!-- 功能選單 -->
            <div class="member-menu">
                <h2 class="menu-title">我的創作空間</h2>
                <div class="menu-grid">
                    <div class="menu-card">
                        <a href="portfolio.php">
                            <h3> 作品集管理</h3>
                            <p>上傳、編輯、展示您的數位創作作品</p>
                        </a>
                    </div>
                    <div class="menu-card">
                        <a href="orders.php">
                            <h3> 訂單管理</h3>
                            <p>查看購買紀錄與電繪板設備訂單</p>
                        </a>
                    </div>
                    <div class="menu-card">
                        <a href="contact.php">
                            <h3> 技術支援</h3>
                            <p>驅動程式、軟體問題與硬體保固</p>
                        </a>
                    </div>
                    <div class="menu-card">
                        <a href="account.php">
                            <h3> 帳號設定</h3>
                            <p>修改密碼、個人檔案與通知設定</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- 登出按鈕 -->
            <div class="logout-section">
                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn" onclick="return confirm('確定要登出嗎？')">
                     登出
                    </button>
                </form>
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
    </script>
</body>
</html>