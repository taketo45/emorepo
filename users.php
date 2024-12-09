<?php
//0. SESSION開始！！
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
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
$query = "SELECT * FROM tn_user_table";
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
<meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>エモレポ ユーザー管理画面</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body id="main">
<!-- Head[Start] -->
<header>
  <div class="header-left">
      <h1 class="header-logo"><img src="img/transformnavi.png" alt="タイトルロゴ"></h1>
  </div>
    <?php echo $_SESSION["name"]; ?>さん
    <?php include("menu.php"); ?>
</header>
<!-- Head[End] -->


<!-- Main[Start] -->
<main class="mainerea">
<div>
    <div class="container jumbotron">

      <table>
      <?php foreach($values as $v){ ?>
        <tr>
          <td><?=$v["id"]?></td>
          <td><a href="user_detail.php?id=<?=$v["id"]?>"><?=$v["name"]?></a></td>
          <td><?=$v["lid"]?></td>
          <td><?=$v["lid"]?></td>
          <?php if($v["mgmt_flg"] == 1) { ?>
            <td>管理職</td>
            <?php } else { ?>
            <td>一般社員</td>
          <?php } ?>
          <?php if($v["life_flg"] == 1) { ?>
            <td>利用停止</td>
            <?php } else { ?>
            <td>有効</td>
          <?php } ?>
          <td><a href="user_delete.php?id=<?=$v["id"]?>">[削除]</a></td>
        </tr>
      <?php } ?>
      </table>

  </div>
</div>
</main>
<!-- Main[End] -->


<script>
  const a = '<?php echo $json; ?>';
  console.log(JSON.parse(a));
</script>
</body>
</html>
