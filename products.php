<?php
session_start();

// 資料庫連線
require_once 'config/database.php';

try {
    $pdo = getDatabaseConnection();

    // 取得所有商品（按品牌分組）
    $stmt = $pdo->query("
        SELECT * FROM products 
        WHERE status = 'active' 
        ORDER BY FIELD(brand, 'wacom', 'xp-pen', 'huion'), 
                 FIELD(size, 's', 'm', 'l')
    ");
    $products_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 將資料重構為與原 JS 物件相同的結構
    $products_js = [];
    foreach ($products_db as $p) {
        if (!isset($products_js[$p['brand'] . '-' . $p['model']])) {
            $products_js[$p['brand'] . '-' . $p['model']] = [];
        }
        $products_js[$p['brand'] . '-' . $p['model']][$p['size']] = [
            'name' => $p['name'],
            'description' => $p['description'],
            'pressure' => $p['pressure'],
            'area' => $p['area'],
            'connection' => $p['connection'],
            'features' => $p['features'],
            'price' => (int) $p['price'],
            'image' => $p['image_path']
        ];
    }

    // 如果沒有商品資料，使用預設資料（開發階段）
    if (empty($products_js)) {
        $products_js = [
            'wacom-intuos-pro' => [
                's' => [
                    'name' => 'Wacom Intuos Pro S號',
                    'description' => '專業級8192級壓感電繪板，精準捕捉每一筆細膩線條，適合插畫師與漫畫家。',
                    'pressure' => '8192級',
                    'area' => '157 × 98 mm',
                    'connection' => 'USB-C / 藍牙5.0',
                    'features' => '快捷鍵自訂、觸控環、防手掌誤觸',
                    'price' => 7800,
                    'image' => './Pictures/wacom-intuos-pro-s.jpg'
                ],
                'm' => [
                    'name' => 'Wacom Intuos Pro M號',
                    'description' => '專業級8192級壓感電繪板，精準捕捉每一筆細膩線條，適合插畫師與漫畫家。',
                    'pressure' => '8192級',
                    'area' => '224 × 148 mm',
                    'connection' => 'USB-C / 藍牙5.0',
                    'features' => '快捷鍵自訂、觸控環、防手掌誤觸',
                    'price' => 9800,
                    'image' => './Pictures/wacom-intuos-pro-m.jpg'
                ],
                'l' => [
                    'name' => 'Wacom Intuos Pro L號',
                    'description' => '專業級8192級壓感電繪板，精準捕捉每一筆細膩線條，適合插畫師與漫畫家。',
                    'pressure' => '8192級',
                    'area' => '314 × 208 mm',
                    'connection' => 'USB-C / 藍牙5.0',
                    'features' => '快捷鍵自訂、觸控環、防手掌誤觸',
                    'price' => 12800,
                    'image' => './Pictures/wacom-intuos-pro-l.jpg'
                ]
            ],
            'xp-pen-artist' => [
                's' => [
                    'name' => 'XP-Pen Artist 12',
                    'description' => '全螢幕繪圖顯示器，1920×1080高解析度，色彩準確度達92% Adobe RGB。',
                    'pressure' => '8192級',
                    'area' => '267 × 150 mm',
                    'connection' => 'USB-C / HDMI',
                    'features' => '全螢幕顯示、防眩光玻璃、可調式支架',
                    'price' => 15800,
                    'image' => './Pictures/xp-pen-artist-12.jpg'
                ],
                'm' => [
                    'name' => 'XP-Pen Artist 16',
                    'description' => '全螢幕繪圖顯示器，1920×1080高解析度，色彩準確度達92% Adobe RGB。',
                    'pressure' => '8192級',
                    'area' => '354 × 199 mm',
                    'connection' => 'USB-C / HDMI',
                    'features' => '全螢幕顯示、防眩光玻璃、可調式支架',
                    'price' => 19800,
                    'image' => './Pictures/xp-pen-artist-16.jpg'
                ]
            ],
            'huion-kamvas' => [
                'm' => [
                    'name' => 'Huion Kamvas Pro 16',
                    'description' => '專業繪圖顯示器，支援60°傾斜感應，配備8個可程式化快捷鍵。',
                    'pressure' => '8192級',
                    'area' => '354 × 199 mm',
                    'connection' => 'USB / HDMI / Mini DisplayPort',
                    'features' => '全螢幕顯示、快捷鍵、觸控條',
                    'price' => 16800,
                    'image' => './Pictures/huion-kamvas-pro-16.jpg'
                ]
            ]
        ];
    }

} catch (PDOException $e) {
    // 錯誤時使用預設資料
    $products_js = [
        'wacom-intuos-pro' => [
            'm' => [
                'name' => 'Wacom Intuos Pro M號',
                'description' => '專業級8192級壓感電繪板，精準捕捉每一筆細膩線條，適合插畫師與漫畫家。',
                'pressure' => '8192級',
                'area' => '224 × 148 mm',
                'connection' => 'USB-C / 藍牙5.0',
                'features' => '快捷鍵自訂、觸控環、防手掌誤觸',
                'price' => 9800,
                'image' => './Pictures/wacom-intuos-pro-m.jpg'
            ]
        ]
    ];
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
        .carousel-inner {
            color: #fff !important;
        }


        /* 添加半透明背景層，確保文字可讀 */
        .carousel-caption {
            background: rgba(0, 0, 0, 0.5);
            padding: 0.5rem 0.2rem;
            border-radius: 12px;
            backdrop-filter: blur(3px);
            left: 50%;
            transform: translateX(-50%);
            bottom: 7%;
        }



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


        #inu-sidebar-logo {
            margin: 40px 0 30px 0;
        }

        #inu-sidebar-logo .inu-sidebar-text {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #171717;
        }


        .inu-sidebar-shell:hover {
            width: 320px;

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


        .inu-sidebar-nav li.inu-sidebar-active::after {
            opacity: 0 !important;
            transform: translateX(-20px) !important;
        }


        .inu-sidebar-nav li.inu-sidebar-active {

            border-top-left-radius: 50px;
            border-bottom-left-radius: 50px;
            transform: none !important;
        }


        .inu-sidebar-nav li.inu-sidebar-active::before {
            content: "";
            position: absolute;
            top: -30px;
            right: 0;
            width: 30px;
            height: 30px;
            border-bottom-right-radius: 25px;

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


        .inu-sidebar-nav li.inu-sidebar-active .inu-sidebar-icon,
        .inu-sidebar-nav li.inu-sidebar-active .inu-sidebar-text {
            color: #fff !important;
        }


        .inu-sidebar-nav li.inu-sidebar-active:hover .inu-sidebar-icon,
        .inu-sidebar-nav li.inu-sidebar-active:hover .inu-sidebar-text {
            color: #fff !important;
        }


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


        .copyright {
            font-size: 14px;
            color: #aaa;
            margin: 0;
        }


        @media (prefers-color-scheme: dark) {
            .inu-sidebar-shell {
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.3);
            }
        }
    </style>
    <style>
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

            width: 100%;
            max-width: 500px;

            margin: 0 auto;

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
    </style>
</head>

<body>

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


        <div class="header-actions">
            <div class="search-container">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path
                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                </svg>
                <input type="text" placeholder="搜尋電繪板、品牌或配件...">
            </div>

            <a href="cart.php" class="nav-icon" aria-label="購物車">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                <span class="nav-icon-badge">
                    <?php echo (int) $cart_count; ?>
                </span>
            </a>

            <a href="login.php" class="nav-icon" aria-label="會員登入">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                </svg>
            </a>
        </div>
    </header>

    <section id="equipment-carousel-section">
        <div class="container-fluid p-0">
            <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"
                        aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"
                        aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"
                        aria-label="Slide 3"></button>
                </div>

                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="./Pictures/resize.webp" class="d-block w-100" alt="精準筆觸技術展示">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>精準至上</h5>
                            <p>8192級壓感技術，每一筆都精準捕捉您的創作意圖。</p>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <img src="./Pictures/wacom-one-14-4.webp" class="d-block w-100" alt="專業創作者使用場景">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>創作者中心</h5>
                            <p>所有產品由專業插畫家參與開發，真實解決創作痛點。</p>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <img src="./Pictures/wacon-one-14-tab-1-drawing.webp" class="d-block w-100" alt="技術支援團隊">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>技術無憂</h5>
                            <p>24/7專業技術支援，讓您專注創作，不被技術問題打斷靈感。</p>
                        </div>
                    </div>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    <section class="second">
        <div class="container">
            <div class="row align-items-center">

                <div class="col-md-6 mb-5 mb-md-0">
                    <div class="product-image-container position-relative">
                        <div class="image-gallery">
                            <div class="main-image">
                                <img src="./Pictures/wacom-intuos-pro.jpg" alt="Wacom Intuos Pro 電繪板"
                                    class="img-fluid rounded-3 shadow" id="product-main-image">
                            </div>
                            <div class="thumbnail-container d-flex gap-2 mt-3">
                                <button class="thumbnail-btn active" data-model="intuos-pro">
                                    <img src="./Pictures/wacom-intuos-pro-thumb.jpg" alt="Wacom Intuos Pro 縮圖">
                                </button>
                                <button class="thumbnail-btn" data-model="xp-pen-artist">
                                    <img src="./Pictures/xp-pen-artist-thumb.jpg" alt="XP-Pen Artist 縮圖">
                                </button>
                                <button class="thumbnail-btn" data-model="huion-kamvas">
                                    <img src="./Pictures/huion-kamvas-pro-16.jpg" alt="Huion Kamvas 縮圖">
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-lg-6">
                    <div class="product-content">
                        <h2 class="mb-3">Wacom Intuos Pro <span class="text-muted" id="product-model">M號</span></h2>
                        <div class="d-flex align-items-center mb-4">
                            <div class="rating me-3">
                                <span class="text-warning">★★★★★</span>
                            </div>
                            <span class="text-muted">(128位專業創作者推薦)</span>
                        </div>

                        <p class="lead mb-4" id="product-description">
                            專業級8192級壓感電繪板，精準捕捉每一筆觸的細微變化。搭載無線藍牙5.0連接，支援多點觸控手勢，適合專業插畫家與設計師使用。
                        </p>

                        <div class="specs mb-4">
                            <h4 class="mb-3">技術規格</h4>
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>壓感等級：</strong><span id="product-pressure">8192級</span></li>
                                <li class="mb-2"><strong>活動區域：</strong><span id="product-area">224 × 148 mm</span></li>
                                <li class="mb-2"><strong>連接方式：</strong><span id="product-connection">USB-C /
                                        藍牙5.0</span></li>
                                <li class="mb-2"><strong>特殊功能：</strong><span
                                        id="product-features">快捷鍵自訂、觸控環、防手掌誤觸</span></li>
                            </ul>
                        </div>

                        <div class="model-selection mb-4">
                            <h4 class="mb-3">選擇型號</h4>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary model-option active"
                                    data-size="s">S號 (小型)</button>
                                <button type="button" class="btn btn-outline-primary model-option" data-size="m">M號
                                    (中型)</button>
                                <button type="button" class="btn btn-outline-primary model-option" data-size="l">L號
                                    (大型)</button>
                            </div>
                        </div>

                        <div class="price-and-action d-flex flex-column flex-md-row align-items-md-center">
                            <div class="price-section me-md-4 mb-3 mb-md-0">
                                <span class="original-price text-decoration-line-through text-muted me-2">NT$
                                    12,800</span>
                                <span class="current-price fw-bold fs-3 text-primary">NT$ <span
                                        id="product-price">9,800</span></span>
                            </div>

                            <form action="cart_process.php" method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" id="product_id_input" value="1">
                                <!-- 初始值 1，但會被 JS 覆蓋 -->
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-lg px-4 py-2">
                                    <i class="bi bi-cart me-2"></i>加入購物車
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <script>

        const products = {
            'intuos-pro': {
                's': {
                    name: 'Wacom Intuos Pro S號',
                    description: '輕巧型8192級壓感電繪板，精準捕捉每一筆觸的細微變化。搭載無線藍牙5.0連接，支援多點觸控手勢，適合初學者到進階創作者使用。',
                    pressure: '8192級',
                    area: '157 × 98 mm',
                    connection: 'USB-C / 藍牙5.0',
                    features: '快捷鍵自訂、觸控環、防手掌誤觸',
                    price: '6,800',
                    image: './Pictures/wacom-intuos-pro-s.jpg'
                },
                'm': {
                    name: 'Wacom Intuos Pro M號',
                    description: '專業級8192級壓感電繪板，精準捕捉每一筆觸的細微變化。搭載無線藍牙5.0連接，支援多點觸控手勢，適合專業插畫家與設計師使用。',
                    pressure: '8192級',
                    area: '224 × 148 mm',
                    connection: 'USB-C / 藍牙5.0',
                    features: '快捷鍵自訂、觸控環、防手掌誤觸',
                    price: '9,800',
                    image: './Pictures/wacom-intuos-pro.jpg'
                },
                'l': {
                    name: 'Wacom Intuos Pro L號',
                    description: '旗艦級8192級壓感電繪板，提供最寬廣的創作空間。搭載無線藍牙5.0連接，支援多點觸控手勢，專為專業工作室與全職創作者設計。',
                    pressure: '8192級',
                    area: '314 × 195 mm',
                    connection: 'USB-C / 藍牙5.0',
                    features: '快捷鍵自訂、雙觸控環、防手掌誤觸、可更換面板',
                    price: '14,800',
                    image: './Pictures/wacom-intuos-pro-l.jpg'
                }
            },
            'xp-pen-artist': {
                's': {
                    name: 'XP-Pen Artist 12',
                    description: '高性價比12吋螢幕電繪板，1920×1080全高清顯示，8192級壓感筆，為數位藝術家提供流暢創作體驗。',
                    pressure: '8192級',
                    area: '256 × 144 mm',
                    connection: 'USB-C / HDMI',
                    features: '防眩光玻璃、快捷鍵、觸控條',
                    price: '12,500',
                    image: './Pictures/xp-pen-artist-12.jpg'
                },
                'm': {
                    name: 'XP-Pen Artist 16',
                    description: '15.6吋專業螢幕電繪板，QHD 2560×1440解析度，8192級壓感，色彩準確度達120% sRGB，適合專業插畫與漫畫創作。',
                    pressure: '8192級',
                    area: '344 × 194 mm',
                    connection: 'USB-C / HDMI / DisplayPort',
                    features: '防眩光玻璃、八快捷鍵、雙觸控條',
                    price: '19,800',
                    image: './Pictures/xp-pen-artist-16.jpg'
                },
                'l': {
                    name: 'XP-Pen Artist 22',
                    description: '21.5吋大螢幕電繪板，4K UHD解析度，100% Adobe RGB色域，提供最細膩的色彩表現，滿足專業影視與遊戲美術需求。',
                    pressure: '8192級',
                    area: '476 × 268 mm',
                    connection: 'USB-C / HDMI / DisplayPort',
                    features: '防眩光玻璃、十快捷鍵、雙觸控條、可調色溫',
                    price: '35,800',
                    image: './Pictures/xp-pen-artist-22.jpg'
                }
            },
            'huion-kamvas': {
                's': {
                    name: 'Huion Kamvas 13',
                    description: '13.3吋便攜式螢幕電繪板，1920×1080解析度，8192級壓感，輕巧設計適合外出創作，支援多種裝置連接。',
                    pressure: '8192級',
                    area: '293 × 165 mm',
                    connection: 'USB-C / Mini HDMI',
                    features: '防眩光螢幕、八快捷鍵、可調支架',
                    price: '8,800',
                    image: './Pictures/huion-kamvas-13.jpg'
                },
                'm': {
                    name: 'Huion Kamvas Pro 16',
                    description: '15.6吋專業螢幕電繪板，2.5K QHD解析度，140% sRGB廣色域，專為色彩要求嚴苛的插畫家與設計師打造。',
                    pressure: '8192級',
                    area: '344 × 194 mm',
                    connection: 'USB-C / HDMI / DisplayPort',
                    features: '防眩光玻璃、十快捷鍵、雙滾輪',
                    price: '16,800',
                    image: './Pictures/huion-kamvas-pro-16.jpg'
                },
                'l': {
                    name: 'Huion Kamvas Pro 24',
                    description: '23.8吋超大螢幕電繪板，4K UHD解析度，ΔE<2專業級色彩精準度，內建雙喇叭，是專業工作室的理想選擇。',
                    pressure: '8192級',
                    area: '527 × 296 mm',
                    connection: 'USB-C / HDMI / DisplayPort',
                    features: '防眩光玻璃、十二快捷鍵、三滾輪、可調RGB背光',
                    price: '42,800',
                    image: './Pictures/huion-kamvas-pro-24.jpg'
                }
            }
        };


        const modelOptions = document.querySelectorAll('.model-option');
        const thumbnailBtns = document.querySelectorAll('.thumbnail-btn');
        const mainImage = document.getElementById('product-main-image');


        let currentModel = 'intuos-pro';
        let currentSize = 'm';


        thumbnailBtns.forEach(btn => {
            btn.addEventListener('click', () => {

                thumbnailBtns.forEach(b => b.classList.remove('active'));

                btn.classList.add('active');


                currentModel = btn.dataset.model;
                updateProductContent();
            });
        });


        modelOptions.forEach(option => {
            option.addEventListener('click', () => {

                modelOptions.forEach(o => o.classList.remove('active'));

                option.classList.add('active');


                currentSize = option.dataset.size;
                updateProductContent();
            });
        });


        function updateProductContent() {
            const product = products[currentModel][currentSize];
            mainImage.src = product.image;
            mainImage.alt = product.name;
            document.querySelector('.product-content h2').innerHTML =
                product.name.split(' ')[0] + ' ' + product.name.split(' ')[1] +
                ' <span class="text-muted">' + product.name.split(' ')[2] + '</span>';
            document.getElementById('product-description').textContent = product.description;
            document.getElementById('product-pressure').textContent = product.pressure;
            document.getElementById('product-area').textContent = product.area;
            document.getElementById('product-connection').textContent = product.connection;
            document.getElementById('product-features').textContent = product.features;
            document.getElementById('product-price').textContent = product.price;

            // ✅【關鍵修正】動態設定 product_id
            const info = MODEL_MAP[currentModel];
            if (!info) {
                console.warn(`未支援的 model: ${currentModel}`);
                document.querySelector('input[name="product_id"]').value = 1;
                return;
            }

            const matched = <?php echo json_encode($products_db); ?>.find(p =>
                p.brand === info.brand && p.model === info.model && p.size === currentSize
            );

            if (matched) {
                document.querySelector('input[name="product_id"]').value = matched.id;
            } else {
                console.warn(`⚠️ 資料庫中無此商品：${info.brand}-${info.model} ${currentSize}`);
                document.querySelector('input[name="product_id"]').value = 1;
            }
        }


        updateProductContent();
    </script>

    <footer>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店 | 專業數位創作設備認證合作夥伴</p>
    </footer>


    <div class="inu-sidebar-shell">
        <ul class="inu-sidebar-nav">

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
            <li>
                <a href="login.php">
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


            <li class="inu-sidebar-divider"></li>


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
        const MODEL_MAP = {
            'intuos-pro': { brand: 'wacom', model: 'intuos-pro' },
            'xp-pen-artist': { brand: 'xp-pen', model: 'artist' },
            'huion-kamvas': { brand: 'huion', model: 'kamvas' }
        };
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
    </script>
    <script src="./bootstrap-5.2.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>

        const header = document.querySelector("header");
        window.addEventListener("scroll", () => {
            header.style.boxShadow = window.scrollY > 0
                ? "0 4px 12px rgba(0, 0, 0, 0.15)"
                : "none";
        });


        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });


            document.querySelector('.start')?.addEventListener('click', () => {
                document.querySelector('.second').scrollIntoView({ behavior: 'smooth' });
            });
        });

    </script>
