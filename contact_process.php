<?php
session_start();

// 防止直接訪問
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.php");
    exit();
}

// 資料庫連線
require_once 'config/database.php';

// 接收表單資料
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$product = $_POST['product'] ?? '';
$message = trim($_POST['message'] ?? '');

try {
    // 表單驗證
    $errors = [];
    
    if (empty($name)) $errors[] = '請輸入姓名或暱稱。';
    if (empty($email)) $errors[] = '請輸入電子信箱。';
    if (empty($product)) $errors[] = '請選擇產品型號。';
    if (empty($message)) $errors[] = '請描述您的技術問題。';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '電子信箱格式不正確。';
    }
    
    if (!empty($errors)) {
        $_SESSION['contact_error'] = implode('<br>', $errors);
        $_SESSION['contact_data'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'product' => $product,
            'message' => $message
        ];
        header("Location: contact.php");
        exit();
    }
    
    // 取得使用者ID（如果已登入）
    $user_id = null;
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    // 儲存到資料庫
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("
        INSERT INTO support_tickets (user_id, name, email, phone, product, message, status) 
        VALUES (:user_id, :name, :email, :phone, :product, :message, 'pending')
    ");
    
    $result = $stmt->execute([
        'user_id' => $user_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone ?: null,
        'product' => $product,
        'message' => $message
    ]);
    
    if ($result) {
        $_SESSION['contact_success'] = '感謝您的技術諮詢！我們的專業工程師將在 2 小時內與您聯繫。緊急技術問題請直接撥打客服專線。';
        unset($_SESSION['contact_data']); // 清除暫存資料
        header("Location: contact.php");
        exit();
    } else {
        throw new Exception('提交失敗，請稍後再試。');
    }
    
} catch (PDOException $e) {
    error_log("技術支援表單錯誤: " . $e->getMessage());
    $_SESSION['contact_error'] = '系統忙碌中，請稍後再試。';
    $_SESSION['contact_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'product' => $product,
        'message' => $message
    ];
    header("Location: contact.php");
    exit();
} catch (Exception $e) {
    $_SESSION['contact_error'] = $e->getMessage();
    header("Location: contact.php");
    exit();
}
?>