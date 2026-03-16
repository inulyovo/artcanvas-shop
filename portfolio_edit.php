<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$artwork_id = $_GET['id'] ?? 0;
$creator_id = $_SESSION['creator_id'] ?? '';

// 取得作品資料
try {
    $stmt = $pdo->prepare("SELECT * FROM artworks WHERE id = ? AND creator_id = ?");
    $stmt->execute([$artwork_id, $creator_id]);
    $artwork = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$artwork) {
        $_SESSION['portfolio_msg'] = "❌ 作品不存在或無權限編輯";
        $_SESSION['portfolio_msg_type'] = "error";
        header("Location: portfolio.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['portfolio_msg'] = "❌ 資料庫錯誤：" . htmlspecialchars($e->getMessage());
    $_SESSION['portfolio_msg_type'] = "error";
    header("Location: portfolio.php");
    exit();
}

// 顯示訊息
$message = $_SESSION['edit_msg'] ?? '';
$message_type = $_SESSION['edit_msg_type'] ?? '';
unset($_SESSION['edit_msg'], $_SESSION['edit_msg_type']);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>編輯作品 | ArtCanvas 電繪板專賣店</title>
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        #fixed-bg { display: none; }
        
        .edit-section {
            min-height: 90vh;
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }
        
        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        .edit-header {
            background-color: #2E5D40;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .edit-header h2 {
            font-size: 1.8rem;
            margin: 0 0 0.5rem;
            font-weight: 500;
        }
        
        .edit-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .edit-form {
            padding: 2.5rem 2rem;
        }
        
        .form-group {
            margin-bottom: 1.8rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-dark);
            font-weight: 500;
            font-size: 1rem;
        }
        
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        /* 當前圖片預覽 */
        .current-image {
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        
        .current-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .current-image p {
            margin-top: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* 檔案上傳區域 */
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }
        
        .upload-area:hover,
        .upload-area.dragover {
            border-color: var(--primary);
            background: #e8f5e9;
        }
        
        .upload-area input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .upload-area-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .upload-area-text {
            color: #666;
            font-size: 0.95rem;
        }
        
        .upload-preview {
            margin-top: 1rem;
            display: none;
        }
        
        .upload-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .upload-preview p {
            margin-top: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* 訊息提示 */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* 按鈕 */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.9rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }
        .btn-primary:hover {
            background-color: #7a5e54;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        /* 字數計算 */
        .char-count {
            text-align: right;
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.3rem;
        }
        
        /* Logo & Header */
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo img { width: 48px; height: 48px; object-fit: contain; display: block; }
        .logo h1 { font-size: 1.5rem; margin: 0; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background-color: white; }
        header nav ul { display: flex; list-style: none; gap: 2rem; }
        header nav a { text-decoration: none; color: var(--primary); }
        
        /* 響應式 */
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                text-align: center;
            }
        }
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

    <!-- 編輯主體 -->
    <section class="edit-section">
        <div class="edit-container">
            <div class="edit-header">
                <h2>✏️ 編輯作品</h2>
                <p>修改您的作品資訊</p>
            </div>
            
            <form class="edit-form" action="portfolio_edit_process.php" method="POST" enctype="multipart/form-data">
                <!-- 隱藏欄位：作品ID -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($artwork['id']); ?>" />
                
                <!-- 訊息提示 -->
                <?php if ($message): ?>
                    <div class="alert <?php echo $message_type === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- 作品標題 -->
                <div class="form-group">
                    <label for="title">作品標題 *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($artwork['title']); ?>" placeholder="請輸入作品名稱" required maxlength="100" />
                </div>
                
                <!-- 作品描述 -->
                <div class="form-group">
                    <label for="description">作品描述</label>
                    <textarea id="description" name="description" placeholder="分享您的創作靈感、使用的工具或技巧..." maxlength="500"><?php echo htmlspecialchars($artwork['description'] ?? ''); ?></textarea>
                    <div class="char-count"><span id="charCount"><?php echo strlen($artwork['description'] ?? ''); ?></span> / 500</div>
                </div>
                
                <!-- 作品分類 -->
                <div class="form-group">
                    <label for="category">作品分類</label>
                    <select id="category" name="category">
                        <option value="illustration" <?php echo ($artwork['category'] ?? '') === 'illustration' ? 'selected' : ''; ?>>🎨 插畫</option>
                        <option value="concept" <?php echo ($artwork['category'] ?? '') === 'concept' ? 'selected' : ''; ?>>🖌️ 概念設計</option>
                        <option value="character" <?php echo ($artwork['category'] ?? '') === 'character' ? 'selected' : ''; ?>>👤 角色設計</option>
                        <option value="landscape" <?php echo ($artwork['category'] ?? '') === 'landscape' ? 'selected' : ''; ?>>🏞️ 場景繪製</option>
                        <option value="anime" <?php echo ($artwork['category'] ?? '') === 'anime' ? 'selected' : ''; ?>>📺 動漫同人</option>
                        <option value="other" <?php echo ($artwork['category'] ?? '') === 'other' ? 'selected' : ''; ?>>✨ 其他</option>
                    </select>
                </div>
                
                <!-- 可見性設定 -->
                <div class="form-group">
                    <label for="status">可見性設定</label>
                    <select id="status" name="status">
                        <option value="public" <?php echo ($artwork['status'] ?? '') === 'public' ? 'selected' : ''; ?>>🌍 公開（所有人可見）</option>
                        <option value="private" <?php echo ($artwork['status'] ?? '') === 'private' ? 'selected' : ''; ?>>🔒 私密（僅自己可見）</option>
                        <option value="draft" <?php echo ($artwork['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>📝 草稿（未完成）</option>
                    </select>
                </div>
                
                <!-- 當前圖片 -->
                <div class="form-group">
                    <label>當前作品圖片</label>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($artwork['image_path'] ?? './images/placeholder.png'); ?>" 
                             alt="<?php echo htmlspecialchars($artwork['title']); ?>"
                             onerror="this.src='./images/placeholder.png'">
                        <p>目前圖片：<?php echo basename($artwork['image_path'] ?? '無圖片'); ?></p>
                    </div>
                </div>
                
                <!-- 更換圖片 -->
                <div class="form-group">
                    <label>更換圖片（可選）</label>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)" />
                        <div class="upload-area-icon">📁</div>
                        <div class="upload-area-text">
                            <strong>點擊上傳</strong> 新圖片<br>
                            <small>支援 JPG、PNG、GIF 格式，最大 5MB（不上傳則保持原圖）</small>
                        </div>
                    </div>
                    <div class="upload-preview" id="uploadPreview">
                        <img id="previewImg" src="" alt="預覽" />
                        <p id="previewName"></p>
                    </div>
                </div>
                
                <!-- 按鈕 -->
                <div class="form-actions">
                    <a href="portfolio.php" class="btn btn-secondary">取消</a>
                    <button type="submit" class="btn btn-primary">儲存修改</button>
                </div>
            </form>
        </div>
    </section>

    <script>
        // 字數計算
        const descTextarea = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        descTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        
        // 圖片預覽
        function previewImage(input) {
            const preview = document.getElementById('uploadPreview');
            const previewImg = document.getElementById('previewImg');
            const previewName = document.getElementById('previewName');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // 檢查檔案大小
                if (file.size > 5 * 1024 * 1024) {
                    alert('檔案大小超過 5MB，請選擇較小的圖片');
                    input.value = '';
                    return;
                }
                
                // 檢查檔案類型
                if (!file.type.match('image.*')) {
                    alert('請選擇圖片檔案（JPG、PNG、GIF）');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        // 拖曳效果
        const uploadArea = document.getElementById('uploadArea');
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const input = document.getElementById('image');
            input.files = e.dataTransfer.files;
            
            const event = new Event('change');
            input.dispatchEvent(event);
        });
    </script>
</body>
</html>