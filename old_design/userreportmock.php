<?php
session_start();
// include "lib/funcs.php";
// require_once('../../emorepoconfig/config.php');
// sschk();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>えもれぽ - レポート履歴</title>
    <link rel="stylesheet" href="css/workwell.css">
    <link rel="stylesheet" href="css/workwell-report.css">
    <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
</head>
<body>
    <div class="layout-container">
        <!-- Header Navigation -->
        <header class="dashboard-header">
            <div class="header-left">
                <img src="img/transformnavi.png" alt="えもれぽ" class="app-logo">
                <h1 class="brand-name nico-moji">えもれぽ</h1>
            </div>

            <div class="nav-container">
                <button class="menu-button" aria-label="メニュー">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <nav class="nav-links">
                    <a href="#" class="nav-link">Dashboard</a>
                    <a href="#" class="nav-link">My Team</a>
                    <a href="#" class="nav-link">Resources</a>
                    <a href="userreport.php" class="nav-link">レポート管理</a>
                    <?php if($_SESSION["mgmt_flg"]=="1"){ ?>
                    <a href="users.php" class="nav-link">ユーザー管理</a>
                    <?php } ?>
                    <a href="logout.php" id="logoutButton" class="nav-link">ログアウト</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="report-content">
            <div class="report-container">
                <h2 class="report-title">レポート履歴</h2>
                
                <div class="report-list">
                    <!-- レポートエントリのサンプル -->
                    <a href="reportdetail.php?id=1" class="report-entry">
                        <div class="report-image">
                            <img src="path/to/image.jpg" alt="レポート画像">
                        </div>
                        <div class="report-info">
                            <div class="report-meta">
                                <span class="report-date">2024/03/20 15:30</span>
                            </div>
                            <div class="report-summary">
                                今日のミーティングでは、新しいプロジェクトについて話し合いました。チームメンバーの意見を積極的に取り入れ、建設的な議論ができました...
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script>
        $(document).ready(function() {
        const $menuButton = $('.menu-button');
        const $navLinks = $('.nav-links');
        const $body = $('body');

        // オーバーレイ要素を動的に追加
        $('body').append('<div class="menu-overlay"></div>');
        const $overlay = $('.menu-overlay');

        $menuButton.on('click', function() {
            $navLinks.toggleClass('show');
            $overlay.toggleClass('show');
            $body.toggleClass('menu-open');
        });

        // オーバーレイクリックでメニューを閉じる
        $overlay.on('click', function() {
            $navLinks.removeClass('show');
            $overlay.removeClass('show');
            $body.removeClass('menu-open');
        });
        });
    </script>
</body>
</html>