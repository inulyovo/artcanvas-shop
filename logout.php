<?php
session_start();

// 清除所有 session 資料
$_SESSION = array();

// 刪除 session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 銷毀 session
session_destroy();

// 重定向到首頁
header("Location: index.php");
exit();
?>