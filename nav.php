<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>差异化侧边栏</title>
    <!-- 假设你已引入 iconfont，若无请替换为 Font Awesome 或移除 -->
    <style>
        /* === 仅作用于 inu-sidebar 的样式，避免污染全局 === */
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
            overflow: hidden;
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
        }

        /* Logo 特殊间距 */
        #inu-sidebar-logo {
            margin: 40px 0 100px 0;
        }

        .inu-sidebar-nav li a {
            display: flex;
            white-space: nowrap;
            color: inherit;
            text-decoration: none;
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
            transition: color 0.5s;
        }

        .inu-sidebar-imageBox {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
        }

        .inu-sidebar-imageBox img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            transition: color 0.5s;
        }

        /* Hover 效果 */
        .inu-sidebar-nav li:hover .inu-sidebar-icon,
        .inu-sidebar-nav li:hover .inu-sidebar-text {
            color: #ffa117;
        }

        /* Active 状态 */
        .inu-sidebar-nav li.inu-sidebar-active {
            background: #171717;
            border-top-left-radius: 50px;
            border-bottom-left-radius: 50px;
        }

        /* Active 伪元素：上角 */
        .inu-sidebar-nav li.inu-sidebar-active::before {
            content: "";
            position: absolute;
            top: -30px;
            right: 0;
            width: 30px;
            height: 30px;
            border-bottom-right-radius: 25px;
            box-shadow: 5px 5px 0 5px #171717;
        }

        /* Active 伪元素：下角 */
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

        /* Active 时头像/图标外圈 */
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
    </style>
</head>
<body>

<div class="inu-sidebar-shell">
    <ul class="inu-sidebar-nav">
        <li class="inu-sidebar-active" id="inu-sidebar-logo">
            <a href="#">
                <div class="inu-sidebar-icon">
                    <div class="inu-sidebar-imageBox">
                        <!-- <img src="your-avatar.jpg" alt="avatar"> -->
                    </div>
                </div>
                <div class="inu-sidebar-text">test</div>
            </a>
        </li>
        <li>
            <a href="#home">
                <div class="inu-sidebar-icon">
                    <i class="iconfont icon-cangku"></i>
                </div>
                <div class="inu-sidebar-text">Home</div>
            </a>
        </li>
        <li>
            <a href="#theme">
                <div class="inu-sidebar-icon">
                    <i class="iconfont icon-zhuti_tiaosepan"></i>
                </div>
                <div class="inu-sidebar-text">theme</div>
            </a>
        </li>
        <li>
            <a href="#wallet">
                <div class="inu-sidebar-icon">
                    <i class="iconfont icon-qianbao"></i>
                </div>
                <div class="inu-sidebar-text">wallet</div>
            </a>
        </li>
        <li>
            <a href="#picture">
                <div class="inu-sidebar-icon">
                    <i class="iconfont icon-tupian"></i>
                </div>
                <div class="inu-sidebar-text">picture</div>
            </a>
        </li>
        <li>
            <a href="#code">
                <div class="inu-sidebar-icon">
                    <i class="iconfont icon-erweima"></i>
                </div>
                <div class="inu-sidebar-text">QR code</div>
            </a>
        </li>
        <li>
            <a href="#authentication">
                <div class="inu-sidebar-icon">
                    <i class="iconfont icon-dunpaibaoxianrenzheng"></i>
                </div>
                <div class="inu-sidebar-text">authentication</div>
            </a>
        </li>
        <li>
            <a href="#me">
                <div class="inu-sidebar-icon">
                    <div class="inu-sidebar-imageBox">
                        <!-- <img src="your-avatar.jpg" alt="me"> -->
                    </div>
                </div>
                <div class="inu-sidebar-text">ME</div>
            </a>
        </li>
    </ul>
</div>

<script>
    const items = document.querySelectorAll('.inu-sidebar-nav li');
    items.forEach(item => {
        item.addEventListener('click', function(e) {
            // 阻止默认跳转（可选）
            // e.preventDefault();
            items.forEach(el => el.classList.remove('inu-sidebar-active'));
            this.classList.add('inu-sidebar-active');
        });
    });
</script>

</body>
</html>