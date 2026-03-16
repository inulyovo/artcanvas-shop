<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>核心技術 | ArtCanvas 電繪板專賣店</title>
    <meta name="description" content="ArtCanvas 電繪板專賣店的精準筆觸技術、三年全面保固與專業創作套件詳情" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        /* === 修復圖示溢出問題 === */
        header .logo img,
        footer .logo img {
            width: 48px; /* 固定合適寬度 */
            height: auto; /* 保持比例 */
            max-height: 52px; /* 限制最大高度 */
            object-fit: contain; /* 確保完整顯示 */
            margin-right: 12px; /* 與標題的間距 */
        }
        
        /* 桌面版微調 */
        @media (min-width: 900px) {
            header .logo img,
            footer .logo img {
                width: 56px;
                max-height: 60px;
            }
        }
        
        /* 隱藏首頁的 canvas 背景（確保無衝突） */
        #fixed-bg {
            display: none;
        }

        /* 內容區樣式 */
        .info-section {
            background-color: var(--light);
            padding: 4rem 1.5rem;
        }

        .info-section h2 {
            text-align: center;
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            color: var(--primary-dark);
            margin-bottom: 3rem;
            letter-spacing: -0.7px;
        }

        .info-content {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            line-height: 1.8;
            color: #444;
            font-size: 1.15rem;
            letter-spacing: -0.3px;
        }

        .info-content h3 {
            color: var(--primary);
            margin: 1.8rem 0 1.2rem;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
        }
        
        .info-content h4 {
            color: var(--secondary);
            margin: 1.4rem 0 0.8rem;
            font-size: 1.3rem;
        }

        .info-content p {
            margin-bottom: 1.2rem;
        }

        .info-content ul {
            padding-left: 1.8rem;
            margin: 1.2rem 0;
        }

        .info-content li {
            margin-bottom: 0.6rem;
        }

        /* 響應式調整 */
        @media (max-width: 650px) {
            /* 縮小logo圖示 */
            header .logo img,
            footer .logo img {
                width: 40px;
                max-height: 44px;
                margin-right: 8px;
            }
            
            header .logo h1,
            footer .logo h1 {
                font-size: 1.3rem; /* 縮小標題 */
            }
            
            .info-section {
                padding: 2.5rem 1rem;
            }
            .info-content {
                padding: 1.8rem;
                font-size: 1.05rem;
            }
            .info-content h3 {
                font-size: 1.45rem;
            }
        }
        
        /* 確保footer標誌區整體平衡 */
        footer .logo {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- 頁首（更新為電繪主題） -->
    <header>
        <div class="logo">
            <a href="index.php"><img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌"></a>
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="login.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
    </header>

    <!-- 精準筆觸技術 -->
    <section class="info-section" id="precision">
        <h2>精準筆觸技術</h2>
        <div class="info-content">
            <h3>8192級壓感，還原真實筆觸感受</h3>
            <p>我們的電繪板搭載最先進的<strong>8192級壓感技術</strong>，能精準捕捉0.1克的力度變化。結合0.002秒的超低延遲感應，讓每一筆線條都如實呈現您的創作意圖。</p>
            
            <h4>核心技術優勢</h4>
            <ul>
                <li><strong>無死角感應區：</strong>100%有效繪圖區域，邊緣不失真</li>
                <li><strong>傾斜感應：</strong>支援±60°傾斜角偵測，完美模擬鉛筆、炭筆效果</li>
                <li><strong>防手掌誤觸：</strong>智能手掌拒絕技術，創作更自由</li>
                <li><strong>多層壓力曲線：</strong>可自訂壓力曲線，適應不同創作風格</li>
            </ul>
            
            <p> <strong>專業提示：</strong>搭配我們的專用筆尖套組（含毛筆、鋼筆、鉛筆等6種材質），可進一步提升特定媒介的模擬真實度。</p>
            <p>所有技術規格均通過日本數位藝術家協會認證，確保專業創作需求得到滿足。</p>
        </div>
    </section>

    <!-- 三年全面保固 -->
    <section class="info-section" id="warranty">
        <h2>三年全面保固</h2>
        <div class="info-content">
            <h3>全方位保障，專注創作無後顧之憂</h3>
            <p>所有ArtCanvas電繪板均享有<strong>三年原廠全面保固</strong>，涵蓋所有硬體元件與驅動程式問題。保固期間內，我們提供免費維修、零件更換，甚至必要時整機更換服務。</p>
            
            <h4>保固涵蓋項目</h4>
            <ul>
                <li><strong>感應面板：</strong>表面磨損、感應失靈、定位偏移</li>
                <li><strong>壓感筆：</strong>筆尖磨損、側鍵失靈、感應元件故障</li>
                <li><strong>連接系統：</strong>USB/Bluetooth連接異常、驅動相容問題</li>
                <li><strong>控制元件：</strong>快捷鍵、觸控環、滾輪功能失效</li>
            </ul>

            <h4>保固不涵蓋項目</h4>
            <ul>
                <li>人為物理損壞（摔落、壓傷、液體侵入）</li>
                <li>未使用原廠配件或非官方改裝造成的損壞</li>
                <li>自然災害或不可抗力因素造成的損壞</li>
                <li>正常磨耗（如筆尖自然磨損）</li>
            </ul>

            <p>申請保固時，您只需登入<strong>會員中心</strong>提交申請，我們將安排<strong>到府收件</strong>服務。維修完成後，新機器或修復後的產品將直接送回您手中，全程無需親自送修。</p>
        </div>
    </section>

    <!-- 專業創作套件 -->
    <section class="info-section" id="software">
        <h2>專業創作套件</h2>
        <div class="info-content">
            <h3>購機即贈完整創作生態系</h3>
            <p>每台新購電繪板皆附贈價值<strong>$3,000元</strong>的專業創作軟體套組，涵蓋插畫、漫畫、3D建模等全方位創作需求。這些軟體均已完成最佳化設定，開箱即用，省去繁瑣設定時間。</p>
            
            <h4>套件內容詳解</h4>
            <ul>
                <li><strong>Clip Studio Paint EX：</strong>完整版一年授權（價值$1,200），專業漫畫與插畫工具</li>
                <li><strong>Adobe Fresco：</strong>完整版兩年授權（價值$1,000），真實水彩與油畫模擬</li>
                <li><strong>Blender：</strong>專業3D建模與動畫軟體（終身免費更新）</li>
                <li><strong>ArtCanvas專屬筆刷庫：</strong>1000+專業筆刷，含日系插畫、水墨、厚塗等風格</li>
                <li><strong>線上教學課程：</strong>8小時基礎到進階教學（價值$800）</li>
            </ul>

            <p>➡️ <a href="#" style="color: var(--primary); text-decoration: underline;">點此下載完整軟體安裝指南與序號兌換流程</a></p>
            <p>我們也提供<strong>一對一遠端設定服務</strong>，專業工程師將協助您完成所有軟體安裝與設定，確保您的創作流程零障礙開始。</p>
        </div>
    </section>

    <!-- 頁尾（更新為電繪主題） -->
    <footer>
        <div class="logo">
            <img src="./Icons/icon.gif" alt="ArtCanvas 電繪板專賣店標誌" />
            <h1>ArtCanvas 電繪板</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">首頁</a></li>
                <li><a href="about.php">品牌故事</a></li>
                <li><a href="login.php">會員中心</a></li>
                <li><a href="contact.php">技術支援</a></li>
            </ul>
        </nav>
        <section class="links">
            <a href="#" aria-label="Facebook"><img src="./Icons/facebook.svg" alt="Facebook" /></a>
            <a href="#" aria-label="Instagram"><img src="./Icons/instagram.svg" alt="Instagram" /></a>
            <a href="#" aria-label="YouTube"><img src="./Icons/youtube.svg" alt="YouTube教學頻道" /></a>
            <a href="#" aria-label="twitter"><img src="./Icons/line.svg" alt="最新優惠"></a>
        </section>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店 | 專業數位創作設備認證合作夥伴</p>
    </footer>

    <script>
        // 頭部陰影效果（與首頁一致）
        const header = document.querySelector("header");
        window.addEventListener("scroll", () => {
            header.style.boxShadow = window.scrollY > 0 
                ? "0 4px 12px rgba(0, 0, 0, 0.15)" 
                : "none";
        });
    </script>
</body>
</html>