<?php
session_start();
include "lib/funcs.php";
sschk();

//1. POSTデータ取得
$name        = $_POST["name"];
$lid         = $_POST["lid"];
$lpw         = $_POST["lpw"];
$mgmt_flg    = $_POST["mgmt_flg"];
$life_flg     = $_POST["life_flg"];

//2. DB接続します
require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');


$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

$query = "UPDATE tn_user_table SET name=:name,lid=:lid,lpw=:lpw,mgmt_flg=:mgmt_flg,life_flg=:life_flg  WHERE id=:id";
$param = [
  ':name' => $name,
  ':lid' => $lid,
  ':lpw' => $lpw,
  ':mgmt_flg' => $mgmt_flg,
  ':life_flg' => $life_flg
];

try{
  $status = $db->modify($query,$param);
  // var_dump($values);
  if(!$status) {
    throw "データベースが空です。";
  } else {
    redirect("users.php");
  }
}catch (Exception $e){
  error_log("SQL Error:" . $e->getMessage());
}


?>
