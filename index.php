<?php
session_start(); // 必须在任何输出之前调用
require_once 'config.php';

// 從資料庫獲取特色產品（前三個）
try {
    $stmt = $pdo->query("SELECT * FROM products WHERE featured = 1 LIMIT 3");
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $featured_products = [];
    error_log("查詢產品失敗: " . $e->getMessage());
}

// 從資料庫獲取購物車數量（如果用戶已登入）
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $cart_count = $result['total'] ?? 0;
    } catch (PDOException $e) {
        $cart_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtCanvas 電繪板專賣店 | 專業數位創作設備首選</title>
    <meta name="description" content="銷售Wacom、XP-Pen等頂級電繪板，提供精準筆觸技術、三年全面保固與專業創作套件">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
    <link href="./bootstrap-5.2.3-dist/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="./style.css">
    <style>
        /* === 側邊欄樣式 === */
        .inu-sidebar-shell {
            position: fixed;
            top: 0;
            left: 0;
            width: 84px;
            height: 100vh;
            background: #fff;
            z-index: 9999;
            transition: width 0.5s ease;
            padding-left: 10px;
            overflow-y: auto;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
        }

        .inu-sidebar-shell::-webkit-scrollbar {
            width: 4px;
        }

        .inu-sidebar-shell::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .inu-sidebar-shell::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .inu-sidebar-shell:hover {
            width: 300px;
        }

        .inu-sidebar-nav {
            position: relative;
            height: 100%;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .inu-sidebar-nav li {
            position: relative;
            padding: 5px 0;
            will-change: transform;
            transition: transform 0.3s ease;
        }

        /* Logo 特殊間距 */
        #inu-sidebar-logo {
            margin: 40px 0 30px 0;
        }

        #inu-sidebar-logo .inu-sidebar-text {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #171717;
        }

        /* 確保側邊欄展開時有足夠寬度顯示完整文字 */
        .inu-sidebar-shell:hover {
            width: 320px;
            /* 稍微增加寬度，確保文字完整顯示 */
        }

        .inu-sidebar-nav li a {
            display: flex;
            white-space: nowrap;
            color: inherit;
            text-decoration: none;
            position: relative;
            z-index: 2;
        }

        .inu-sidebar-icon {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 60px;
            height: 70px;
            padding-left: 10px;
            color: rgb(110, 90, 240);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .inu-sidebar-imageBox {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            background: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .inu-sidebar-imageBox img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .inu-sidebar-text {
            height: 70px;
            display: flex;
            align-items: center;
            font-size: 20px;
            color: #333;
            padding-left: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        /* 懸停效果增強 */
        .inu-sidebar-nav li:not(.inu-sidebar-divider):not(.inu-sidebar-social-item):hover {
            transform: translateX(5px);
        }

        .inu-sidebar-nav li:not(.inu-sidebar-divider):not(.inu-sidebar-social-item):hover .inu-sidebar-icon {
            transform: scale(1.1);
            color: #ffa117;
        }

        .inu-sidebar-nav li:not(.inu-sidebar-divider):not(.inu-sidebar-social-item):hover .inu-sidebar-text {
            color: #ffa117;
            transform: translateX(3px);
        }

        .inu-sidebar-nav li:not(.inu-sidebar-divider):not(.inu-sidebar-social-item):hover .inu-sidebar-imageBox img {
            transform: scale(1.05);
        }

        /* 懸停背景效果 - 使用偽元素創建流暢動畫 */
        .inu-sidebar-nav li:not(.inu-sidebar-divider):not(.inu-sidebar-social-item)::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f8f8f8;
            border-radius: 50px 0 0 50px;
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1;
        }

        .inu-sidebar-nav li:not(.inu-sidebar-divider):not(.inu-sidebar-social-item):hover::after {
            opacity: 1;
            transform: translateX(0);
        }

        /* Active 狀態覆蓋懸停效果 */
        .inu-sidebar-nav li.inu-sidebar-active::after {
            opacity: 0 !important;
            transform: translateX(-20px) !important;
        }

        /* Active 狀態 */
        .inu-sidebar-nav li.inu-sidebar-active {
            /* background: #171717; */
            border-top-left-radius: 50px;
            border-bottom-left-radius: 50px;
            transform: none !important;
        }

        /* Active 偽元素 */
        .inu-sidebar-nav li.inu-sidebar-active::before {
            content: "";
            position: absolute;
            top: -30px;
            right: 0;
            width: 30px;
            height: 30px;
            border-bottom-right-radius: 25px;
            /* box-shadow: 5px 5px 0 5px #171717; */
        }

        .inu-sidebar-nav li.inu-sidebar-active::after {
            content: "";
            position: absolute;
            bottom: -30px;
            right: 0;
            width: 30px;
            height: 30px;
            border-top-right-radius: 25px;
            box-shadow: 5px -5px 0 5px #171717;
        }

        /* Active 時頭像/圖標外圈 */
        .inu-sidebar-nav li.inu-sidebar-active .inu-sidebar-icon::before {
            content: "";
            position: absolute;
            top: 5px;
            left: 5px;
            width: 60px;
            height: 60px;
            background: #fff;
            border-radius: 50%;
            border: 7px solid #171717;
            box-sizing: border-box;
            z-index: -1;
        }

        /* Active 時的圖標和文字顏色 */
        .inu-sidebar-nav li.inu-sidebar-active .inu-sidebar-icon,
        .inu-sidebar-nav li.inu-sidebar-active .inu-sidebar-text {
            color: #fff !important;
        }

        /* 活動狀態下懸停不改變顏色 */
        .inu-sidebar-nav li.inu-sidebar-active:hover .inu-sidebar-icon,
        .inu-sidebar-nav li.inu-sidebar-active:hover .inu-sidebar-text {
            color: #fff !important;
        }

        /* 分隔線樣式 */
        .inu-sidebar-divider {
            height: 1px;
            background: #eee;
            margin: 20px 20px;
            width: calc(100% - 40px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .inu-sidebar-shell:hover .inu-sidebar-divider {
            opacity: 1;
        }

        /* 社群媒體項目樣式 */
        .inu-sidebar-social-item {
            height: 60px;
            display: flex;
            align-items: center;
            padding-left: 25px;
            position: relative;
            transition: all 0.3s ease;
            will-change: transform;
            margin: 5px 0;
        }

        .inu-sidebar-social-item:hover {
            background: #f8f8f8;
            transform: translateX(5px);
            border-radius: 0 20px 20px 0;
        }

        .inu-sidebar-social-icon {
            width: 45px;
            height: 45px;
            padding-left: 20px;
            margin-right: 30px;
            transition: all 0.3s ease;
        }

        .inu-sidebar-social-item:hover .inu-sidebar-social-icon {
            transform: scale(1.1);
            filter: brightness(1.2);
        }

        .inu-sidebar-social-text {
            font-size: 18px;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .inu-sidebar-social-item:hover .inu-sidebar-social-text {
            color: #ffa117;
            transform: translateX(3px);
        }

        /* 社群項目懸停背景 */
        .inu-sidebar-social-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f8f8f8;
            border-radius: 0 20px 20px 0;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .inu-sidebar-social-item:hover::after {
            opacity: 1;
            transform: translateX(0);
        }

        /* === 頁腳樣式 === */
        .copyright {
            font-size: 14px;
            color: #aaa;
            margin: 0;
        }

        /* 確保側邊欄在深色背景上清晰可見 */
        @media (prefers-color-scheme: dark) {
            .inu-sidebar-shell {
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.3);
            }
        }
    </style>
    <style>
        /* === 搜尋欄與圖示樣式 === */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .search-container {
            position: relative;
        }

        .search-container input {
            width: 200px;
            padding: 0.6rem 0.8rem 0.6rem 2.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #f8f5f0;
            color: var(--primary-dark);
        }

        .search-container input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 93, 63, 0.2);
            outline: none;
            width: 250px;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
        }

        .nav-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(46, 93, 63, 0.08);
            color: var(--primary);
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .nav-icon:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .nav-icon:hover img {
            filter: brightness(0) invert(1);
        }

        .nav-icon-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #ff4d4d;
            color: white;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .nav-icon img {
            width: 24px;
            height: 24px;
            filter: brightness(0) saturate(100%) invert(30%) sepia(90%) saturate(100%) hue-rotate(95deg) brightness(95%) contrast(85%);
            transition: all 0.3s ease;
        }

        /* 響應式調整 */
        @media (max-width: 900px) {
            .search-container input {
                width: 150px;
                padding-left: 2.2rem;
                font-size: 0.85rem;
            }

            .search-container input:focus {
                width: 180px;
            }

            .header-actions {
                gap: 0.8rem;
            }
        }

        @media (max-width: 768px) {
            .search-container {
                display: none;
            }

            .nav-icon {
                width: 40px;
                height: 40px;
            }
        }

        /* === 其他通用樣式 === */
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: 'LXGW WenKai Mono TC', monospace;
        }

        :root {
            --primary-dark: #1a3a2a;
            --primary: #2e5d3f;
            --secondary: #8d6e63;
            --accent: #a5d6a7;
            --light: #f8f5f0;
            --dark: #263238;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* 山林背景動畫樣式 - 改為創作背景 */
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
        }

        #fixed-bg {
            position: fixed;
            left: 50%;
            top: 55%;
            transform: translate(-50%, -50%);
            width: 100vw;
            height: 100vh;
            z-index: -1;
            background: linear-gradient(to bottom, #1a2a3a 0%, #2a3a4a 100%);
            pointer-events: none;
        }

        canvas {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            aspect-ratio: 1;
            pointer-events: none;
        }

        #c {
            width: auto;
            height: 100%;
        }

        #c2 {
            width: 100%;
            height: auto;
        }

        section.second .card img {
            transition: transform 0.3s, filter 0.3s;
        }

        section.second .card:hover img {
            transform: scale(1.08) rotate(2deg);
            filter: drop-shadow(0 5px 10px rgba(46, 93, 63, 0.3));
        }

        @media (max-aspect-ratio: 1) {
            #c {
                width: 100%;
                height: auto;
            }

            #c2 {
                width: auto;
                height: 100%;
            }
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 500;
            line-height: 1.4;
            letter-spacing: -0.5px;
        }

        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: white;
            padding: 0 2rem;
            height: 72px;
            box-sizing: border-box;
        }

        header div.logo {
            display: flex;
            align-items: center;
            flex: 5 1 400px;
            margin-left: 2rem;
        }

        header div.logo img {
            width: 5.5vw;
            height: 5.5vw;
            min-width: 50px;
            min-height: 50px;
        }

        header div.logo h1 {
            margin-left: 1rem;
            font-size: 1.7rem;
            color: var(--primary-dark);
            letter-spacing: -0.8px;
        }

        header nav {
            flex: 2 1 400px;
        }

        header nav ul {
            display: flex;
            list-style-type: none;
            justify-content: space-between;
            /* 修改為 space-between 使導航平分空間 */
            width: 100%;
            max-width: 500px;
            /* 限制最大寬度，避免過度分散 */
            margin: 0 auto;
            /* 水平居中 */
            padding: 0;
        }

        header nav ul li a {
            color: var(--primary);
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 500;
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            transition: all 0.3s;
            letter-spacing: -0.3px;
        }

        header nav ul li a:hover,
        header nav ul li a:focus {
            color: var(--secondary);
            background-color: rgba(141, 110, 99, 0.1);
        }

        header nav ul li a:active {
            transform: scale(0.98);
        }

        main section.backImage {
            min-height: 100vh;
            width: 100%;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            padding: 0 1.5rem;
            z-index: 10;
        }

        main section.backImage h3 {
            font-size: clamp(2rem, 4.5vw, 3.5rem);
            color: var(--light);
            max-width: 900px;
            margin-bottom: 2.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            line-height: 1.35;
            letter-spacing: -1.2px;
            position: relative;
            z-index: 2;
        }

        main section.backImage button.start {
            padding: 0.8rem 1.8rem;
            border: none;
            border-radius: 8px;
            font-size: 1.3rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
            background-color: var(--secondary);
            color: white;
            box-shadow: 0 4px 15px rgba(141, 110, 99, 0.6);
            letter-spacing: -0.5px;
            position: relative;
            z-index: 2;
        }

        main section.backImage button.start:hover {
            background-color: #7a5e54;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(141, 110, 99, 0.8);
        }

        main section.backImage button.start:active {
            transform: translateY(1px);
        }

        section.second {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 5rem 1.5rem;
            background-color: var(--light);
        }

        section.second h2 {
            font-size: clamp(1.7rem, 3.8vw, 2.6rem);
            margin: 1.5rem 0 3rem;
            color: var(--primary-dark);
            max-width: 900px;
            line-height: 1.45;
            letter-spacing: -0.7px;
        }

        section.second h2 span {
            color: var(--primary);
            display: block;
            margin-top: 0.5rem;
        }

        section.second section.cards {
            display: flex;
            width: 100%;
            max-width: 1400px;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2.5rem;
            padding: 1rem 0;
        }

        section.second section.cards div.card {
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2.2rem 1.8rem;
            flex: 1 1 320px;
            max-width: 380px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        section.second section.cards div.card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        section.second section.cards div.card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        section.second section.cards div.card h4 {
            font-size: 1.8rem;
            margin: 1.4rem 0 1.1rem;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
        }

        section.second section.cards div.card p {
            font-size: 1.12rem;
            text-align: center;
            line-height: 1.8;
            color: #555;
            margin-bottom: 1.5rem;
            min-height: 110px;
            letter-spacing: -0.3px;
        }

        section.second section.cards div.card a {
            padding: 0.8rem 1.8rem;
            border: none;
            border-radius: 8px;
            font-size: 1.15rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-align: center;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            letter-spacing: -0.4px;
        }

        section.second section.cards div.card a:hover {
            background-color: #1e3a29;
            transform: scale(1.03);
        }

        section.second section.cards div.card a:active {
            transform: scale(0.98);
        }

        footer {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 10vh;
            justify-content: space-between;
            background: linear-gradient(to bottom, var(--primary-dark) 0%, #1a2a3a 100%);
            padding: 3rem 1.5rem 2rem;
            color: var(--light);
        }

        footer .copyright {
            font-size: 1.05rem;
            opacity: 0.85;
            text-align: center;
            max-width: 700px;
            line-height: 1.7;
            letter-spacing: -0.2px;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
            border: 2px solid var(--light);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary);
        }

        @media (max-width: 900px) {
            header {
                flex-direction: column;
                padding: 1rem;
            }

            header div.logo {
                margin: 0 0 1.2rem 0;
                justify-content: center;
            }

            header nav {
                width: 100%;
            }

            header nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.8rem;
            }

            section.second {
                padding: 3.5rem 1.5rem;
            }

            section.second h2 {
                font-size: 2.1rem;
            }

            footer {
                min-height: auto;
                padding: 2.5rem 1rem;
            }

            footer .copyright {
                font-size: 0.95rem;
                padding: 0 1rem;
            }
        }

        @media (max-width: 650px) {
            header {
                padding: 0.3rem 0.6rem;
                background-color: white;
            }

            header div.logo {
                margin: 0 0 0.5rem 0;
            }

            header div.logo img {
                width: 36px;
                height: 36px;
            }

            header div.logo h1 {
                font-size: 1.1rem;
                margin-left: 0.5rem;
            }

            header nav ul li a {
                font-size: 0.85rem;
                padding: 0.2rem 0.4rem;
            }

            header nav ul {
                gap: 0.4rem;
                padding: 0;
            }

            main section.backImage {
                min-height: auto;
                height: auto;
                margin-top: 90px;
                padding: 2rem 1rem 2rem;
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                text-align: center;
            }

            main section.backImage h3 {
                font-size: 2rem;
                margin-top: 1rem;
                margin-bottom: 1.5rem;
                line-height: 1.4;
                letter-spacing: -0.8px;
            }

            main section.backImage button.start {
                font-size: 1.15rem;
                padding: 0.65rem 1.4rem;
                margin-top: 1rem;
            }

            section.second h2 {
                font-size: 1.75rem;
            }

            section.second section.cards div.card {
                padding: 1.6rem 1rem;
            }

            section.second section.cards div.card img {
                width: 130px;
                height: 130px;
            }

            #fixed-bg {
                display: none;
            }

            main section.backImage {
                background: linear-gradient(rgba(26, 42, 58, 0.85), rgba(26, 42, 58, 0.9)),
                    url("../Pictures/creative-bg-mobile.jpg") center/cover no-repeat fixed;
            }
        }

        /* 修改 hero 容器為 flex 佈局 */
        .hero-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* 增加文字區域的寬度比例，減少影片區域的寬度比例 */
        .hero-content {
            flex: 0.6;
            /* 減少文字區域的flex比例，讓它佔更小空間 */
            max-width: 650px;
            padding-right: 3rem;
            /* 增加右側內邊距，給文字更多呼吸空間 */
        }

        .hero-video {
            flex: 0.9;
            /* 增加影片區域的flex比例，讓它佔更大空間 */
            max-width: 650px;
            /* 限制最大寬度，避免過大 */
        }

        .hero-video iframe {
            width: 100%;
            height: 450px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        /* 微調文字行高和間距，避免擠壓 */
        .hero-content h3 {
            line-height: 1.45;
            letter-spacing: -0.5px;
        }

        /* 響應式調整：小屏幕恢復垂直排列 */
        @media (max-width: 992px) {
            .hero-container {
                flex-direction: column;
                text-align: center;
            }

            .hero-content {
                padding-right: 0;
                margin-bottom: 2rem;
                max-width: 100%;
            }

            .hero-video {
                max-width: 100%;
                width: 100%;
            }

            .hero-video iframe {
                height: 400px;
            }
        }

        @media (max-width: 650px) {
            .hero-video iframe {
                height: 250px;
            }

            .hero-content h3 {
                font-size: 1.8rem;
                line-height: 1.3;
            }
        }
    </style>
</head>

<body>
    <!-- 創作背景動畫 -->
    <div id="fixed-bg">
        <canvas id="c2"></canvas>
        <canvas id="c"></canvas>
    </div>
    <?php
    // 購物車商品數量計算（放在 session_start() 之後）
    $cart_count = 0;
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        // 已登入：從資料庫查詢
        require_once 'config/database.php';
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $cart_count = $stmt->fetchColumn();
        } catch (PDOException $e) {
            $cart_count = 0;
        }
    } else {
        // 未登入：從 session 讀取
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $cart_count = array_sum(array_column($_SESSION['cart'], 'quantity'));
        }
    }
    ?>
    <header>
        <nav class="nav-links">
            <ul>
                <li><a href="index.php">首頁</a></li>
                <li><a href="products.php">商品頁面</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
        <!-- 搜尋欄與圖示區域 -->
        <div class="header-actions">
            <div class="search-container">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path
                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                </svg>
                <input type="text" placeholder="搜尋電繪板、品牌或配件..." id="searchInput">
            </div>
            <a href="cart.php" class="nav-icon" aria-label="購物車">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                <span class="nav-icon-badge"><?php echo (int) $cart_count; ?></span>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- 已登入顯示用戶頭像 -->
                <a href="member.php" class="nav-icon" aria-label="會員中心">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                    </svg>
                </a>
            <?php else: ?>
                <!-- 未登入顯示登入按鈕 -->
                <a href="login.php" class="nav-icon" aria-label="會員登入">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <section class="backImage" id="home">
            <div class="hero-container">
                <div class="hero-content">
                    <h3>
                        準備好釋放了嗎？<br>
                        藝術之旅從這裡開始
                    </h3>
                    <button class="start">探索電繪板</button>
                </div>
                <div class="hero-video">
                    <iframe width="560" height="315"
                        src="https://www.youtube.com/embed/W4_ih8nOJXg?autoplay=1&mute=1&loop=1&playlist=W4_ih8nOJXg"
                        title="YouTube video player" frameborder="0"
                        allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen>
                    </iframe>
                </div>
            </div>
        </section>
    </main>
    <section class="second">
        <h2>
            我們助您打造最流暢的<br>
            <span>數位創作體驗</span>
        </h2>
        <section class="cards">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $product): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <a href="product.php?id=<?php echo $product['id']; ?>">技術規格</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- 沒有資料時顯示預設內容 -->
                <div class="card">
                    <img src="./Pictures/huion-kamvas-pro-16.jpg" alt="專業繪圖電繪板">
                    <h4>精準筆觸技術</h4>
                    <p>8192級壓感筆搭配無延遲偵測，完美還原真實筆觸。支援多種創意軟體，讓您的創作不受限制。</p>
                    <a href="info.php#specs">技術規格</a>
                </div>
                <div class="card">
                    <img src="./Pictures/resize.webp" alt="產品保固">
                    <h4>三年全面保固</h4>
                    <p>所有電繪板均享三年全面保固服務，包含非人為損壞維修。24小時專業客服，解決您的技術問題。</p>
                    <a href="info.php#warranty">保固條款</a>
                </div>
                <div class="card">
                    <img src="./Pictures/wacom-one-14-4.webp" alt="創作軟體">
                    <h4>專業創作套件</h4>
                    <p>購買新電繪板即贈專業繪圖軟體套組，包含Clip Studio Paint、Adobe Fresco等價值$3,000元的創作工具。</p>
                    <a href="info.php#software">軟體詳情</a>
                </div>
            <?php endif; ?>
        </section>
    </section>
    <!-- 簡化後的頁腳 - 僅保留版權 -->
    <footer>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店 | 專業數位創作設備認證合作夥伴</p>
    </footer>
    <!-- 重新設計的側邊欄 -->
    <div class="inu-sidebar-shell">
        <ul class="inu-sidebar-nav">
            <!-- 品牌標識 -->
            <li class="inu-sidebar-active" id="inu-sidebar-logo">
                <a href="index.php">
                    <div class="inu-sidebar-icon">
                        <div class="inu-sidebar-imageBox">
                            <img src="./Icons/icon.gif" alt="ArtCanvas 電繪板標誌"
                                style="width: 30px; height: 30px; object-fit: contain;">
                        </div>
                    </div>
                    <div class="inu-sidebar-text">ArtCanvas 電繪板</div>
                </a>
            </li>
            <!-- 主要導航 -->
            <li>
                <a href="index.php">
                    <div class="inu-sidebar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.5v3.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5z" />
                        </svg>
                    </div>
                    <div class="inu-sidebar-text">首頁</div>
                </a>
            </li>
            <li>
                <a href="about.php">
                    <div class="inu-sidebar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                            <path
                                d="M8.93 6.588l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.183.852c-.125.573-.49 1.03-.972 1.293a4.02 4.02 0 0 1-1.15-.212c-.477-.238-.78-.66-.78-1.142 0-.726.486-1.276 1.162-1.276.53 0 .96.292 1.069.737l.183.852c.048.238-.156.477-.5.477-.278 0-.462-.183-.49-.422l-.183-.852c-.09-.436-.549-.743-1.096-.743-.726 0-1.276.549-1.276 1.276 0 .627.36 1.093.83 1.293.578.245 1.118.327 1.564.327.592 0 1.118-.113 1.564-.327.47-.2 1.026-.687 1.026-1.48 0-.573-.212-1.026-.578-1.293.073-.347-.048-.726-.5-1.069l-.45-.083-.034-.172c.142-.573.607-1.012 1.15-1.012.622 0 1.106.44 1.106 1.106 0 .104-.012.22-.034.333zm.432 8.178a.5.5 0 0 1 .568.428l.183.852c.073.347.05.713-.212 1.012-.288.327-.743.5-1.276.5-1.106 0-2-1.054-2-2.25 0-.53.212-.96.578-1.293.347-.327.83-.5 1.422-.5.592 0 1.075.172 1.422.5.366.333.578.763.578 1.293 0 .53-.212.96-.578 1.293a2.25 2.25 0 0 1-1.422.5c-.53 0-.96-.172-1.276-.5a1.5 1.5 0 0 1-.212-1.012l.183-.852a.5.5 0 0 1 .428-.387z" />
                        </svg>
                    </div>
                    <div class="inu-sidebar-text">品牌故事</div>
                </a>
            </li>
            <li>
                <a href="info.php">
                    <div class="inu-sidebar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M8.06 2.637a.5.5 0 0 1 .44.248l1.427 2.377a.5.5 0 0 1-.065.565l-1.71 1.381a.5.5 0 0 1-.628-.13l-.94-.705a.5.5 0 0 0-.758.13l-1.71 1.38a.5.5 0 0 1-.628.13l-.94-.705a.5.5 0 0 0-.758.13L.526 9.435a.5.5 0 0 1-.786-.412L1.267.538a.5.5 0 0 1 .963-.102L8.06 2.637zM1 11.414l5.94-5.94.707.53L5.793 8.92l4.444 1.536a.5.5 0 0 0 .383-.09l2.708-2.708 1.415 1.415L1 12.414v-1zM14 13a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6.586l6-6 6 6V13z" />
                        </svg>
                    </div>
                    <div class="inu-sidebar-text">產品支援</div>
                </a>
            </li>
            <li>
                <a href="contact.php">
                    <div class="inu-sidebar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                            viewBox="0 0 16 16">
                            <path
                                d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-2 3.5c0 .71-.59 1.29-1.3 1.29H3.3a1.3 1.3 0 0 1-1.3-1.29V5.29c0-.71.59-1.29 1.3-1.29h9.4c.71 0 1.3.58 1.3 1.29v6.22z" />
                            <path d="M8 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 6a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
                        </svg>
                    </div>
                    <div class="inu-sidebar-text">技術支援</div>
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="member.php">
                        <div class="inu-sidebar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                            </svg>
                        </div>
                        <div class="inu-sidebar-text">會員中心</div>
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="login.php">
                        <div class="inu-sidebar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                                viewBox="0 0 16 16">
                                <path
                                    d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                            </svg>
                        </div>
                        <div class="inu-sidebar-text">會員登入</div>
                    </a>
                </li>
            <?php endif; ?>
            <!-- 分隔線 -->
            <li class="inu-sidebar-divider"></li>
            <!-- 社群媒體連結 -->
            <li class="inu-sidebar-social-item">
                <a href="#" aria-label="Facebook 創作社群">
                    <img src="./Icons/facebook.svg" alt="Facebook 創作社群" class="inu-sidebar-social-icon">
                    <div class="inu-sidebar-social-text">創作社群</div>
                </a>
            </li>
            <li class="inu-sidebar-social-item">
                <a href="#" aria-label="Instagram 創作分享">
                    <img src="./Icons/instagram.svg" alt="Instagram 創作分享" class="inu-sidebar-social-icon">
                    <div class="inu-sidebar-social-text">創作分享</div>
                </a>
            </li>
            <li class="inu-sidebar-social-item">
                <a href="#" aria-label="YouTube 教學頻道">
                    <img src="./Icons/youtube.svg" alt="YouTube 教學頻道" class="inu-sidebar-social-icon">
                    <div class="inu-sidebar-social-text">教學頻道</div>
                </a>
            </li>
            <li class="inu-sidebar-social-item">
                <a href="#" aria-label="Line 技術支援">
                    <img src="./Icons/line.svg" alt="Line 技術支援" class="inu-sidebar-social-icon">
                    <div class="inu-sidebar-social-text">技術支援</div>
                </a>
            </li>
        </ul>
    </div>
    <script>
        // 側邊欄點擊事件
        const sidebarItems = document.querySelectorAll('.inu-sidebar-nav > li:not(.inu-sidebar-divider)');
        sidebarItems.forEach(item => {
            if (!item.classList.contains('inu-sidebar-social-item')) {
                item.addEventListener('click', function (e) {
                    if (this.classList.contains('inu-sidebar-divider')) return;
                    if (this.classList.contains('inu-sidebar-social-item')) {
                        return;
                    }
                    e.preventDefault();
                    sidebarItems.forEach(el => {
                        if (!el.classList.contains('inu-sidebar-social-item') &&
                            !el.classList.contains('inu-sidebar-divider')) {
                            el.classList.remove('inu-sidebar-active');
                        }
                    });
                    this.classList.add('inu-sidebar-active');
                    const target = this.querySelector('a').getAttribute('href');
                    if (target.startsWith('#')) {
                        document.querySelector(target).scrollIntoView({
                            behavior: 'smooth'
                        });
                    } else {
                        window.location.href = target;
                    }
                });
            }
        });
        if ('ontouchstart' in document.documentElement) {
            document.body.classList.add('touch-device');
        } else {
            const sidebar = document.querySelector('.inu-sidebar-shell');
            sidebar.addEventListener('mouseenter', () => {
                sidebar.style.boxShadow = '5px 0 25px rgba(0, 0, 0, 0.15)';
            });
            sidebar.addEventListener('mouseleave', () => {
                sidebar.style.boxShadow = '2px 0 15px rgba(0, 0, 0, 0.1)';
            });
        }

        // 搜尋功能
        document.getElementById('searchInput')?.addEventListener('input', function (e) {
            const query = e.target.value.trim();
            if (query.length >= 2) {
                // AJAX 搜尋功能，稍後可實作
                console.log('搜尋:', query);
            }
        });
    </script>
    <script src="./bootstrap-5.2.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 頭部陰影效果
        const header = document.querySelector("header");
        window.addEventListener("scroll", () => {
            header.style.boxShadow = window.scrollY > 0
                ? "0 4px 12px rgba(0, 0, 0, 0.15)"
                : "none";
        });
        // 平滑滾動
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
            // 探索裝備按鈕
            document.querySelector('.start')?.addEventListener('click', () => {
                document.querySelector('.second').scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>

</html>