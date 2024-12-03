<?php
//0. SESSION開始！！
session_start();
include("funcs.php");
sschk();

require_once('../../appconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

//LOGINチェック → funcs.phpへ関数化しましょう！
//if(!isset($_SESSION["chk_ssid"]) || $_SESSION["chk_ssid"]!=session_id()){
//    exit("Login Error");
//}else{
//    session_regenerate_id(true);
//    $_SESSION["chk_ssid"] = session_id();
//}

//1.  DB接続します
$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);
// include("funcs.php");
// $pdo = db_conn();
$query = "SELECT * FROM gs_an_table";
// $param = [':lid' => $lid];

try{
  $values = $db->select($query);
  // var_dump($values);
  if(!$values) {
    throw "データベースが空です。";
  }
}catch (Exception $e){
  error_log("SQL Error:" . $e->getMessage());
}

//２．データ登録SQL作成
// $pdo = db_conn();
// $sql = "SELECT * FROM gs_an_table";
// $stmt = $pdo->prepare($sql);
// $status = $stmt->execute();

//３．データ表示
// $values = "";

//全データ取得
// $values =  $stmt->fetchAll(PDO::FETCH_ASSOC); //PDO::FETCH_ASSOC[カラム名のみで取得できるモード]
$json = json_encode($values,JSON_UNESCAPED_UNICODE);

?>


<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>フリーアンケート表示</title>
<link rel="stylesheet" href="css/range.css">
<link href="css/bootstrap.min.css" rel="stylesheet">
<style>div{padding: 10px;font-size:16px;}</style>
</head>
<body id="main">
<!-- Head[Start] -->
<header>
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
      <?=$_SESSION["name"]?>さん、こんにちは。
      <a class="navbar-brand" href="index.php">データ登録</a>
      <?php if($_SESSION["kanri_flg"]=="1"){ ?>
      <a class="navbar-brand" href="user.php">ユーザー登録</a>
      <?php } ?>
      <a class="navbar-brand" href="logout.php">ログアウト</a>
      </div>
    </div>
  </nav>
</header>
<!-- Head[End] -->


<!-- Main[Start] -->
<div>
    <div class="container jumbotron">

      <table>
      <?php foreach($values as $v){ ?>
        <tr>
          <td><?=$v["id"]?></td>
          <td><a href="detail.php?id=<?=$v["id"]?>"><?=$v["name"]?></a></td>
          <td><a href="delete.php?id=<?=$v["id"]?>">[削除]</a></td>
        </tr>
      <?php } ?>
      </table>

  </div>
</div>
<!-- Main[End] -->


<script>
  const a = '<?php echo $json; ?>';
  console.log(JSON.parse(a));
</script>
</body>
</html>
