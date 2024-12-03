<?php
session_start();

include "lib/funcs.php";
sschk();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>エモレポ ユーザー登録</title>
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
<form method="post" action="user_insert.php" id="user_input_form">
  <div id="user_input_form_div">

  <!-- <div class="jumbotron">
   <fieldset> -->
    <div><h3>ユーザー登録</h3></div>
    <div>
      <label for="user_input_name">名前：</label>
      <input type="text" name="name" id="user_input_name"></label>
    </div>
    <div>
      <label for="user_input_lid">Login ID: </label>
      <input type="text" name="lid" id="user_input_lid">
    </div>
    <div>
      <label for="">Login PW: </label>
      <input type="text" name="lpw" id="user_input_lpw">
    </div>
    <label for="mgmt_flg_div">職階: </label>
    <div id="mgmt_flg_div">
      <div>
        <label for="user_input_mgmt_0" class="flg_elem">一般社員</label>
        <input type="radio" name="mgmt_flg" value="0" id="user_input_mgmt_0" class="flg_elem">
      </div>
      <div>
        <label for="user_input_mgmt_1" class="flg_elem">管理職</label>
        <input type="radio" name="mgmt_flg" value="1" id="user_input_mgmt_1" class="flg_elem">
      </div>
    </div>
     <!-- <label>退会FLG：<input type="text" name="life_flg"></label><br> -->
     <input type="submit" value="送信" class="btn" id="user_input_btn">
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
