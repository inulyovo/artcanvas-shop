<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>品牌故事 | ArtCanvas 電繪板專賣店</title>
    <meta name="description" content="ArtCanvas 成立於2018年，由專業插畫家與工程師團隊共同創立，致力提供最精準、流暢的數位創作體驗。" />
    <link rel="preconnect" href="https://fonts.gstatic.com" />
    <link href="https://fonts.googleapis.com/css2?family=LXGW+WenKai+Mono+TC:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./style.css" />
    <style>
        #fixed-bg { display: none; }
        .about-section {
            background-color: var(--light);
            padding: 4rem 1.5rem;
        }
        .about-content {
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
        .about-content h2 {
            color: var(--primary);
            text-align: center;
            margin: 1.5rem 0 2rem;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
        }
        .about-content p {
            margin-bottom: 1.4rem;
        }
        .values {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin: 2.5rem 0;
        }
        .value-card {
            flex: 1 1 200px;
            background: var(--accent);
            padding: 1.4rem;
            border-radius: 10px;
            color: var(--primary-dark);
            font-weight: 500;
        }
        .value-card h4 {
            margin-bottom: 0.6rem;
            font-size: 1.3rem;
        }
        @media (max-width: 650px) {
            .about-section { padding: 2.5rem 1rem; }
            .about-content { padding: 1.8rem; font-size: 1.05rem; }
        }
        
        /* 修復圖示溢出問題 */
        header .logo img,
        footer .logo img {
            width: 48px;
            height: auto;
            max-height: 52px;
            object-fit: contain;
            margin-right: 12px;
        }
        
        @media (min-width: 900px) {
            header .logo img,
            footer .logo img {
                width: 56px;
                max-height: 60px;
            }
        }
        
        @media (max-width: 650px) {
            header .logo img,
            footer .logo img {
                width: 40px;
                max-height: 44px;
                margin-right: 8px;
            }
            
            header .logo h1,
            footer .logo h1 {
                font-size: 1.3rem;
            }
        }
        
        footer .logo {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
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

    <section class="about-section">
        <div class="about-content">
            <h2>我們的故事</h2>
            <p>
                「ArtCanvas 電繪板專賣店」成立於 2018 年，由一群專業插畫家與硬體工程師共同創立。
                我們深知：一幅動人的數位作品，始於精準流暢的創作工具。
            </p>
            <p>
                當年，創辦人 Anna（資深插畫家）在使用多款電繪板後，發現市面產品常存在壓感不精準、驅動不穩定、
                售後支援不足等問題，嚴重影響創作效率與體驗。於是，她與兩位硬體工程師夥伴決定打造一個
                「真正理解創作者需求」的電繪板品牌，從使用者體驗出發，專注於技術精進與完善支援。
            </p>

            <h2>我們的核心價值</h2>
            <div class="values">
                <div class="value-card">
                    <h4>精準至上</h4>
                    <p>每一塊繪圖板都經過藝術家實測校準，追求0.001mm的定位精準度。</p>
                </div>
                <div class="value-card">
                    <h4>創作者中心</h4>
                    <p>所有產品設計由專業插畫家參與開發，真實解決創作痛點。</p>
                </div>
                <div class="value-card">
                    <h4>技術無憂</h4>
                    <p>提供24/7技術支援，讓您專注創作，不被技術問題打斷靈感。</p>
                </div>
            </div>

            <h2>我們的承諾</h2>
            <p>
                從入門級 XP-Pen 到專業級 Wacom Cintiq，每一款產品都經過我們團隊超過 200 小時的實測。
                我們不只銷售硬體，更提供完整的創作生態系——專業軟體套件、線上教學資源、
                一對一技術諮詢，以及全台最快的維修服務。
            </p>
            <p>
                無論您是剛接觸數位繪圖的新手，還是需要高效工作流程的職業插畫家，
                ArtCanvas 都將成為您最可靠的創作夥伴，見證每一幅作品從靈感誕生到完成的全過程。
            </p>
            
            <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #eaeaea;">
                <p style="font-size: 1.2rem; color: var(--primary); font-weight: 500; letter-spacing: -0.3px;">
                    「最好的工具，是讓你忘記工具的存在，只記得創作的感動。」<br>
                    <span style="font-size: 1rem; color: #666; font-weight: 300;">— ArtCanvas 創辦團隊</span>
                </p>
            </div>
        </div>
    </section>

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
            <a href="#" aria-label="Facebook"><img src="./Icons/facebook.svg" alt="Facebook 創作社群" /></a>
            <a href="#" aria-label="Instagram"><img src="./Icons/instagram.svg" alt="Instagram 創作分享" /></a>
            <a href="#" aria-label="YouTube"><img src="./Icons/youtube.svg" alt="YouTube 教學頻道" /></a>
            <a href="#" aria-label="Line"><img src="./Icons/line.svg" alt="Line 技術支援" /></a>
        </section>
        <p class="copyright">© 2025 ArtCanvas 電繪板專賣店 | 專業數位創作設備認證合作夥伴</p>
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