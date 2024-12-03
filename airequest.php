<?php

session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/GeminiAPIService.php');

require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

//エラー表示
ini_set("display_errors", 1);


$speechtext = $_POST["speechtext"];

$date = new DateTime('now');
$ymd = $date->format("Y-m-d");
$name = $_SESSION["name"];

$geminicotroltext = `
上記は、社員の日次報告の基礎情報です。日次報告の基礎情報に下記の要素が含まれていない場合は、含まれていない要素を追加で話すように促して、処理を停止してください。
必要な要素
 ・本日の業務
 ・明日の予定
  ・現在の注力テーマまたは課題事項
日次報告の基礎情報の中に、必要な要素がすべて含まれていたら内容を、マークダウン記法を用いてビジネスの日報にしてください。日付は、{$ymd}、報告者は{$name}として記載してください。
ビジネス日報の最後に、もともとの日次報告の基礎情報をオリジナルの報告内容として付記してください。
あなたは役所の人間です。ユーザーが話していない要素を勝手に足したり、用語から連想されるような内容を付け足さないでください。記載されている要素を報告書の形式にまとめ直すだけにしてください。`;

$geminiinputtext = $speechtext.$geminicotroltext;
error_log("geminiinputtext: " . $geminiinputtext);
// APIリクエストの実行
$gemini = new GeminiAPIService(GEMINI_API_KEY, IS_DEBUG);


$response = $gemini->generateResponse($geminiinputtext);
header('Content-Type: application/json');
echo json_encode($response); 


?>


