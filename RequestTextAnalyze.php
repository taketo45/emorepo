<?php
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/TextEmotionAnalyzer.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

//エラー表示

error_reporting(E_ALL);
ini_set('display_errors', 0); // ブラウザへの表示を無効化
ini_set('log_errors', 1);  

$textanalyzerequest = $_POST["textanalyzerequest"];  // ★★ここが空になっている。。。
if($textanalyzerequest == "") {
    http_response_code(400);
    echo json_encode(['error' => 'Text is empty']);
    exit;
}

// DB接続
$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

// 感情辞書の取得
$sql = "SELECT emotion, word, color FROM tn_emotional_words ORDER BY emotion, word";
try {
    $emotionalWords = $db->select($sql);
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load emotional dictionary']);
    exit;
}
if(IS_DEBUG) {
    error_log("emotionalWords: " . json_encode($emotionalWords));
}
// error_log("emotionalWords: " . $emotionalWords);

// $config = [
//    'apiKey' => GOOGLE_CLOUD_API_KEY,
//    'apiEndpoint' => GOOGLE_CLOUD_ENDPOINT,
// ];

if(IS_DEBUG) {
    error_log("textanalyzerequest: " . $textanalyzerequest);
}

// TextEmotionAnalyzerクラスのインスタンス生成
$textanalyzer = new TextEmotionAnalyzer(GOOGLE_CLOUD_CONFIG, $emotionalWords, IS_DEBUG);

try {
   // APIリクエストの実行
    $textanalyzeresponse = $textanalyzer->analyzeText($textanalyzerequest);

    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'data' => $textanalyzeresponse
   ]);
} catch (Exception $e) {
      error_log("TextAnalysis Error: " . $e->getMessage());
      header('Content-Type: application/json');
      http_response_code(500);
      echo json_encode([
         'success' => false,
         'error' => $e->getMessage()
      ]);
}