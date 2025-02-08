<?php
session_start();
include "lib/funcs.php";
require_once('../../emorepoconfig/config.php');
sschk();
error_log("IS_DEBUG:".IS_DEBUG);
if(IS_DEBUG){
    $isDebug = "true";
} else {
    $isDebug = "false";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>えもれぽ</title>

    <link rel="stylesheet" href="css/workwell.css">
    <link rel="stylesheet" href="css/workwell-dashboard.css">
    <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
    <link rel="shortcut icon" href="img/transformnavi_favicon.png" type="image/x-icon">
</head>
<body>
    <div class="layout-container">
        <!-- Header Navigation -->
        <header class="dashboard-header">
            <div class="header-left">
                <img src="img/transformnavi.png" alt="えもれぽ" class="app-logo">
                <h1 class="brand-name nico-moji">えもれぽ</h1>
            </div>

            <div class="nav-container">
                <div class="header-right">
                    <div class="user-controls">
                        <?=$_SESSION["name"]?>さん

                        <nav class="nav-links">
                            <button class="icon-button" aria-label="通知">
                                <svg width="20" height="20" viewBox="0 0 256 256"><path d="M221.8,175.94C216.25,166.38,208,139.33,208,104a80,80,0,1,0-160,0c0,35.34-8.26,62.38-13.81,71.94A16,16,0,0,0,48,200H88.81a40,40,0,0,0,78.38,0H208a16,16,0,0,0,13.8-24.06Z" fill="currentColor"/></svg>
                            </button>
                            <button class="icon-button" aria-label="メッセージ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256">
                                    <path d="M140,128a12,12,0,1,1-12-12A12,12,0,0,1,140,128ZM84,116a12,12,0,1,0,12,12A12,12,0,0,0,84,116Zm88,0a12,12,0,1,0,12,12A12,12,0,0,0,172,116Zm60,12A104,104,0,0,1,79.12,219.82L45.07,231.17a16,16,0,0,1-20.24-20.24l11.35-34.05A104,104,0,1,1,232,128Z" />
                                </svg>
                            </button>
                            <a href="userreport.php" class="nav-link">レポート管理</a>
                            <?php if($_SESSION["mgmt_flg"]=="1"){ ?>
                            <a href="users.php" class="nav-link">ユーザー管理</a>
                            <?php } ?>
                            <a href="logout.php" id="logoutButton" class="nav-link">ログアウト</a>
                        </nav>
                    </div>
                </div>
            </div>
            <button class="menu-button" aria-label="メニュー">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </header>

        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="control-panel">
                <button id="startButton">日報入力開始</button>
                <button id="stopButton" disabled>日報入力停止</button>
                <button id="clearButton" disabled>クリア</button>
                <button id="analyzebtn" disabled>日報を提出</button>
                <?php if(IS_DEBUG){ ?>
                <!-- <button id="debugbtn">デバグ用</button>
                <textarea id="testinput"></textarea>
                <div id="debugresults" class="result-section"></div> -->
                <?php } ?>
            </div>
            
            <div class="content-container">
                <div class="content-wrapper">
                    <div class="preview-container">
                        <video id="video" autoplay playsinline></video>
                        <img id="imagePreview" style="display: none;">
                    </div>

                    <div class="status-text-container">
                        <div id="status">待機中...</div>
                        <div class="text-areas">
                            <textarea id="transcriptArea" readonly placeholder="音声入力テキストがここに表示されます..."></textarea>
                            <textarea id="reportArea" placeholder="レポートがここに表示されます..." style="display: none;"></textarea>
                        </div>
                    </div>
                
                    <!-- Debug Panel -->
                    <?php if(IS_DEBUG&&DEBUG_MODE_DB){ ?>
                        <div class="debug-panel">
                            <h4>デバッグパネル</h4>
                            <div class="debug-form">
                                <div class="form-group">
                                    <label for="debug-speech">音声テキスト:</label>
                                    <textarea id="speeech-testinput" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="debug-gemini">AIレポート:</label>
                                    <textarea id="debug-gemini" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="debug-text-emotion">テキスト感情分析結果:</label>
                                    <textarea id="debug-text-emotion" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="debug-image-emotion">画像感情分析結果:</label>
                                    <textarea id="debug-image-emotion" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="debug-image">画像データ（Base64）:</label>
                                    <textarea id="debug-image" class="form-control"></textarea>
                                </div>
                                <button id="debugbtn" class="btn btn-warning">デバッグ送信</button>
                            </div>
                            <div id="debugresults" class="result-section"></div>
                        </div>
                    <?php } ?>
                    
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="js/emotionalDictionary.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="module">
        import { WebApiClient } from './js/WebAPIClient.js';
        import { CameraModule } from './js/camera.js';
        import { TextEmotionResultRenderer } from './js/TextEmotionResultRenderer.js';
        import { ImageEmotionAnalyzer } from './js/ImageEmotionAnalyzer.js';
        import { WebSpeechService } from './js/WebSpeechService.js';
        import { DisplayModule } from './js/displayUtils.js';
        import {auth, provider, app, db, storage, googleAuthLaterProcess, logOut} from './js/FirebaseInit.js';
        import { EmotionScoreCalculator } from './js/EmotionScoreCalculator.js';

        const isDebug = <?=$isDebug?>;

        if(isDebug) {
            console.log('@@@@@@@ Debug Mode @@@@@@@');
            const debuginput = localStorage.getItem('debug');
            if(debuginput) {
                $('#testinput').val(debuginput);
            }
        }

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

        const webSpeech = new WebSpeechService(controls, isDebug);

        const domain = location.origin;
        const path = "/emorepo";
        const domainpath = domain + path;
        const AiFrontPath = '/airequest.php';
        const RequestTextAnalyzePath = '/RequestTextAnalyze.php';

        const reportinfo = {
            uid: null,
            datetime: null,
            reportoriginal: null,
            reportformatted: null,
            faceimage: null,
            imageanalyzed: null,
            textanalyzed: null,
            documentScore: null,
            terminologyScore: null,
            emotionScore: null
        };
        let inputText = "";
        let beforeInputText = "";
        const lid = '<?=$_SESSION["lid"]?>';
        console.log('lid:', lid);



        $(document).ready(async function() {
            updateUI();
            
            //録音開始ボタン
            controls.$startButton.on('click',async function() {
                console.log('Start Recording');
                await CameraModule.init();
                beforeInputText = inputText;
                webSpeech.start();
                analyzeEL.$emoreport.hide();
            });

            //録音停止ボタン
            controls.$stopButton.on('click', async () => {
                try {
                    // オーバーレイを表示
                    $('#processing-overlay').show();
                    
                    // 録音停止処理
                    webSpeech.stop();
                    
                    // UI更新：処理中表示
                    controls.$status.html(`
                        <div class="processing-status">
                            <div class="spinner-border text-primary"></div>
                            <span class="ml-2">日報作成中...</span>
                        </div>
                    `);
                    controls.$transcriptArea.prop('readonly', true);
                    controls.$stopButton.prop('disabled', true);
                    controls.$clearButton.prop('disabled', true);
                    
                    // 画像キャプチャ
                    const imageData = CameraModule.captureImage();
                    DisplayModule.updatePreview(imageData);
                    
                    // 日報生成用のデータ準備
                    const inputText = controls.$transcriptArea.val().trim();
                    if(isDebug) {
                        reportinfo.reportoriginal = inputText || controls.$testinput.val();
                    } else {
                        reportinfo.reportoriginal = inputText;
                    }
                    
                    // AI Request PHP向けのインスタンス生成
                    const AIapiClient = new WebApiClient(domainpath, isDebug);
                    console.log('Sending request to:', AiFrontPath);
                    const textObj = {};
                    if(isDebug) {
                        textObj.speechtext = inputText || controls.$testinput.val();
                    } else {
                        textObj.speechtext = inputText;
                    }

                    const response = await AIapiClient.postFormData(AiFrontPath, textObj);
                    
                    if (response && response.success) {
                        const formattedText = response.text.replace(/<\/?report>/g, ''); 
                        console.log('Formatted Text:', formattedText);
                        controls.$reportArea.val(formattedText);
                        controls.$reportArea.show();
                        controls.$transcriptArea.hide();
                        reportinfo.reportformatted = formattedText;
                        controls.$status.text('日報が作成されました');
                        analyzeEL.$start.prop('disabled', false);
                    } else {
                        throw new Error(response?.error || '日報の生成に失敗しました');
                    }
                    
                } catch (error) {
                    console.error('Error in stop recording:', error);
                    controls.$status.text('エラーが発生しました');
                    alert('日報の作成に失敗しました: ' + error.message);
                } finally {
                    // オーバーレイを非表示
                    $('#processing-overlay').hide();
                    controls.$clearButton.prop('disabled', false);
                }
            });

            //クリアボタン
            controls.$clearButton.on('click',async function() {
                webSpeech.clear();
                controls.$transcriptArea.val('');
                controls.$transcriptArea.show();
                controls.$reportArea.val('');
                controls.$reportArea.hide();
                beforeInputText = "";
                inputText = "";
                await CameraModule.reset();
                DisplayModule.resetPreview();
                analyzeEL.$start.prop('disabled', true);
                controls.$clearButton.prop('disabled', true);
                controls.$transcriptArea.prop('readonly', true);
            });

            //デバグボタン DEBUGモードのみ表示
            $('#debugbtn').on('click', async function() {
                const debugData = {
                    speechText: $('#speeech-testinput').val() || 'デバッグテスト',
                    dailyReport: $('#debug-gemini').val() || 'デバッグレポート',
                    textEmotionResult: $('#debug-text-emotion').val(),
                    imageEmotionResult: $('#debug-image-emotion').val(),
                    imageUrl: $('#debug-image').val()
                };

                if (!debugData.speechText || !debugData.dailyReport) {
                    $('#debugresults').html('エラー：音声テキストと日次報告は必須です');
                    return;
                }

                try {
                    const debugDBapiClient = new WebApiClient(domainpath, isDebug);
                    const saveResponse = await debugDBapiClient.postFormData('/savereport.php', debugData);
                    
                    if (saveResponse.success) {
                        $('#debugresults').html('保存成功：' + JSON.stringify(saveResponse));
                    } else {
                        $('#debugresults').html('保存失敗：' + saveResponse.error);
                    }
                } catch (error) {
                    $('#debugresults').html('エラー発生：' + error.message);
                    console.error('Debug Error:', error);
                }
            });

            //日報提出（感情分析）ボタン
            analyzeEL.$start.on('click', async () => {
                try {
                    // 処理中表示
                    const $button = analyzeEL.$start;
                    $button.prop('disabled', true)
                        .html(`
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm mr-2"></div>
                                <span>処理中...</span>
                            </div>
                        `);
                    
                    analyzeEL.$emoreport.show();
                    
                    // 日報提出ボタンを無効化し、ローディング表示
                    analyzeEL.$start.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 日報提出中...');
                    
                    reportinfo.reportformatted = controls.$reportArea.val();
                    // テキスト感情分析の実行と待機
                    const textAnalysisResult = await textEmotionAnalyze(reportinfo.reportoriginal);
                    if (!textAnalysisResult || !textAnalysisResult.data) {
                        throw new Error('テキスト分析結果が不正です');
                    }
                    
                    reportinfo.textanalyzed = textAnalysisResult;


                    
                    // 画像分析の実行
                    const imageData = controls.$faceImage.attr('src');
                    if (!imageData) {
                        throw new Error('画像データが見つかりません');
                    }
                    
                    // まず画像をアップロード
                    const imageApiClient = new WebApiClient(domainpath, isDebug);
                    const uploadResponse = await imageApiClient.postFormData('/upload_image.php', {
                        imageData: imageData
                    });

                    if (!uploadResponse.success) {
                        throw new Error('画像のアップロードに失敗しました');
                    }

                    reportinfo.faceimage = uploadResponse.imageUrl;

                    // 画像分析を実行
                    const imageAnalysisResult = await imageApiClient.postFormData('/RequestImageAnalyze.php', {
                        imageUrl: uploadResponse.imageUrl
                    });

                    if (!imageAnalysisResult.success) {
                        throw new Error(imageAnalysisResult.error || '画像分析に失敗しました');
                    }

                    reportinfo.imageanalyzed = imageAnalysisResult.data;
                    
                    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                    //TODO: スコア計算のロジック見直し ★★
                    const scoreCalculator = new EmotionScoreCalculator();
                    const scores = scoreCalculator.calculateScores(
                        textAnalysisResult.data,
                        reportinfo.reportoriginal,
                        imageAnalysisResult
                    );
                    
                    reportinfo.documentScore = scores.documentScore;
                    reportinfo.terminologyScore = scores.terminologyScore;
                    // reportinfo.emotionScore = scoreCalculator.calculateEmotionScore(imageAnalysisResult);
                    reportinfo.emotionScore = scores.emotionScore;

                    // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


                    // PHPの受信時に問題を起こす\nをエスケープ
                    const escapedReport = reportinfo.reportformatted
                        .replace(/\\/g, '\\\\')
                        .replace(/\n/g, '\\n');
                    // レポートデータのDB保存
                    const reportData = {
                        speechText: reportinfo.reportoriginal,
                        dailyReport: escapedReport,
                        textEmotionResult: JSON.stringify(reportinfo.textanalyzed),
                        imageEmotionResult: JSON.stringify(imageAnalysisResult),
                        imageUrl: uploadResponse.imageUrl,
                        documentScore: reportinfo.documentScore,
                        terminologyScore: reportinfo.terminologyScore,
                        emotionScore: reportinfo.emotionScore
                    };
                    if(isDebug) {
                        console.log('Report Data:');
                        console.log(reportData);
                    }

                    const DBapiClient = new WebApiClient(domainpath, isDebug);
                    const saveResponse = await DBapiClient.postFormData('/savereport.php', reportData);
                    
                    if (saveResponse.success) {
                        window.location.href = `reportdetail.php?id=${saveResponse.reportId}`;
                    } else {
                        throw new Error(saveResponse?.error || 'レポートの保存に失敗しました');
                    }
                    
                } catch (error) {
                    console.error('Error in report submission:', error);
                    alert(error.message || '処理中にエラーが発生しました');
                } finally {
                    // ボタンの状態を元に戻す
                    analyzeEL.$start.prop('disabled', false).html('日報を提出');
                }
            });

            controls.$logout.on('click', () =>  {
                if(!user) alert('ログインしていません');
                logOut(auth);
            });
        });

        async function textEmotionAnalyze(text) {
            if (!text) {
                throw new Error('分析するテキストが空です');
            }

            const textPostData = { 
                textanalyzerequest: text,
                type: 'emotion'
            };

            const textApiClient = new WebApiClient(domainpath, isDebug);
            const renderer = new TextEmotionResultRenderer(analyzeEL, isDebug);

            try {
                if (isDebug) {
                    console.log('Sending text analysis request:', textPostData);
                }

                const response = await textApiClient.postFormData(RequestTextAnalyzePath, textPostData);
                
                // 分析結果の表示

                if (response.success) {
                    controls.$reportArea.val(response.text);
                    const html = renderer.generateResultHtml(response.data);
                    analyzeEL.$output.html(html);
                    return response; // 分析結果を返す  
                } else {
                    throw new Error(response.error || 'テキスト分析に失敗しました');
                }
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

        function updateUI() {
            $('#userInfo').show();
            $('#loginButton').hide();
            $('#results').show();
        }
        // メニューボタンのクリックイベント
        $('.menu-button').on('click', function() {
        $('.nav-links').toggleClass('show');
        });

        // メニュー外クリックで閉じる
        $(document).on('click', function(e) {
        if (!$(e.target).closest('.menu-button, .nav-links').length) {
            $('.nav-links').removeClass('show');
        }
        });
    </script>
    <div id="processing-overlay" class="processing-overlay" style="display: none;">
        <div class="processing-content">
            <div class="spinner-border text-light"></div>
            <p class="mt-3 text-light">日報作成中...</p>
        </div>
    </div>
</body>
</html>