<?php
session_start();

// 1. 連接資料庫 (請根據你的實際路徑修改)
// 假設你的 config.php 或 database.php 裡有 $pdo 變數
require 'config.php'; // 或者 require 'config/database.php';

// 如果沒有 $pdo，請使用以下範例連接 (請修改帳號密碼)
/*
$host = 'localhost';
$db   = 'artcanvas_db'; // 你的資料庫名稱
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
try { $pdo = new PDO($dsn, $user, $pass, $options); } catch (\PDOException $e) { throw new \PDOException($e->getMessage(), (int)$e->getCode()); }
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_msg'] = "電子信箱格式不正確。";
        $_SESSION['reset_msg_type'] = "error";
        header("Location: forgot_password.php");
        exit();
    }

    // 檢查 Email 是否存在
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 產生重置 Token (隨機字串)
        $token = bin2hex(random_bytes(32));
        // 設定過期時間 (1小時後)
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 更新資料庫
        $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $updateStmt->execute([$token, $expires, $user['id']]);

        // 產生重設連結
        $projectPath = dirname($_SERVER['PHP_SELF']);
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . $projectPath . "/reset_password.php?token=" . $token;

        // === 模擬發送郵件 ===
// 直接提供可點擊的連結
        $_SESSION['reset_msg'] = "重設連結已產生（模擬發送）。<br><br>" .
            "<strong>請點擊下方連結：</strong><br>" .
            "<a href='" . $resetLink . "' target='_blank'>" . $resetLink . "</a><br><br>" .
            "<small>（或複製上方連結到瀏覽器）</small>";
        $_SESSION['reset_msg_type'] = "success";

        // 正式環境請取消下面這行的註解並刪除上面的模擬訊息
        // mail($email, "重置密碼", "請點擊連結: $resetLink");
        // $_SESSION['reset_msg'] = "重置密碼連結已發送至您的信箱。";
    } else {
        // 為了安全，即使找不到使用者，也顯示相同的訊息，避免被窮舉 Email
        $_SESSION['reset_msg'] = "如果該電子信箱已註冊，您將收到重置密碼的信件。";
        $_SESSION['reset_msg_type'] = "success";
    }

    header("Location: forgot_password.php");
    exit();
}
?>