<?php
session_start();
$id = $_GET["id"]; //?id~**を受け取る

include("funcs.php");
require_once('../../appconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');
sschk();

$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

$query = "SELECT * FROM gs_an_table WHERE id=:id";
$param = [':id' => $id];

try{
  $values = $db->select($query,$param);
  // var_dump($values);
  if(!$values) {
    throw "データベースが空です。";
  } else {
    $row = $values[0];
  }
}catch (Exception $e){
  error_log("SQL Error:" . $e->getMessage());
}


?>



<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>データ更新</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <style>div{padding: 10px;font-size:16px;}</style>
</head>
<body>

<!-- Head[Start] -->
<header>
  <nav class="navbar navbar-default">
    <div class="container-fluid">
    <div class="navbar-header"><a class="navbar-brand" href="select.php">データ一覧</a></div>
    </div>
  </nav>
</header>
<!-- Head[End] -->


<!-- Main[Start] -->
<form method="POST" action="update.php">
  <div class="jumbotron">
   <fieldset>
    <legend>[編集]</legend>
     <label>名前：<input type="text" name="name" value="<?=$row["name"]?>"></label><br>
     <label>Email：<input type="text" name="email" value="<?=$row["email"]?>"></label><br>
     <label>年齢：<input type="text" name="age" value="<?=$row["age"]?>"></label><br>
     <label><textArea name="naiyou" rows="4" cols="40"><?=$row["naiyou"]?></textArea></label><br>
     <input type="submit" value="送信">
     <input type="hidden" name="id" value="<?=$id?>">
    </fieldset>
  </div>
</form>
<!-- Main[End] -->


</body>
</html>
