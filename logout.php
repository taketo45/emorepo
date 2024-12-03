<?php
//必ずsession_startは最初に記述
session_start();

//SESSIONを初期化（空っぽにする）
$_SESSION = array();

//Cookieに保存してある"SessionIDの保存期間を過去にして破棄
if (isset($_COOKIE[session_name()])) { //session_name()は、セッションID名を返す関数
    setcookie(session_name(), '', time()-42000, '/');
}

//サーバ側での、セッションIDの破棄
session_destroy();

//処理後、index.phpへリダイレクト
header("Location: index.html");
exit();

?>
