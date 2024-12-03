<?php
session_start();
include("lib/funcs.php");
sschk();

$id = $_GET["id"]; //?id~**を受け取る

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');
sschk();

$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

$query = "SELECT * FROM tn_user_table WHERE id=:id";
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
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>エモレポ ユーザー更新</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/main.css">
</head>
<body>

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
<form method="POST" action="user_update.php">
<div id="user_input_form_div">

<!-- <div class="jumbotron">
 <fieldset> -->
  <div><h3>ユーザー更新</h3></div>
  <div>
    <label for="user_input_name">名前：</label>
    <input type="text" name="name" id="user_input_name" value="<?=$row["name"]?>">
  </div>
  <div>
    <label for="user_input_lid">Login ID: </label>
    <input type="text" name="lid" id="user_input_lid" value="<?=$row["lid"]?>">
  </div>
  <div>
    <label for="">Login PW: </label>
    <input type="text" name="lpw" id="user_input_lpw" value="">
  </div>
  <label for="mgmt_flg_div">職階Flg: </label>
  <div id="mgmt_flg_div">
    <div>
      <?php if($row["mgmt_flg"] === 0) {
        $norm = 'checked="checked"';
        $manager = '';
      } else {
        $norm = '';
        $manager = 'checked="checked"';
      } ?>
        <label for="user_input_mgmt_0" class="flg_elem">一般社員</label>
        <input type="radio" name="mgmt_flg" id="user_input_mgmt_0" class="flg_elem" value="0" <?=$norm?>>
    </div>
    <div>
        <label for="user_input_mgmt_1" class="flg_elem">管理職</label>
        <input type="radio" name="mgmt_flg" value="1" id="user_input_mgmt_1" class="flg_elem"  <?=$manager?>>
    </div>
  </div>
  <label for="life_flg_div">離職Flg: </label>
  <div id="life_flg_div">
    <div>
      <?php if($row["life_flg"] === 0) {
        $active = 'checked="checked"';
        $nonactive = '';
      } else {
        $active = '';
        $nonactive = 'checked="checked"';
      } ?>
        <label for="user_input_life_0" class="flg_elem">在職</label>
        <input type="radio" name="life_flg" id="user_input_life_0" class="flg_elem" value="0" <?=$active?>>
    </div>
    <div>
        <label for="user_input_life_1" class="flg_elem">離職</label>
        <input type="radio" name="life_flg" value="1" id="user_input_life_1" class="flg_elem"  <?=$nonactive?>>
    </div>
  </div>
   <!-- <label>退会FLG：<input type="text" name="life_flg"></label><br> -->
   <input type="submit" value="更新" class="btn" id="user_input_btn">
  <!-- </fieldset>
</div> -->
</div>
</form>
</main>
<!-- Main[End] -->

<footer>
    <div class="footer">
        <small>エモレポ</small>
    </div>
</footer>

</body>
</html>
