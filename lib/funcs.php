<?php

//XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str){
    if(!$str||$str==""||$str=="undefined...") return "報告事項なし";  // 空の場合は空文字列を返す 
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
