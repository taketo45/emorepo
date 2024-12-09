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
// error_log("speechtext: ".$speechtext)

$date = new DateTime('now');
$ymd = $date->format("Y-m-d");
$name = $_SESSION["name"];

// 日付のフォーマット処理
$formattedDate = date('Y年m月d日', strtotime($ymd));

$geminiprompt = <<<EOD
# ビジネス日報作成支援AI
あなたは経験豊富なビジネス文書作成の専門家です。社員の口頭報告をもとに、適切で分かりやすいビジネス日報を作成することが求められています。同時に、報告内容に不足がある場合は適切な指示を行い、再提出を促すことも求められます。

## 入力情報
以下の音声認識テキストをもとに<report>の中に日報を作成してください：
{{{$speechtext}}}}

## 処理手順
1. 入力テキストの内容確認
   - 本日の業務内容の有無
   - 課題・懸念事項の有無
   - 明日の予定の有無
   
2. 必須情報の確認
   - 上記3要素のいずれかが欠けている場合、以下のメッセージを返す：
   必要な報告内容が不足しています。以下の要素を含めて、再度報告をお願いいたします：
   [ ] 本日の業務内容
   [ ] 課題・懸念事項
   [ ] 明日の予定
   ※不足している項目にチェックを入れて表示

3. テキスト処理ルール
   - 入力された音声認識テキストのみを情報源とし、推測による情報追加は行わない
   - 明らかな音声認識エラーは文脈に基づいて適切に修正する
   - ビジネス上不適切な用語は適切な代替用語に置換する
   - 例：
     - 「ヤバい」→「危機的」
     - 「超」→「非常に」
     - 「めっちゃ」→「大変」
     - 「キレる」→「強い不満を示す」

4. その他所感欄の処理
   - 必須報告事項以外の内容は「その他」欄に記載する
   - 堅苦しい表現に変換せず、原文の表現を活かして記載する
   - この内容にまとめられる情報は、必須情報ではないため、2. 必須情報の確認の対象外とする

## 出力形式 
markdown
<report>
# 業務日報
日付：{$formattedDate}
報告者：{$name}

<summary>
{{本日の業務内容を中心に、200文字程度で要約}}
</summary>

## 1. 本日の業務内容
{{報告された業務内容を簡潔に箇条書きで記載}}

## 2. 課題・懸念事項
{{報告された課題や問題点を箇条書きで記載}}

## 3. 明日の予定
{{報告された翌日の予定を箇条書きで記載}}

## 4. その他
{{必須報告事項以外の内容を箇条書きにして記載。堅苦しい表現に変換せず原文の表現を活かして記載する。}}

## 参考. 音声認識原文
{{入力情報は省略せずにそのまま記載する}}
{$speechtext}

</report>

## 返答前の最終確認
1. 本日の業務内容の有無
2. 課題・懸念事項の有無
3. 明日の予定の有無

<report>の内容に3要素が含まれていることを確認し、3要素のいずれかが欠けている、または「なし」場合、以下のメッセージを返す：
   必要な報告内容が不足しています。以下の要素を含めて、再度報告をお願いいたします：
   [ ] 本日の業務内容
   [ ] 課題・懸念事項
   [ ] 明日の予定
   ※不足している項目にチェックを入れて表示

EOD;

// $geminiinputtext = $speechtext.$geminicotroltext;
error_log("geminiprompt: " . $geminiprompt);

if(!IS_DEBUG || (IS_DEBUG && DEBUG_MODE_AI)){
    // APIリクエストの実行
   $gemini = new GeminiAPIService(GEMINI_API_KEY, IS_DEBUG);
} else {
    $gemini = new GeminiAPIService(GEMINI_API_KEY, IS_DEBUG);
}




$response = $gemini->generateResponse($geminiprompt);
header('Content-Type: application/json');
echo json_encode($response); 


?>


