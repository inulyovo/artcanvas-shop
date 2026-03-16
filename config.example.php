<?php
// config.example.php
// 請複製此檔案並改名為 config.php，然後填入真實的資料庫資訊

$host = 'localhost';
$dbname = 'your_database_name'; // 請替換為實際資料庫名稱
$username = 'your_database_user'; // 請替換為實際使用者名稱
$password = 'your_database_password'; // 請替換為實際密碼

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("資料庫連接失敗：" . $e->getMessage());
}
?>