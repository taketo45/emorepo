<?php

session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/DifyAPIService.php');

// POSTメソッドのみを許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method Not Allowed'
    ]);
    exit;
}

// エラー表示設定
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$inputText = $_POST["inputText"] ?? '';

if (empty($inputText)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => '入力テキストが空です'
    ]);
    exit;
}

try {
    $dify = new DifyAPIService(DIFY_API_KEY, DIFY_API_ENDPOINT, IS_DEBUG);
    $response = $dify->generateResponse($inputText);
    
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    error_log('Dify Request Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'サーバーエラーが発生しました'
    ]);
}
?> 