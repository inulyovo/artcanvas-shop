<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $_SESSION['reset_pass_error'] = "❌ 密碼長度至少需要 6 個字元。";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['reset_pass_error'] = "❌ 兩次輸入的密碼不一致。";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    try {
        // 驗證 Token
        $stmt = $pdo->prepare("SELECT id, username, reset_token, reset_expires FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['reset_pass_error'] = "❌ Token 不存在於資料庫";
            header("Location: forgot_password.php");
            exit();
        }

        if (strtotime($user['reset_expires']) <= time()) {
            $_SESSION['reset_pass_error'] = "❌ Token 已過期";
            header("Location: forgot_password.php");
            exit();
        }

        // 更新密碼
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $updateStmt->execute([$hashed_password, $user['id']]);

        if ($updateStmt->rowCount() === 0) {
            throw new Exception("密碼更新失敗");
        }

        $_SESSION['login_success_msg'] = "✅ 密碼重設成功，請使用新密碼登入。";
        header("Location: login.php");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['reset_pass_error'] = "❌ 資料庫錯誤：" . htmlspecialchars($e->getMessage());
        header("Location: forgot_password.php");
        exit();
    }
}
?>