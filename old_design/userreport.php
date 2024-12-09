<?php
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

// まずユーザーIDを取得
$userQuery = "SELECT id FROM tn_user_table WHERE lid = :lid LIMIT 1";
$userParam = [':lid' => $_SESSION['lid']];
$userData = $db->select($userQuery, $userParam);

if (!$userData || empty($userData)) {
    error_log("User not found for lid: " . $_SESSION['lid']);
    $reports = [];
} else {
    $userId = $userData[0]['id'];
    // レポートを取得
    $sql = "SELECT 
        *
    FROM tn_report_table 
    WHERE user_id = :user_id 
    ORDER BY created_time DESC";
    $reports = $db->select($sql, ['user_id' => $userId]);
}

if(IS_DEBUG) {
    error_log("Found " . count($reports) . " reports for user " . $_SESSION['lid']);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レポート履歴</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="header-left">
            <h1 class="header-logo"><img src="img/transformnavi.png" alt="タイトルロゴ"></h1>
        </div>
        <div class="header-center">
            <h2>レポート履歴</h2>
        </div>
        <div class="header-right">
            <div id="userInfo">
                <?=$_SESSION["name"]?>さん
                <a class="btn" href="userInformation.php">レポート作成</a>
                <button class="btn" onclick="location.href='logout.php'">ログアウト</button>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="reports-list">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>日時</th>
                        <th>概要</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reports as $report): ?>
                    <tr>
                        <td class="date-column">
                            <?=h(date('Y/m/d H:i', strtotime($report['created_time'])))?>
                        </td>
                        <td class="summary-column">
                            <div class="summary-content" data-summary="<?=h($report['report_summary'] ?? '')?>">
                            </div>
                        </td>
                        <td class="action-column">
                            <button class="btn btn-primary view-detail" 
                                    data-report-id="<?=h($report['report_id'])?>">
                                詳細を見る
                            </button>
                        </td>
                    </tr>
                    <?php if (!empty($report['image_url'])): ?>
                        <tr>
                            <td colspan="3">
                                <img src="<?= h($report['image_url']) ?>" 
                                     alt="表情分析画像" 
                                     class="emotion-image">
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('.view-detail').on('click', function() {
            const reportId = $(this).data('report-id');
            window.location.href = `reportdetail.php?id=${reportId}`;
        });

        $('.summary-content').each(function() {
            const summaryData = $(this).data('summary');
            if (summaryData) {
                const escapedSummary = summaryData
                    .replace(/\\n/g, '\n')  // エスケープされた改行を実際の改行に変換
                    .replace(/\\/g, '');     // 残りのバックスラッシュを削除
                $(this).html(escapedSummary.replace(/\n/g, '<br>'));
            }
        });
    });
    </script>

    <style>
    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .date-column {
        width: 150px;
    }
    .summary-column {
        min-width: 400px;
    }
    .action-column {
        width: 100px;
        text-align: center;
    }
    </style>
</body>
</html>