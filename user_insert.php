<?php
//$_SESSION使うよ！
session_start();
include "lib/funcs.php";
sschk();

//※htdocsと同じ階層に「includes」を作成してfuncs.phpを入れましょう！
//include "../../includes/funcs.php";

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');



//1. POSTデータ取得
$name      = filter_input( INPUT_POST, "name" );
$lid       = filter_input( INPUT_POST, "lid" );
$lpw       = filter_input( INPUT_POST, "lpw" );
$mgmt_flg = filter_input( INPUT_POST, "mgmt_flg" );
$lpw       = password_hash($lpw, PASSWORD_DEFAULT);   //パスワードハッシュ化


$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

$query = "INSERT INTO tn_user_table(name,lid,lpw,mgmt_flg,life_flg)VALUES(:name,:lid,:lpw,:mgmt_flg,0)";
$param = [
  ':name' => $name,
  ':lid' => $lid,
  ':lpw' => $lpw,
  ':mgmt_flg' => $mgmt_flg
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