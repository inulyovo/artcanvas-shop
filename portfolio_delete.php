<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require 'config.php';

$artwork_id = $_GET['id'] ?? 0;
$creator_id = $_SESSION['creator_id'] ?? '';

try {
    // 驗證作品屬於當前使用者
    $stmt = $pdo->prepare("SELECT id, image_path FROM artworks WHERE id = ? AND creator_id = ?");
    $stmt->execute([$artwork_id, $creator_id]);
    $artwork = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($artwork) {
        // 刪除檔案（如果存在）
        if (!empty($artwork['image_path']) && file_exists($artwork['image_path'])) {
            unlink($artwork['image_path']);
        }
        
        // 刪除資料庫記錄
        $deleteStmt = $pdo->prepare("DELETE FROM artworks WHERE id = ? AND creator_id = ?");
        $deleteStmt->execute([$artwork_id, $creator_id]);
        
        $_SESSION['portfolio_msg'] = "✅ 作品已成功刪除";
        $_SESSION['portfolio_msg_type'] = "success";
    } else {
        $_SESSION['portfolio_msg'] = "❌ 作品不存在或無權限刪除";
        $_SESSION['portfolio_msg_type'] = "error";
    }
} catch (PDOException $e) {
    $_SESSION['portfolio_msg'] = "❌ 刪除失敗：" . htmlspecialchars($e->getMessage());
    $_SESSION['portfolio_msg_type'] = "error";
}

header("Location: portfolio.php");
exit();
?>