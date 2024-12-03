<?php

session_start();
include "lib/funcs.php";
require_once('../../emorepoconfig/config.php');
sschk();


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>エモレポ</title>

    <link rel="stylesheet" href="css/userstyle.css">
    <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="header-left">
            <h1 class="header-logo"><img src="img/transformnavi.png" alt="タイトルロゴ"></h1>
        </div>
        <div class="header-center">
            <h2>えもれぽ</h2>
        </div>
        <div class="header-right">
            <!-- ログイン状態表示エリア -->
            <div id="authContainer" class="auth-container">
                <div id="userInfo" style="display: none;">
                    <!-- <span id="userName"></span> -->
                    <?=$_SESSION["name"]?>さん、こんにちは
                    <a class="btn" href="index.php">レポート管理</a>
                    <?php if($_SESSION["mgmt_flg"]=="1"){ ?>
                    <a class="btn" href="users.php">ユーザー管理</a>
                    <?php } ?>
                    <button id="logoutButton" class="btn" onclick="location.href='logout.php'">ログアウト</button>
                </div>
                <!-- <button id="loginButton" class="btn">Googleでログイン</button> -->
        </div>
    </header>

    <div class="container">
        <h3>ムービーレポート入力画面</h3>
        <div class="preview-container">
            <video id="video" autoplay playsinline></video>
            <img id="imagePreview" style="display: none;">
        </div>
        <div class="control-panel">
            <button id="startButton">日報入力開始</button>
            <button id="stopButton" disabled>日報入力停止</button>
            <button id="clearButton" disabled>クリア</button>
            <button id="analyzebtn" disabled>日報を提出</button>
            <!-- <textarea id="testinput" hidden></textarea> -->
            <textarea id="testinput"></textarea>
            <!-- <a id="download" download="capture.png">写真のDL</a> -->
        </div>
        <div id="status">待機中...</div>
        <textarea id="transcriptArea" readonly></textarea>
        <textarea id="reportArea"></textarea>
    </div>
    <div class="container" id="emoreport">
        <h3>文書の分析</h3>
        <div id="result" class="result-section"></div>

        <h3>表情の分析</h3>
        <div id="resultsContainer" class="result-container"></div>
        
        <div class="chart-container">
            <canvas id="emotionChart"></canvas>
        </div>
        
        <div id="historyContainer" class="history-container"></div>
        <div id="canvasDisplay"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <!-- <script src="js/Authkeys.js"></script> -->
    <!-- <script src="js/webspeechService.js"></script> -->
    <script src="js/emotionalDictionary.js"></script>
    <!-- <script src="js/textEmotionAnalyzer.js"></script> -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="module" src="js/camera.js"></script>
    <!-- <script type="module" src="js/imageAnalysis.js"></script> -->
    <script type="module" src="js/displayUtils.js"></script>
    <script type="module">
        import { getAuth, signInWithPopup, GoogleAuthProvider, signOut, onAuthStateChanged } 
        from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";
        import { WebApiClient } from './js/WebAPIClient.js';
        import { CameraModule } from './js/camera.js';
        import { TextEmotionAnalyzer } from './js/TextEmotionAnalyzer.js';
        import { ImageEmotionAnalyzer } from './js/ImageEmotionAnalyzer.js';
        import { WebSpeechService } from './js/WebSpeechService.js';
        import { DisplayModule } from './js/displayUtils.js';
        // import { db } from './js/firebase.js';
        import { AuthService } from './js/authService.js';
        import {auth, provider, app, db, storage, googleAuthLaterProcess, logOut} from './js/FirebaseInit.js';
        // import { FirestoreService } from './js/FirestoreService.js';
        import { GeminiAPI} from './js/GeminiAPIClass.js';

        //デバグモードで動かす場合は、true。コメントアウトすれば通常モード
        const isDebug = <?=IS_DEBUG?>;

        // HTML要素の取得をオブジェクトにまとめる
        const controls = {
            $startButton: $('#startButton'),
            $stopButton: $('#stopButton'),
            $clearButton: $('#clearButton'),
            $status: $('#status'),
            $transcriptArea: $('#transcriptArea'),
            $reportArea: $('#reportArea'),
            $faceImage: $('#imagePreview'),
            $logout: $('#logoutButton'),
            $testinput: $('#testinput')
        };

        const analyzeEL = {
            $input: $('#transcriptArea'),
            $start: $('#analyzebtn'),
            $output: $('#result'),
            $emoreport: $('#emoreport')
        }
        const EAconfig = {
            apiKey: '<?=GOOGLE_CLOUD_API_KEY?>',
            endpoint: '<?=GOOGLE_CLOUD_ENDPOINT?>'
        };
        const IAconfig = {
            apiKey: '<?=VISION_API_KEY?>',
            endpoint: '<?=VISION_API_ENDPOINT?>'
        }

        const webSpeech = new WebSpeechService(controls, isDebug);
        const textAnalyzer = new TextEmotionAnalyzer(EAconfig, isDebug);
        const imageAnalyzer = new ImageEmotionAnalyzer(IAconfig, isDebug);
        const geminiAPI = new GeminiAPI('<?=GEMINI_API_KEY?>', isDebug);

        const storeinfo = {
            collection: "emoreportusers",
            uid: "",
            subcollection: "report",
            yeardate: ""
        }

        const reportinfo = {
            uid: null,
            datetime: null,
            reportoriginal: null,
            reportformatted: null,
            faceimage: null,
            imageanalyzed: null,
        }
        let inputText = "";
        let beforeInputText = "";
        const lid = '<?=$_SESSION["lid"]?>';

        $(document).ready(function() {
            // onAuthStateChanged(auth, async (user) => {
            //     if(!user){
            //         logOut(auth);
            //     }
            //     // console.log(user);
                
            // });  
            
            (async function(){  //即時関数 開始
                // *********↓↓Firestore関連の初期処理*********
                // updateUI(user);
                updateUI();
                // const firestore = new FirestoreService(storeinfo.collection, isDebug);
                // const users = await firestore.getDocumentIds();
                // if(!users.includes(user)) {
                //     firestore.setDocToCollection(user);
                //     storeinfo.uid = user.uid;
                //     reportinfo.uid = user.uid;
                // }
                // AuthService.setupEventListeners();
                // await AuthService.updateUI(user);

                
                
                // *********↑↑Firestore関連の初期処理*********

                // *********↓↓onAuthStateChanged()の内側にいれる*********

                
                
                //録音開始ボタン
                controls.$startButton.on('click',async function() {
                    await CameraModule.init();
                    beforeInputText = inputText;
                    webSpeech.start();
                    analyzeEL.$emoreport.hide();

                });

                //録音停止ボタン
                controls.$stopButton.on('click', async function() {
                    webSpeech.stop();

                //えもめーたー処理マージ
                const imageData = CameraModule.captureImage();
                DisplayModule.updatePreview(imageData);
                
                // ダウンロードリンクの更新
                // emometerEL.$download.attr('href', imageData);

                //Gemini 送信前処理・送信処理
                inputText = controls.$transcriptArea.val().trim(); //分析ボタンが押下されるまでグローバル変数に保持
                // if(!inputText.legth) {
                //     console.log("No Input!!");
                //     controls.$status.text('音声が入力されていません。マイクを確認ください。');
                //     return;
                // }
                
                //喋らなくてもテストできるように追加
                //#testinputのhiddenを解除してテキストをキー入力することで利用
                inputText += controls.$testinput.val();
                
                if(!inputText || inputText === "") {
                    console.log('No Audio Data!: Please talk something.');
                    return;
                }

                //TODO: 下記処理は機能していないので、要Debug
                if(inputText === beforeInputText){
                    console.log('No Audio Data added!');
                    return;
                }
                reportinfo.reportoriginal = inputText||" ";
                controls.$status.val('処理中...');

                //@@@@@@@@@@@@  PHP側のAI Web Serviceを呼び出す @@@@@@

                const domain = location.origin;
                const path = "/emorepo";
                const domainpath = domain + path;
                const AiFrontPath = '/airequest.php';

                const AipostData = {
                    speechtext: inputText,
                };


                const apiClient = new WebApiClient(domainpath, isDebug);

                apiClient.postFormData(AiFrontPath, AipostData)
                .then(response => {
                    // controls.$reportArea.html(response);
                    if (response.success) {
                        controls.$reportArea.val(response.text);
                        //ここにLocalstrageの処理を追記
                        reportinfo.reportformatted = response.text; //分析ボタンが押下されるまでグローバルオブジェクトに保持

                    } else {
                        controls.$reportArea.val('');
                        this.showError(response.error);
                    }
                })
                .catch(error => {
                    console.error('API Error:', error);
                    controls.$reportArea.val('');
                })
                .finally (() => {
                    // 分析ボタン、クリアボタンを有効化
                    analyzeEL.$start.prop('disabled', false);
                    controls.$clearButton.prop('disabled', false);
                    controls.$transcriptArea.hide();
                    
                    controls.$reportArea.attr('length', '700px').show();
                });

                //@@@@@@@@@@@@  PHP側のAI Web Service呼び出し終わり @@@@@@


                // const date = new Date();
                // const yyyymmddDate = date.toLocaleDateString("ja-JP");

                // const geminiControlText = `

                // 上記は、社員の日次報告の基礎情報です。
                // 日次報告の基礎情報に下記の要素が含まれていない場合は、含まれていない要素を追加で話すように促して、処理を停止してください。
                // 必要な要素
                // ・本日の業務
                // ・明日の予定
                // ・現在の注力テーマまたは課題事項

                // 日次報告の基礎情報の中に、必要な要素がすべて含まれていたら内容を、マークダウン記法を用いてビジネスの日報にしてください。あなたは役所の人間です。勝手に要素を足したり、用語から連想されるような内容を付け足さないでください。記載されている要素を報告書の形式にまとめ直すだけにしてください。
                // 日付は、${yyyymmddDate}、報告者は<?=$_SESSION["name"]?>として記載してください。
                // ビジネス日報の最後、もともとの記載内容をオリジナルの報告内容として最後に付記してください。
                // `;

                // inputText += geminiControlText;

                // try {
                //     const response = await geminiAPI.generateResponse(inputText);
                    
                //     if (response.success) {
                //         controls.$reportArea.val(response.text);
                //         //ここにLocalstrageの処理を追記
                //         reportinfo.reportformatted = response.text; //分析ボタンが押下されるまでグローバルオブジェクトに保持

                //     } else {
                //         controls.$reportArea.val('');
                //         this.showError(response.error);
                //     }
                // } catch (e) {
                //     console.error('UI Error:', e);
                //     this.showError('予期せぬエラーが発生しました');
                //     controls.$reportArea.val('');
                // } finally {
                //     // 分析ボタン、クリアボタンを有効化
                //     analyzeEL.$start.prop('disabled', false);
                //     controls.$clearButton.prop('disabled', false);
                //     controls.$transcriptArea.hide();
                    
                //     controls.$reportArea.attr('length', '700px').show();
                // }
                
                });

                //クリアボタン
                controls.$clearButton.on('click',async function() {
                    webSpeech.clear();

                    controls.$transcriptArea.val('');
                    controls.$reportArea.val('');
                    beforeInputText = "";
                    inputText = "";

                    //えもめーたー処理マージ
                    await CameraModule.reset();
                    DisplayModule.resetPreview();

                    // 分析ボタン、クリアボタンを無効化
                    analyzeEL.$start.prop('disabled', true);
                    controls.$clearButton.prop('disabled', true);
                    controls.$transcriptArea.prop('readonly', true);
                });


                //感情分析ボタン
                analyzeEL.$start.on('click',async ()=>{
                    analyzeEL.$emoreport.show();

                    ///////////////ここから///////////////
                    const inputtext = analyzeEL.$input.val();

                    const result = await textAnalyzer.analyzeText(inputtext);

                    // 現在日付を取得
                    reportinfo.datetime = new Date();
                    const ymd = new Date().toLocaleDateString('ja-JP').split('/').join(''); // yyyymmdd形式で取得
                    const time = new Date().toLocaleTimeString('ja-JP', {hour12: false}).split(':').join('');   // hhmmss形式で取得
                    const curDatetime = ymd + time;
                    storeinfo.yeardate = curDatetime; //必ずsetDocToSubColloction()より前に処理する。

                    // firestore.setDocToSubColloction(storeinfo, "userid", storeinfo.uid);
                    // firestore.setDocToSubColloction(storeinfo, "datetime", newdate);
                    // firestore.setDocToSubColloction(storeinfo, "reportoriginal", reportoriginal);
                    // firestore.setDocToSubColloction(storeinfo, "reportformatted", reportformatted);

                    // firestore.setDocToSubColloction(storeinfo, "textanalyzed", result);
                    reportinfo.textanalyzed = result;
                    //ここで文書感情分析結果を、firestore格納処理

                    const html = textAnalyzer.generateResultHtml(result);
                    analyzeEL.$output.html(html);
                    ///////////////ここまでControlerClass///////////////

                    //えもめーたー処理のマージ
                    const imageData = controls.$faceImage.attr('src');
                    //ここにCloud storageの処理
                    //ここにstores格納処理 → reportinfoへ投入しAnalyzeボタン押下を待つ
                    reportinfo.faceimage = "storage URL";

                    try {
                        const response = await imageAnalyzer.analyzeFaceAndEmotions(imageData);
                        console.log(response);
                        reportinfo.imageanalyzed = response;
                        // console.log(storeinfo);
                        console.log(reportinfo);
                        // firestore.setDocToSubColloction(storeinfo, "imageanalyzed", response);
                        DisplayModule.displayResults(response);
                    } catch (error) {
                        console.error('Failed to analyze image:', error);
                        alert('画像の分析に失敗しました: ' + error.message);
                    }
                    // const success = await firestore.setDocToSubCollectionAll(reportinfo);
                    // if (success) {
                    //     console.log('Document saved successfully');
                    // }
                    

                    // 分析ボタン、クリアボタンを無効化
                    analyzeEL.$start.prop('disabled', true);
                    controls.$clearButton.prop('disabled', true);
                    controls.$transcriptArea.prop('readonly', true);
                });

                controls.$logout.on('click', () =>  {
                    if(!user) alert('ログインしていません');
                    logOut(auth);
                });
                // *********↑↑onAuthStateChanged()の内側にいれる*********
            })();    //即時関数 終了



        });  //$(document)の終了

        // function updateUI(user) {
        function updateUI() {
            // if (user) {
                // TODO: PHP認証への変換
                // $('#userName').text(`${user.displayName}さん`);
                $('#userInfo').show();
                $('#loginButton').hide();
                $('#results').show();
            // } else {
            //     $('#userInfo').hide();
            //     $('#loginButton').show();
            //     $('#results').hide();
            // }
        }

    </script>
</body>
</html>
