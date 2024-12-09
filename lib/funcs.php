<?php

define('IS_DEBUG', true);
define('DEBUG_MODE_AI', true); // AIのデータの詳細表示  
define('DEBUG_MODE_DB', false); // userInformation.phpのデータベース接続データの詳細表示  
define('DEBUG_MODE_ANALYZE', false);  // reportdetail.phpの表情分析・テキスト分析データの詳細表示
//XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str){
    return htmlspecialchars($str, ENT_QUOTES);
}

//SQLエラー
function sql_error($stmt){
    //execute（SQL実行時にエラーがある場合）
    $error = $stmt->errorInfo();
    exit("SQLError:".$error[2]);
}

//リダイレクト
function redirect($file_name){
  header("Location: ".$file_name);
  exit();
}

//SessionCheck
function sschk(){
  //LOGINチェック → funcs.phpへ関数化しましょう！
  if(!isset($_SESSION["chk_ssid"]) || $_SESSION["chk_ssid"]!=session_id()){
    exit("Login Error");
  }else{
    session_regenerate_id(true); //セッションハイジャック対策→違うセッションIDに更新してくれる。
    //trueを付け忘れるとセッションファイルがどんんどん新規作成されてしまうので、セキュリティ上よくない。基本trueを入れる。
    $_SESSION["chk_ssid"] = session_id();
  }
}
