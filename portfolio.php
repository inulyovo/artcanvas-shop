<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// 取得會員資訊
$user_id = $_SESSION['user_id'] ?? '';
$username = $_SESSION['username'] ?? '創作者';
$creator_id = $_SESSION['creator_id'] ?? '';

// 取得使用者的作品集
$artworks = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM artworks WHERE creator_id = ? ORDER BY created_at DESC");
    $stmt->execute([$creator_id]);
    $artworks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 如果資料表不存在，忽略錯誤
    $artworks = [];
}

// 顯示訊息
$message = $_SESSION['portfolio_msg'] ?? '';
$message_type = $_SESSION['portfolio_msg_type'] ?? '';
unset($_SESSION['portfolio_msg'], $_SESSION['portfolio_msg_type']);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>作品集管理 | ArtCanvas 電繪板專賣店</title>
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        #fixed-bg { display: none; }
        
        /* 主容器 */
        .portfolio-section {
            min-height: 90vh;
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }
        
        .portfolio-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        /* Header 區塊 */
        .portfolio-header {
            background-color: #2E5D40;
            color: white;
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .portfolio-header h2 {
            font-size: 1.8rem;
            margin: 0;
            font-weight: 500;
        }
        
        .upload-btn {
            background-color: white;
            color: #2E5D40;
            border: 2px solid white;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .upload-btn:hover {
            background-color: transparent;
            color: white;
        }
        
        /* 訊息提示 */
        .alert {
            padding: 1rem;
            margin: 1.5rem 2rem 0;
            border-radius: 8px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* 作品集網格 */
        .artwork-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }
        
        .artwork-card {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #eaecef;
            transition: all 0.3s ease;
        }
        
        .artwork-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .artwork-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #eee;
        }
        
        .artwork-info {
            padding: 1.2rem;
        }
        
        .artwork-info h3 {
            color: var(--primary-dark);
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .artwork-info p {
            color: #666;
            font-size: 0.9rem;
            margin: 0 0 1rem;
            line-height: 1.4;
        }
        
        .artwork-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 1rem;
        }
        
        .artwork-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .artwork-actions button {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-edit {
            background-color: var(--primary);
            color: white;
        }
        .btn-edit:hover { background-color: var(--primary-dark); }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover { background-color: #c82333; }
        
        /* 空狀態 */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-dark);
        }
        
        .empty-state p {
            margin-bottom: 2rem;
        }
        
        /* 返回按鈕 */
        .back-link {
            display: inline-block;
            margin: 0 2rem 2rem;
            color: var(--primary);
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        
        /* 響應式 */
        @media (max-width: 768px) {
            .portfolio-header {
                flex-direction: column;
                text-align: center;
            }
            .artwork-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Logo 樣式 */
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo img { width: 48px; height: 48px; object-fit: contain; display: block; }
        .logo h1 { font-size: 1.5rem; margin: 0; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background-color: white; }
        header nav ul { display: flex; list-style: none; gap: 2rem; }
        header nav a { text-decoration: none; color: var(--primary); }
        footer { background: var(--primary-dark); color: white; padding: 2rem; text-align: center; }
        footer nav ul { display: flex; justify-content: center; list-style: none; gap: 2rem; margin: 1rem 0; }
        footer nav a { color: white; text-decoration: none; }
        footer .links { display: flex; justify-content: center; gap: 1rem; margin: 1.5rem 0; }
        footer .links img { width: 32px; height: 32px; }
        footer .copyright { font-size: 0.9rem; opacity: 0.8; }
    </style>
</head>
<body>
    <!-- 頁首 -->
    <header>
        <div class="logo">
            <a href="index.php"><img src="./Icons/icon.gif" alt="ArtCanvas"></a>
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php#home">首頁</a></li>
                <li><a href="member.php">會員中心</a></li>
            </ul>
        </nav>
    </header>

    <!-- 作品集主體 -->
    <section class="portfolio-section">
        <div class="portfolio-container">
            <!-- Header -->
            <div class="portfolio-header">
                <h2> 我的作品集</h2>
                <a href="portfolio_upload.php" class="upload-btn">+ 上傳新作品</a>
            </div>
            
            <!-- 訊息提示 -->
            <?php if ($message): ?>
                <div class="alert <?php echo $message_type === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- 作品集網格 -->
            <?php if (empty($artworks)): ?>
                <div class="empty-state">
                    <h3>還沒有任何作品</h3>
                    <p>開始上傳您的第一件數位創作吧！</p>
                    <a href="portfolio_upload.php" class="upload-btn">上傳作品</a>
                </div>
            <?php else: ?>
                <div class="artwork-grid">
                    <?php foreach ($artworks as $artwork): ?>
                        <div class="artwork-card">
                            <img src="<?php echo htmlspecialchars($artwork['image_path'] ?? './images/placeholder.png'); ?>" 
                                 alt="<?php echo htmlspecialchars($artwork['title']); ?>" 
                                 class="artwork-image"
                                 onerror="this.src='./images/placeholder.png'">
                            <div class="artwork-info">
                                <h3><?php echo htmlspecialchars($artwork['title']); ?></h3>
                                <p><?php echo htmlspecialchars(mb_substr($artwork['description'] ?? '', 0, 50)); ?><?php echo strlen($artwork['description'] ?? '') > 50 ? '...' : ''; ?></p>
                                <div class="artwork-meta">
                                    <span>📅 <?php echo date('Y-m-d', strtotime($artwork['created_at'])); ?></span>
                                    <span>👁️ <?php echo $artwork['views'] ?? 0; ?></span>
                                </div>
                                <div class="artwork-actions">
                                    <button class="btn-edit" onclick="location.href='portfolio_edit.php?id=<?php echo $artwork['id']; ?>'">編輯</button>
                                    <button class="btn-delete" onclick="if(confirm('確定刪除此作品？')) location.href='portfolio_delete.php?id=<?php echo $artwork['id']; ?>'">刪除</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <a href="member.php" class="back-link">← 返回會員中心</a>
        </div>
    </section>

    <!-- 頁尾 -->
    <footer>
        <div class="logo" style="justify-content: center;">
            <img src="./Icons/icon.gif" alt="ArtCanvas">
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
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店</p>
    </footer>
</body>
</html>