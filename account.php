<?php
session_start();

// 檢查登入
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die('無效使用者');
}

require_once 'config/database.php';

// 初始化變數
$message = '';
$error = '';

try {
    $pdo = getDatabaseConnection();

    // ✅ 修正：加入 id 欄位
    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('使用者資料不存在');
    }

    // 處理表單提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // 驗證原密碼
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $stored_hash = $stmt->fetchColumn();

            if (!password_verify($current_password, $stored_hash)) {
                $error = '原密碼錯誤，請重新輸入。';
            } elseif (strlen($new_password) < 6) {
                $error = '新密碼長度至少 6 字元。';
            } elseif ($new_password !== $confirm_password) {
                $error = '新密碼與確認密碼不一致。';
            } else {
                // 更新密碼
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$new_hash, $user_id]);
                $message = '密碼已成功更新！';
            }
        } elseif ($action === 'change_email') {
            $new_email = trim($_POST['new_email'] ?? '');
            $email_pattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

            if (!preg_match($email_pattern, $new_email)) {
                $error = '請輸入有效的電子信箱格式。';
            } elseif ($new_email === $user['email']) {
                $error = '新電子信箱與目前相同。';
            } else {
                // 檢查是否已被註冊
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$new_email]);
                if ($stmt->fetch()) {
                    $error = '此電子信箱已被其他帳號使用。';
                } else {
                    // 更新電子信箱
                    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $stmt->execute([$new_email, $user_id]);
                    $_SESSION['email'] = $new_email; // 同步 session
                    $message = '電子信箱已成功更新！';
                }
            }
        }
    }

} catch (PDOException $e) {
    $error = '系統錯誤：' . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>帳號設定 | ArtCanvas 電繪板專賣店</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        header .logo img {
            object-fit: contain;
        }
        footer .logo img {
            object-fit: contain;
        }



        #fixed-bg {
            display: none;
        }

        .account-section {
            background-color: var(--light);
            padding: 2rem 1.5rem;
        }

        .account-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .account-header {
            background: var(--primary);
            color: white;
            padding: 1.8rem 2rem;
            text-align: center;
        }

        .account-header h1 {
            font-size: 1.8rem;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .account-info {
            padding: 1.8rem;
            border-bottom: 1px solid #eee;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px dashed #eee;
        }

        .info-label {
            font-weight: 500;
            color: var(--primary-dark);
        }

        .info-value {
            color: #555;
        }

        .form-section {
            padding: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-dark);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: 'LXGW WenKai Mono TC', monospace;
            font-size: 1rem;
        }

        .btn-submit {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: -0.3px;
        }

        .btn-submit:hover {
            background: #7a5e54;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(141, 110, 99, 0.4);
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .logout-section {
            padding: 1.8rem;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        @media (max-width: 768px) {
            .info-row {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .form-section,
            .account-info {
                padding: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <!-- 頁首 -->
    <header>
        <div class="logo">
            <a href="index.php"><img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌"></a>
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php#home">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="member.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
    </header>

    <!-- 帳號設定主體 -->
    <section class="account-section">
        <div class="account-container">
            <div class="account-header">
                <h1>帳號設定</h1>
            </div>

            <?php if ($message): ?>
                <div class="message message-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="message message-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- 個人資訊區 -->
            <div class="account-info">
                <div class="info-row">
                    <span class="info-label">創作者 ID</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['id']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">使用者名稱</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">電子信箱</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">加入日期</span>
                    <span class="info-value"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></span>
                </div>
            </div>

            <!-- 修改密碼區 -->
            <div class="form-section">
                <h3 style="color: var(--primary-dark); margin-bottom: 1.2rem;">修改密碼</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label for="current_password">目前密碼</label>
                        <input type="password" id="current_password" name="current_password" class="form-control"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">新密碼</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required
                            minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">確認新密碼</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            required>
                    </div>
                    <button type="submit" class="btn-submit">更新密碼</button>
                </form>
            </div>

            <!-- 修改電子信箱區 -->
            <div class="form-section">
                <h3 style="color: var(--primary-dark); margin-bottom: 1.2rem;">修改電子信箱</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="change_email">
                    <div class="form-group">
                        <label for="new_email">新電子信箱</label>
                        <input type="email" id="new_email" name="new_email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn-submit">更新電子信箱</button>
                </form>
            </div>

            <!-- 登出區 -->
            <div class="logout-section">
                <form action="logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="logout-btn" onclick="return confirm('確定要登出嗎？')">
                        登出帳號
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- 頁尾 -->
    <footer>
        <div class="logo">
            <img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌" />
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php#home">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="member.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店</p>
    </footer>

    <script>
        const header = document.querySelector("header");
        window.addEventListener("scroll", () => {
            header.style.boxShadow = window.scrollY > 0
                ? "0 4px 12px rgba(0, 0, 0, 0.15)"
                : "none";
        });
    </script>
</body>

</html>