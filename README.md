# ArtCanvas 電繪板專賣店

> 中彰投職訓局數位內容課程結業專案｜垂直電商平台 × 創作者社群

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

---

## 專案簡介

本專案為中彰投職訓局「數位內容開發」課程之結業成果，打造一個專為數位創作者服務的垂直電商平台。結合「硬體銷售」與「作品展示」雙核心，讓使用者不僅能購買專業電繪板（Wacom、XP-Pen、Huion），更能建立個人創作 Portfolio，實踐社群互動理念。

### 線上演示
- Demo 連結：http://inuly.infinityfreeapp.com（待網站開通部署）
- GitHub 仓库：https://github.com/inulyovo/artcanvas-shop

---

## 技術架構

### 後端
- PHP 8.0+：原生開發，邏輯清晰易維護
- MySQL 8.0：關係型資料庫，資料結構規範化
- PDO 預處理語句：防止 SQL 注入，確保資安
- password_hash()：密碼加密儲存，符合現代資安標準

### 前端
- HTML5 + CSS3：語意化標籤、CSS 變數、響應式設計
- JavaScript (ES6+)：AJAX 無刷新更新購物車、表單驗證
- GSAP 3.12：專業動畫庫，提升互動體驗
- Bootstrap 5.2.3：網格系統輔助版面配置

### 開發工具
- Git + GitHub：版本控制與協作
- XAMPP：本地開發環境
- VS Code：程式編輯器

---

## 核心功能

### 購物系統
- 混合式購物車：未登入使用 Session、已登入同步至資料庫
- 智能運費計算：訂單滿 NT$10,000 自動免運
- 優惠碼機制：支援折扣套用與即時總價更新
- AJAX 無刷新操作：數量調整、商品移除即時回饋

### 會員系統
- 安全登入/註冊：帳號密碼驗證 + Session 狀態管理
- 個人資料管理：密碼修改、電子信箱更新
- 訂單歷史查詢：完整購買紀錄與狀態追蹤

### 創作者作品集
- 作品上傳/編輯/刪除：完整 CRUD 功能
- 圖片壓縮與路徑管理：優化儲存效率
- 個人作品牆：展示數位創作，增強用戶黏著度

### 資安防護
- PDO 預處理語句防止 SQL 注入
- password_hash + password_verify 密碼加密
- 表單輸入過濾與 XSS 防護
- Session 驗證機制保護敏感頁面

---

## 專案結構

```
project01/
├── config.php              # 資料庫連線設定（本地使用，勿上傳）
├── config.example.php      # 設定範本（可公開）
├── index.php               # 首頁
├── products.php            # 商品列表
├── cart.php                # 購物車
├── checkout.php            # 結帳流程
├── login.php / register.php # 會員登入註冊
├── account.php             # 帳號設定
├── portfolio.php           # 作品集管理
├── style.css               # 全局樣式
├── js/                     # JavaScript 模組
├── css/                    # 額外樣式表
├── Pictures/               # 商品與背景圖片
├── Icons/                  # SVG 圖示資源
└── .gitignore              # Git 忽略設定
```

---

## 本地安裝步驟

1. 環境準備
   - 安裝 XAMPP（含 Apache + PHP + MySQL）
   - 啟動 Apache 與 MySQL 服務

2. 專案部署
   - 將專案複製至 htdocs 資料夾：`C:\xampp\htdocs\project01`

3. 資料庫設定
   - 開啟 phpMyAdmin，建立資料庫：
     ```sql
     CREATE DATABASE artcanvas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   - 匯入資料表結構（需準備 artcanvas.sql）

4. 設定連線
   - 複製 `config.example.php` 為 `config.php`
   - 填寫您的資料庫帳號密碼

5. 啟動測試
   - 瀏覽器開啟：`http://localhost/project01`

---

## 部署建議

本專案採用傳統 PHP + MySQL 架構，推薦部署平台：

| 平台 | 類型 | 適合情境 |
|------|------|----------|
| InfinityFree | 免費 PHP 主機 | 學習/演示 |
| Railway / Render | PaaS 平台 | 進階部署 |
| Hostinger | 付費虛擬主機 | 正式上線 |

注意：不建議使用 Vercel，因專案依賴傳統 Session 與檔案上傳，與 Serverless 環境不相容。

---

## 開發日誌

- 2026/01：專案初始化，完成資料庫設計
- 2026/02：會員系統與購物車核心功能實作
- 2026/02：作品集模組與 AJAX 互動優化
- 2026/03：響應式設計與資安強化


---

## 開發團隊

| 角色 | 姓名 | 貢獻 |
|------|------|------|
| 全端開發 | inuly | 專案架構、前後端實作、UI 設計 |
| 課程指導 | 中彰投職訓局講師 | 技術諮詢與程式審查 |

---

## 授權條款

本專案採用 MIT License 開放原始碼授權，歡迎學習參考。  
專案中商品圖片與品牌商標歸屬原權利人所有，僅供教學演示使用。

備註：本專案為職訓課程學習成果，部分功能為模擬實作，正式商用需補充金流、物流與客服系統整合。
