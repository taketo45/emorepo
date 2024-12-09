<?php
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);


$userQuery = "SELECT * FROM tn_report_table WHERE user_id = :user_id ORDER BY created_time DESC";
$userParam = [':user_id' => $_SESSION['uid']];
$reports = $db->select($userQuery, $userParam);


if (!$reports || empty($reports)) {
    error_log("Report not found for user_id: " . $_SESSION['uid']);
    $reports = [];
} 

if(IS_DEBUG) {
    error_log("Found " . count($reports) . " reports for user " . $_SESSION['uid']);
}
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
                    <?=$_SESSION["name"]?>さん
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
                    <?php if (count($reports) > 0): ?>
                        <?php foreach ($reports as $report): ?>
                            <a href="reportdetail.php?id=<?=h($report['report_id'])?>" class="report-entry">
                                <div class="report-image">
                                    <?php if ($report['image_url']): ?>
                                        <img src="<?=h($report['image_url'])?>" alt="レポート画像">
                                    <?php else: ?>
                                        <img src="img/default-report.jpg" alt="デフォルト画像">
                                    <?php endif; ?>
                                </div>
                                <div class="report-info">
                                    <div class="report-meta">
                                        <span class="report-date">
                                            <?=date('Y/m/d H:i', strtotime($report['created_time']))?>
                                        </span>
                                    </div>
                                    <div class="report-summary">
                                        <?php 
                                            $summary = h($report['report_summary']);
                                            $summary = preg_replace('/\\\\n/', "\n", $summary);
                                            $summary = preg_replace('/\\\\/', '', $summary);
                                            echo nl2br($summary);
                                        ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-reports">レポートがありません</p>
                    <?php endif; ?>
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

            // メニュー外クリックでも閉じる
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.menu-button, .nav-links').length) {
                    $navLinks.removeClass('show');
                    $overlay.removeClass('show');
                    $body.removeClass('menu-open');
                }
            });
        });
    </script>
</body>
</html>