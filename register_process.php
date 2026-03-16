<?php
session_start();

// 防止直接訪問
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// 設定時區
date_default_timezone_set('Asia/Taipei');

// 資料庫連線
require_once 'config/database.php';

// 接收並清理輸入資料
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$creator_id = trim($_POST['creator_id'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// 儲存輸入資料（用於錯誤時重新填入）
$_SESSION['reg_username'] = $username;
$_SESSION['reg_email'] = $email;
$_SESSION['reg_creator_id'] = $creator_id;

try {
    // =====================================================
    // 表單驗證
    // =====================================================
    $errors = [];
    
    // 基本欄位檢查
    if (empty($username)) $errors[] = '請輸入顯示名稱。';
    if (empty($email)) $errors[] = '請輸入電子信箱。';
    if (empty($creator_id)) $errors[] = '請輸入創作者ID。';
    if (empty($password)) $errors[] = '請輸入密碼。';
    if (empty($password_confirm)) $errors[] = '請確認密碼。';
    
    // 長度檢查
    if (strlen($username) > 50) $errors[] = '顯示名稱不能超過50個字元。';
    if (strlen($creator_id) < 3 || strlen($creator_id) > 20) {
        $errors[] = '創作者ID長度需為3-20字元。';
    }
    
    // 格式檢查
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '電子信箱格式不正確。';
    }
    
    // 創作者ID格式（只允許字母、數字、底線）
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $creator_id)) {
        $errors[] = '創作者ID只能包含英文字母、數字、底線。';
    }
    
    // 密碼檢查
    if (strlen($password) < 6) {
        $errors[] = '密碼至少需6個字元。';
    }
    if ($password !== $password_confirm) {
        $errors[] = '密碼與確認密碼不一致。';
    }
    
    if (!empty($errors)) {
        $_SESSION['register_error'] = implode('<br>', $errors);
        header("Location: register.php");
        exit();
    }
    
    // =====================================================
    // 檢查唯一性
    // =====================================================
    $pdo = getDatabaseConnection();
    
    // 檢查電子信箱是否已存在
    $email_check = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $email_check->execute(['email' => $email]);
    if ($email_check->fetch()) {
        $_SESSION['register_error'] = '此電子信箱已被註冊。';
        header("Location: register.php");
        exit();
    }
    
    // 檢查創作者ID是否已存在
    $creator_check = $pdo->prepare("SELECT id FROM users WHERE creator_id = :creator_id");
    $creator_check->execute(['creator_id' => $creator_id]);
    if ($creator_check->fetch()) {
        $_SESSION['register_error'] = '此創作者ID已被使用，請選擇其他ID。';
        header("Location: register.php");
        exit();
    }
    
    // =====================================================
    // 建立新帳號
    // =====================================================
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $status = 'pending'; // 預設待審核狀態
    
    $insert_stmt = $pdo->prepare("
        INSERT INTO users (username, email, creator_id, password_hash, status, created_at) 
        VALUES (:username, :email, :creator_id, :password_hash, :status, NOW())
    ");
    
    $result = $insert_stmt->execute([
        'username' => $username,
        'email' => $email,
        'creator_id' => $creator_id,
        'password_hash' => $password_hash,
        'status' => $status
    ]);
    
    if ($result) {
        // 清除暫存資料
        unset($_SESSION['reg_username']);
        unset($_SESSION['reg_email']);
        unset($_SESSION['reg_creator_id']);
        
        // 設定成功訊息
        $_SESSION['register_success'] = '註冊成功！您的帳號正在審核中，請耐心等候。';
        header("Location: login.php");
        exit();
    } else {
        throw new Exception('註冊失敗，請稍後再試。');
    }
    
} catch (PDOException $e) {
    error_log("註冊錯誤: " . $e->getMessage());
    $_SESSION['register_error'] = '系統忙碌中，請稍後再試。';
    header("Location: register.php");
    exit();
} catch (Exception $e) {
    $_SESSION['register_error'] = $e->getMessage();
    header("Location: register.php");
    exit();
}
?>