</body>
<style>
    /* 產品展示區樣式 */
    .second {
        background: linear-gradient(to bottom, #f8f5f0 0%, #ffffff 100%);
        padding: 4rem 0;
    }

    .product-image-container {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        height: 100%;
    }

    .main-image {
        background: #f0f0f0;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .main-image:hover {
        box-shadow: 0 10px 25px rgba(46, 93, 63, 0.2);
    }

    .main-image img {
        max-width: 90%;
        max-height: 85%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .main-image img:hover {
        transform: scale(1.03);
    }

    .thumbnail-container {
        justify-content: center;
    }

    .thumbnail-btn {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        padding: 4px;
        transition: all 0.2s ease;
        opacity: 0.7;
    }

    .thumbnail-btn:hover {
        opacity: 1;
        transform: scale(1.05);
    }

    .thumbnail-btn.active {
        border-color: var(--primary);
        opacity: 1;
        box-shadow: 0 0 0 3px rgba(46, 93, 63, 0.2);
    }

    .thumbnail-btn img {
        width: 60px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }

    .product-content {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        height: 100%;
    }

    h2 {
        color: var(--primary-dark);
        font-size: 2.2rem;
        letter-spacing: -0.8px;
    }

    h4 {
        color: var(--primary);
        font-size: 1.4rem;
        margin-bottom: 1rem;
        letter-spacing: -0.5px;
    }

    .model-option {
        border: 2px solid var(--primary);
        color: var(--primary);
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .model-option:hover {
        background-color: var(--primary);
        color: white;
    }

    .model-option.active {
        background-color: var(--primary);
        color: white;
        box-shadow: 0 4px 10px rgba(46, 93, 63, 0.3);
    }

    .price-section {
        text-align: center;
        text-align: md-left;
    }

    .original-price {
        font-size: 1.2rem;
    }

    .current-price {
        color: #e63946;
        font-size: 1.8rem;
    }

    .btn-primary {
        background-color: var(--primary);
        border: none;
        font-weight: 600;
        letter-spacing: -0.3px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #1e3a29;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(46, 93, 63, 0.4);
    }

    /* 響應式調整 */
    @media (max-width: 991px) {
        .second {
            padding: 3rem 0;
        }

        .product-content {
            margin-top: 2rem;
        }

        .thumbnail-container {
            flex-wrap: wrap;
        }

        .thumbnail-btn {
            margin-bottom: 8px;
        }
    }

    @media (max-width: 768px) {
        .main-image {
            aspect-ratio: 4/3;
        }

        h2 {
            font-size: 1.8rem;
        }

        .current-price {
            font-size: 1.5rem;
        }
    }

    .second .container {
        max-width: 1600px;

        margin-left: auto;
        margin-right: auto;

        padding-left: 0;
        padding-right: 0;
    }


    @media (min-width: 992px) {
        .second .container {
            margin-right: 8%;

        }
    }

    @media (min-width: 1200px) {
        .second .container {
            margin-right: 10%;
        }
    }
</style>

</html>