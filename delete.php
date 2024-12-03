<?php
session_start();
include("lib/funcs.php");
sschk();

//1. POSTデータ取得
$id = $_GET["id"];

//2. DB接続します

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

// $pdo = db_conn();

// //３．データ登録SQL作成
// $stmt = $pdo->prepare("DELETE FROM gs_an_table WHERE id=:id");
// $stmt->bindValue(':id', $id, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)


// $status = $stmt->execute(); //実行

// //４．データ登録処理後
// if($status==false){
//   sql_error($stmt);
// }else{
//   redirect("select.php");
// }


$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);
// include("funcs.php");
// $pdo = db_conn();
$query = "DELETE FROM tn_user_table WHERE id=:id";
// echo($query);
$param = [':id' => $id];

try{
  $status = $db->modify($query,$param);
  // var_dump($values);
  if(!$status) {
    throw "データベースが空です。";
  }else{
    redirect("users.php");
  }
}catch (Exception $e){
  error_log("SQL Error:" . $e->getMessage());
}

?>
