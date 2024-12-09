<?php
session_start();
require_once("lib/funcs.php");
sschk();
require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');
require_once('lib/EmotionalDictionaryGenerator.php');

try {
    $dbinfo = new DatabaseInfo();
    $db = new DatabaseController($dbinfo, IS_DEBUG);
    $generator = new EmotionalDictionaryGenerator($db, IS_DEBUG);
    
    $result = $generator->generate();
    
    echo json_encode([
        'success' => true,
        'message' => '感情辞書ファイルの生成が完了しました'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 