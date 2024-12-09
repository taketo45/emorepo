<?php
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/ImageEmotionAnalyzer.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// POSTデータのチェック
$imageUrl = $_POST["imageUrl"] ?? "";
if(empty($imageUrl)) {
    http_response_code(400);
    echo json_encode(['error' => 'Image URL is empty']);
    exit;
}

if(IS_DEBUG) {
    error_log("Processing image URL: " . $imageUrl);
}

// ImageEmotionAnalyzerクラスのインスタンス生成
$imageAnalyzer = new ImageEmotionAnalyzer(GOOGLE_CLOUD_CONFIG, IS_DEBUG);

try {
    // APIリクエストの実行
    $analysisResponse = $imageAnalyzer->analyzeImageUrl($imageUrl);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $analysisResponse,
        'imageUrl' => $imageUrl
    ]);
} catch (Exception $e) {
    error_log("Image Analysis Error: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 