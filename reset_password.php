<?php
session_start();
require 'config.php';

$token = $_GET['token'] ?? '';
$valid_token = false;
$user_data = null;

if ($token) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, reset_expires FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data && strtotime($user_data['reset_expires']) > time()) {
            $valid_token = true;
        }
    } catch (PDOException $e) {
        // 忽略錯誤，讓 valid_token 保持 false
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <title>重設密碼 | ArtCanvas</title>
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        #fixed-bg { display: none; }
        .login-section { min-height: 80vh; display: flex; align-items: center; justify-content: center; background-color: var(--light); padding: 2rem 1.5rem; }
        .login-container { background: white; padding: 2.8rem; border-radius: 18px; box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12); width: 100%; max-width: 480px; text-align: center; }
        .login-container h2 { color: var(--primary-dark); margin-bottom: 1rem; font-size: 1.8rem; }
        .login-form input { width: 100%; padding: 0.9rem 1rem; margin-bottom: 1.4rem; border: 1px solid #ccc; border-radius: 8px; font-family: 'LXGW WenKai Mono TC', monospace; font-size: 1.1rem; box-sizing: border-box; }
        .login-form button { width: 100%; padding: 0.95rem; background-color: var(--secondary); color: white; border: none; border-radius: 8px; font-size: 1.2rem; cursor: pointer; }
        .login-form button:hover { background-color: #7a5e54; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; background: #f8d7da; color: #721c24; }
        .logo { display: flex; align-items: center; gap: 12px; justify-content: center; }
        .logo img { width: 48px; height: 48px; object-fit: contain; display: block; }
        .logo h1 { font-size: 1.5rem; margin: 0; }
        header { display: flex; justify-content: center; padding: 1rem 2rem; background-color: white; }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="./Icons/icon.gif" alt="ArtCanvas">
            <h1>ArtCanvas</h1>
        </div>
    </header>

    <section class="login-section">
        <div class="login-container">
            <h2>重設密碼</h2>
            
            <?php if (!$valid_token): ?>
                <div class="alert">
                    ❌ 重置連結無效或已過期。請重新申請。
                </div>
                <a href="forgot_password.php" style="color:var(--primary)">返回忘記密碼頁面</a>
            <?php else: ?>
                <p>您好，<?php echo htmlspecialchars($user_data['username']); ?>。請輸入您的新密碼。</p>
                
                <?php if (isset($_SESSION['reset_pass_error'])): ?>
                    <div class="alert"><?php echo $_SESSION['reset_pass_error']; unset($_SESSION['reset_pass_error']); ?></div>
                <?php endif; ?>

                <form class="login-form" action="reset_password_process.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="password" name="password" placeholder="新密碼 (至少6碼)" required />
                    <input type="password" name="confirm_password" placeholder="確認新密碼" required />
                    <button type="submit">更新密碼</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>