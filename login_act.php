<?php
include("lib/funcs.php");
require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

//最初にSESSIONを開始！！ココ大事！！
session_start();

//POST値
$lid = $_POST["lid"]; //lid
$lpw = $_POST["lpw"]; //lpw

//1.  DB接続します
$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

$query = "SELECT * FROM tn_user_table WHERE lid=:lid";
$param = [':lid' => $lid];

try{
  $values = $db->select($query,$param);
  var_dump($values);
  if(!$values) {
    throw "データベースが空です。";
  }
}catch (Exception $e){
  error_log("SQL Error:" . $e->getMessage());
}


$value=$values[0];
$pw = password_verify($lpw, $value["lpw"]); //$lpw = password_hash($lpw, PASSWORD_DEFAULT);   //パスワードハッシュ化
if($pw){ 
  //Login成功時
  $_SESSION["chk_ssid"]  = session_id();
  $_SESSION["mgmt_flg"] = $value['mgmt_flg'];
  $_SESSION["name"]      = $value['name'];
  $_SESSION["lid"]       = $value['lid'];
  //Login成功時
  redirect("userInformation.php");

}else{
  //Login失敗時(login.phpへ)
  redirect("index.html");

}

exit();


