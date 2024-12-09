<?php
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/ImageFileHandler.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// デバッグ情報の出力を追加
if(IS_DEBUG) {
    error_log("Received image data");
    error_log("POST data: " . print_r($_POST, true));
}

// レスポンスヘッダーの設定を追加
header('Content-Type: application/json');

// POSTデータのチェック
$imageData = $_POST["imageData"] ?? "";
if(empty($imageData)) {
    http_response_code(400);
    echo json_encode(['error' => 'Image data is empty']);
    exit;
}

try {
    $imageHandler = new ImageFileHandler(IS_DEBUG);
    $userId = $_SESSION["lid"];
    
    // 画像を保存し、URLを取得
    $imageUrl = $imageHandler->saveImage($imageData, $userId);
    
    if (!$imageUrl) {
        throw new Exception('画像の保存に失敗しました');
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'imageUrl' => $imageUrl
    ]);
} catch (Exception $e) {
    error_log("Image Upload Error: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 