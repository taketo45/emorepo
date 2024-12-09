<?php
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

function extractSummary($report) {
    if (IS_DEBUG) {
        error_log("Extracting summary from report: " . substr($report, 0, 200));
    }

    // 改行を含むマルチバイト対応の正規表現
    if (mb_ereg('<summary>(.*?)</summary>', $report, $matches)) {
        if (IS_DEBUG) {
            error_log("Summary found in tags: " . $matches[1]);
        }
        return trim($matches[1]);
    }
    
    if (IS_DEBUG) {
        error_log("No summary tags found, trying section match");
    }

    // セクション抽出の試行
    if (mb_ereg('## 1\. 本日業務内容\n(.*?)(?=##)', $report, $matches)) {
        $content = trim($matches[1]);
        if (IS_DEBUG) {
            error_log("Section content found: " . substr($content, 0, 100));
        }
        return mb_substr($content, 0, 200) . '...';
    }
    
    if (IS_DEBUG) {
        error_log("No section found, using first 200 chars");
    }

    // デフォルトの処理
    return mb_substr($report, 0, 200) . '...';
}

try {
    // POSTデータの取得方法を変更
    $reportData = $_POST;

    if(IS_DEBUG) {
        error_log("Processed report data: " . print_r($reportData, true));
    }

    // DB接続
    $dbinfo = new DatabaseInfo();
    $db = new DatabaseController($dbinfo, IS_DEBUG);

    // データの検証
    if (empty($reportData['speechText'])) {
        throw new Exception('レポート本文が空です');
    }

    // 画像URLの検証
    if (empty($reportData['imageUrl'])) {
        throw new Exception('画像データが見つかりません');
    }

    // 画像URLが正しい形式かチェック
    if (!preg_match('#^uploads/faces/\d{8}_\d{6}_.*?_[a-f0-9]+\.jpg$#', $reportData['imageUrl'])) {
        throw new Exception('不正な画像URLです');
    }

    // レポートデータの保存
    $sql = "INSERT INTO tn_report_table (
        user_id,
        speech_text,
        gemini_report,
        report_summary,
        image_url,
        image_emotion,
        ai_text_emotion,
        document_score,
        terminology_score,
        emotion_score,
        created_time
    ) VALUES (
        :user_id,
        :speech_text,
        :gemini_report,
        :report_summary,
        :image_url,
        :image_emotion,
        :ai_text_emotion,
        :document_score,
        :terminology_score,
        :emotion_score,
        :created_time
    )";

    $currentTime = date('Y-m-d H:i:s');  // MySQLのdatetime形式に合わせる

    $params = [
        ':user_id' => $_SESSION['uid'],
        ':speech_text' => $reportData['speechText'],
        ':gemini_report' => $reportData['dailyReport'],
        ':report_summary' => extractSummary($reportData['dailyReport']),
        ':image_url' => $reportData['imageUrl'],
        ':image_emotion' => $reportData['imageEmotionResult'],
        ':ai_text_emotion' => $reportData['textEmotionResult'],
        ':document_score' => $reportData['documentScore'],
        ':terminology_score' => $reportData['terminologyScore'],
        ':emotion_score' => $reportData['emotionScore'],
        ':created_time' => $currentTime
    ];

    // SQLの実行
    
        $excuteCount = $db->modify($sql, $params);

        // 最新のレポートIDを取得
        $lastIdSql = "SELECT LAST_INSERT_ID() as last_id";
        $lastIdResult = $db->select($lastIdSql);
        $lastId = $lastIdResult[0]['last_id'];

    // 成功レスポンス
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'reportId' => $lastId
    ]);

} catch (Exception $e) {
    error_log("Save Report Error: " . $e->getMessage());
    
    // エラーレスポンス
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
