<?php
session_start();

// =====================================================
// login_process.php - 會員登入處理
// =====================================================

// 防止直接訪問（必須透過POST提交）
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// 設定時區
date_default_timezone_set('Asia/Taipei');

// =====================================================
// 資料庫連線設定
// =====================================================
require_once 'config/database.php';

// =====================================================
// 接收並清理輸入資料
// =====================================================
$account = trim($_POST['account'] ?? '');
$password = $_POST['password'] ?? '';

// =====================================================
// 表單驗證
// =====================================================
$errors = [];

// 檢查帳號是否為空
if (empty($account)) {
    $errors[] = '請輸入帳號（電子信箱或創作者ID）。';
}

// 檢查密碼是否為空
if (empty($password)) {
    $errors[] = '請輸入密碼。';
}

// 密碼長度檢查
if (strlen($password) < 6) {
    $errors[] = '密碼至少需 6 個字元。';
}

// 若有錯誤，返回登入頁並顯示錯誤訊息
if (!empty($errors)) {
    $_SESSION['login_error'] = implode('<br>', $errors);
    $_SESSION['last_account'] = $account;
    header("Location: login.php");
    exit();
}

// =====================================================
// 判斷帳號類型（電子信箱 或 創作者ID）
// =====================================================
$is_email = filter_var($account, FILTER_VALIDATE_EMAIL);

try {
    // =====================================================
    // 資料庫查詢
    // =====================================================
    $pdo = getDatabaseConnection(); // 從 database.php 取得連線
    
    // 準備 SQL 語句（防止 SQL Injection）
    if ($is_email) {
        // 使用電子信箱登入
        $sql = "SELECT id, creator_id, email, password_hash, username, status, last_login 
                FROM users 
                WHERE email = :account AND status != 'deleted' 
                LIMIT 1";
    } else {
        // 使用創作者ID登入
        $sql = "SELECT id, creator_id, email, password_hash, username, status, last_login 
                FROM users 
                WHERE creator_id = :account AND status != 'deleted' 
                LIMIT 1";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['account' => $account]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // =====================================================
    // 驗證帳號是否存在
    // =====================================================
    if (!$user) {
        $_SESSION['login_error'] = '帳號或密碼錯誤。';
        $_SESSION['last_account'] = $account;
        header("Location: login.php");
        exit();
    }
    
    // =====================================================
    // 檢查帳號狀態
    // =====================================================
    if ($user['status'] === 'suspended') {
        $_SESSION['login_error'] = '此帳號已被停權，請聯絡客服。';
        header("Location: login.php");
        exit();
    }
    
    // if ($user['status'] === 'pending') {
    //     $_SESSION['login_error'] = '帳號尚未通過審核，請耐心等候。';
    //     header("Location: login.php");
    //     exit();
    // }
    
    // =====================================================
    // 驗證密碼
    // =====================================================
    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['login_error'] = '帳號或密碼錯誤。';
        $_SESSION['last_account'] = $account;
        header("Location: login.php");
        exit();
    }
    
    // =====================================================
    // 檢查是否需要重新加密密碼（PHP版本更新時）
    // =====================================================
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $update_stmt->execute([
            'hash' => $new_hash,
            'id' => $user['id']
        ]);
    }
    
    // =====================================================
    // 密碼正確，設定 Session
    // =====================================================
    $_SESSION['user_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['creator_id'] = $user['creator_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time();
    
    // =====================================================
    // 記錄登入時間與IP
    // =====================================================
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $login_time = date('Y-m-d H:i:s');
    
    $log_stmt = $pdo->prepare("
        INSERT INTO login_logs (user_id, login_time, ip_address, user_agent) 
        VALUES (:user_id, :login_time, :ip_address, :user_agent)
    ");
    $log_stmt->execute([
        'user_id' => $user['id'],
        'login_time' => $login_time,
        'ip_address' => $ip_address,
        'user_agent' => $user_agent
    ]);
    
    // 更新最後登入時間
    $update_login_stmt = $pdo->prepare("
        UPDATE users SET last_login = :login_time WHERE id = :id
    ");
    $update_login_stmt->execute([
        'login_time' => $login_time,
        'id' => $user['id']
    ]);
    
    // =====================================================
    // 清除登入頁的暫存資料
    // =====================================================
    unset($_SESSION['login_error']);
    unset($_SESSION['last_account']);
    
    // =====================================================
    // 登入成功，跳轉至會員中心
    // =====================================================
    header("Location: member.php");
    exit();
    
} catch (PDOException $e) {
    // 資料庫錯誤處理
    error_log("登入錯誤: " . $e->getMessage());
    $_SESSION['login_error'] = '系統忙碌中，請稍後再試。';
    $_SESSION['last_account'] = $account;
    header("Location: login.php");
    exit();
}
?>