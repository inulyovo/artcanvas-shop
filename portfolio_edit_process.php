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
    $artwork_id = $_POST['id'] ?? 0;
    $creator_id = $_SESSION['creator_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'other';
    $status = $_POST['status'] ?? 'public';
    
    // 驗證
    if (empty($title)) {
        $_SESSION['edit_msg'] = "❌ 作品標題不能為空";
        $_SESSION['edit_msg_type'] = "error";
        header("Location: portfolio_edit.php?id=" . $artwork_id);
        exit();
    }
    
    if (strlen($title) > 100) {
        $_SESSION['edit_msg'] = "❌ 作品標題不能超過 100 字元";
        $_SESSION['edit_msg_type'] = "error";
        header("Location: portfolio_edit.php?id=" . $artwork_id);
        exit();
    }
    
    try {
        // 驗證作品屬於當前使用者
        $stmt = $pdo->prepare("SELECT id, image_path FROM artworks WHERE id = ? AND creator_id = ?");
        $stmt->execute([$artwork_id, $creator_id]);
        $artwork = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$artwork) {
            $_SESSION['edit_msg'] = "❌ 作品不存在或無權限編輯";
            $_SESSION['edit_msg_type'] = "error";
            header("Location: portfolio.php");
            exit();
        }
        
        $old_image_path = $artwork['image_path'];
        $new_image_path = $old_image_path;
        
        // 處理新圖片上傳
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            
            // 檢查檔案類型
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                $_SESSION['edit_msg'] = "❌ 不支援的檔案格式，請使用 JPG、PNG、GIF 或 WebP";
                $_SESSION['edit_msg_type'] = "error";
                header("Location: portfolio_edit.php?id=" . $artwork_id);
                exit();
            }
            
            // 檢查檔案大小 (5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                $_SESSION['edit_msg'] = "❌ 檔案大小超過 5MB";
                $_SESSION['edit_msg_type'] = "error";
                header("Location: portfolio_edit.php?id=" . $artwork_id);
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
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $new_image_path = $webpath;
                
                // 刪除舊圖片（如果存在且不同）
                if ($old_image_path && $old_image_path !== $new_image_path && file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        }
        
        // 更新資料庫
        $updateStmt = $pdo->prepare("
            UPDATE artworks 
            SET title = ?, description = ?, category = ?, status = ?, image_path = ?, updated_at = NOW()
            WHERE id = ? AND creator_id = ?
        ");
        $updateStmt->execute([$title, $description, $category, $status, $new_image_path, $artwork_id, $creator_id]);
        
        $_SESSION['edit_msg'] = "✅ 作品已成功更新！";
        $_SESSION['edit_msg_type'] = "success";
        header("Location: portfolio.php");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['edit_msg'] = "❌ 資料庫錯誤：" . htmlspecialchars($e->getMessage());
        $_SESSION['edit_msg_type'] = "error";
        header("Location: portfolio_edit.php?id=" . $artwork_id);
        exit();
    }
}

// 非 POST 請求
header("Location: portfolio.php");
exit();
?>