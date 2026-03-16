<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? '創作者';
$creator_id = $_SESSION['creator_id'] ?? '';

// 顯示訊息
$message = $_SESSION['upload_msg'] ?? '';
$message_type = $_SESSION['upload_msg_type'] ?? '';
unset($_SESSION['upload_msg'], $_SESSION['upload_msg_type']);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>上傳作品 | ArtCanvas 電繪板專賣店</title>
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        #fixed-bg { display: none; }
        
        .upload-section {
            min-height: 90vh;
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }
        
        .upload-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 18px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        .upload-header {
            background-color: #2E5D40;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .upload-header h2 {
            font-size: 1.8rem;
            margin: 0 0 0.5rem;
            font-weight: 500;
        }
        
        .upload-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .upload-form {
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
        
        /* 檔案上傳區域 */
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 12px;
            padding: 3rem 2rem;
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
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .upload-area-text {
            color: #666;
            font-size: 1rem;
        }
        
        .upload-area-text strong {
            color: var(--primary);
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

    <!-- 上傳主體 -->
    <section class="upload-section">
        <div class="upload-container">
            <div class="upload-header">
                <h2> 上傳新作品</h2>
                <p>分享您的數位創作，讓更多人看見您的才華</p>
            </div>
            
            <form class="upload-form" action="portfolio_upload_process.php" method="POST" enctype="multipart/form-data">
                <!-- 訊息提示 -->
                <?php if ($message): ?>
                    <div class="alert <?php echo $message_type === 'error' ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <!-- 作品標題 -->
                <div class="form-group">
                    <label for="title">作品標題 *</label>
                    <input type="text" id="title" name="title" placeholder="請輸入作品名稱" required maxlength="100" />
                </div>
                
                <!-- 作品描述 -->
                <div class="form-group">
                    <label for="description">作品描述</label>
                    <textarea id="description" name="description" placeholder="分享您的創作靈感、使用的工具或技巧..." maxlength="500"></textarea>
                    <div class="char-count"><span id="charCount">0</span> / 500</div>
                </div>
                
                <!-- 作品分類 -->
                <div class="form-group">
                    <label for="category">作品分類</label>
                    <select id="category" name="category">
                        <option value="illustration"> 插畫</option>
                        <option value="concept"> 概念設計</option>
                        <option value="character"> 角色設計</option>
                        <option value="landscape"> 場景繪製</option>
                        <option value="anime"> 動漫同人</option>
                        <option value="other"> 其他</option>
                    </select>
                </div>
                
                <!-- 可見性設定 -->
                <div class="form-group">
                    <label for="status">可見性設定</label>
                    <select id="status" name="status">
                        <option value="public">🌍 公開（所有人可見）</option>
                        <option value="private">🔒 私密（僅自己可見）</option>
                        <option value="draft">📝 草稿（未完成）</option>
                    </select>
                </div>
                
                <!-- 檔案上傳 -->
                <div class="form-group">
                    <label>作品圖片 *</label>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" id="image" name="image" accept="image/*" required onchange="previewImage(this)" />
                        <div class="upload-area-icon">📁</div>
                        <div class="upload-area-text">
                            <strong>點擊上傳</strong> 或拖曳圖片到這裡<br>
                            <small>支援 JPG、PNG、GIF 格式，最大 5MB</small>
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
                    <button type="submit" class="btn btn-primary">上傳作品</button>
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
            
            // 觸發 change 事件
            const event = new Event('change');
            input.dispatchEvent(event);
        });
    </script>
</body>
</html>