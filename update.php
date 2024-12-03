<?php
session_start();
include "funcs.php";
sschk();

//1. POSTデータ取得
$name   = $_POST["name"];
$email  = $_POST["email"];
$naiyou = $_POST["naiyou"];
$age    = $_POST["age"];
$id     = $_POST["id"];

//2. DB接続します
require_once('../../appconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');


$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

$query = "UPDATE gs_an_table SET name=:name,email=:email,age=:age,naiyou=:naiyou WHERE id=:id";
$param = [
  ':name' => $name,
  ':email' => $email,
  ':age' => $age,
  ':naiyou' => $naiyou,
  ':id' => $id,
];

try{
  $status = $db->modify($query,$param);
  // var_dump($values);
  if(!$status) {
    throw "データベースが空です。";
  } else {
    redirect("select.php");
  }
}catch (Exception $e){
  error_log("SQL Error:" . $e->getMessage());
}


?>
