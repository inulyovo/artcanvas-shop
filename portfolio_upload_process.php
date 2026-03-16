<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 檢查是否已登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creator_id = $_SESSION['creator_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'other';
    $status = $_POST['status'] ?? 'public';
    
    // 驗證
    if (empty($title)) {
        $_SESSION['upload_msg'] = "❌ 作品標題不能為空";
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
    
    if (strlen($title) > 100) {
        $_SESSION['upload_msg'] = "❌ 作品標題不能超過 100 字元";
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
    
    // 檢查檔案
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['upload_msg'] = "❌ 請選擇要上傳的圖片";
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
    
    $file = $_FILES['image'];
    
    // 檢查上傳錯誤
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => '檔案超過 php.ini 限制',
            UPLOAD_ERR_FORM_SIZE => '檔案超過表單限制',
            UPLOAD_ERR_PARTIAL => '檔案只上傳了一部分',
            UPLOAD_ERR_NO_FILE => '沒有檔案上傳',
            UPLOAD_ERR_NO_TMP_DIR => '缺少暫存資料夾',
            UPLOAD_ERR_CANT_WRITE => '寫入失敗',
            UPLOAD_ERR_EXTENSION => 'PHP 擴充功能阻止上傳'
        ];
        $_SESSION['upload_msg'] = "❌ 上傳失敗：" . ($errorMessages[$file['error']] ?? '未知錯誤');
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
    
    // 檢查檔案類型
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['upload_msg'] = "❌ 不支援的檔案格式，請使用 JPG、PNG、GIF 或 WebP";
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
    
    // 檢查檔案大小 (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['upload_msg'] = "❌ 檔案大小超過 5MB";
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
    
    // 建立上傳目錄
    $uploadDir = __DIR__ . '/uploads/artworks/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 產生唯一檔案名稱
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('artwork_') . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    $webpath = './uploads/artworks/' . $filename;
    
    // 移動檔案
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        $_SESSION['upload_msg'] = "❌ 檔案儲存失敗";
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
    
    try {
        // 插入資料庫
        $stmt = $pdo->prepare("
            INSERT INTO artworks (creator_id, title, description, category, image_path, status, views) 
            VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([$creator_id, $title, $description, $category, $webpath, $status]);
        
        $_SESSION['upload_msg'] = "✅ 作品上傳成功！";
        $_SESSION['upload_msg_type'] = "success";
        header("Location: portfolio.php");
        exit();
        
    } catch (PDOException $e) {
        // 上傳失敗則刪除檔案
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        $_SESSION['upload_msg'] = "❌ 資料庫錯誤：" . htmlspecialchars($e->getMessage());
        $_SESSION['upload_msg_type'] = "error";
        header("Location: portfolio_upload.php");
        exit();
    }
}

// 非 POST 請求
header("Location: portfolio.php");
exit();
?>