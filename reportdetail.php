<?php
session_start();
include("lib/funcs.php");
sschk();

require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

// レポートIDの取得
$reportId = $_GET['id'] ?? null;
if (!$reportId) {
    header('Location: userreport.php');
    exit;
}

// DB接続とレポート取得
$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo);

$sql = "SELECT r.*, u.name as user_name 
        FROM tn_report_table r 
        JOIN tn_user_table u ON r.user_id = u.id 
        WHERE r.report_id = :report_id";
$report = $db->select($sql, [':report_id' => $reportId]);

if (!$report) {
    header('Location: userreport.php');
    exit;
}

$report = $report[0];
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>えもれぽ - レポート詳細</title>
    <link rel="stylesheet" href="css/workwell.css">
    <link rel="stylesheet" href="css/workwell-report.css">
    <link rel="stylesheet" href="css/emorepo-reportdetail.css">
    <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <button class="menu-button" aria-label="メニュー">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <nav class="nav-links">
                    <?=$_SESSION["name"]?>さん
                    <a href="userInformation.php" class="nav-link">レポート作成</a>
                    <a href="userreport.php" class="nav-link">レポート管理</a>
                    <?php if($_SESSION["mgmt_flg"]=="1"){ ?>
                    <a href="users.php" class="nav-link">ユーザー管理</a>
                    <?php } ?>
                    <a href="logout.php" class="nav-link">ログアウト</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="report-content">
            <div class="report-container">
                <div class="report-header">
                    <h2 class="report-title">レポート詳細</h2>
                    <div class="report-meta">
                        <span class="report-date">
                            <?=date('Y年m月d日 H:i', strtotime($report['created_time']))?>
                        </span>
                    </div>
                </div>

                <div class="report-body">
                    <section class="report-section">
                        <h3 class="section-title">日報内容</h3>
                        <div id="report-dynamic" class="report-text"></div>
                    </section>

                    <?php if ($report['image_url']): ?>
                    <section class="report-section">
                        <h3 class="section-title">表情分析</h3>
                        <div class="face-emotion-analysis">
                            <div class="emotion-image-container">
                                <img src="<?=h($report['image_url'])?>" alt="表情分析画像" class="emotion-image">
                            </div>
                            <div class="emotion-chart-container">
                                <canvas id="emotionChart"></canvas>
                            </div>
                            <?php if (IS_DEBUG): ?>
                            <div class="emotion-result-debug" id="imageEmotionResults-debug"></div>
                            <?php endif; ?>
                            <div id="resultsContainer" class="emotion-results"></div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <section class="report-section">
                        <h3 class="section-title">テキスト感情分析</h3>
                        <div class="text-emotion-analysis">
                            <?php if (IS_DEBUG): ?>
                            <div id="textEmotionResults-debug"></div>
                            <?php endif; ?>
                            <div class="emotion-result" id="emotion-result-display"></div>
                            <div id="textEmotionResults" class="emotion-results"></div>
                        </div>
                    </section>

                    <section class="report-section">
                        <h3 class="section-title">評価スコア</h3>
                        <div class="score-grid">
                            <div class="score-item">
                                <span class="score-label">文書感情スコア</span>
                                <div class="score-bar-container">
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?=h($report['document_score']*10)?>%"></div>
                                        <div class="score-center-marker"></div>
                                    </div>
                                    <div class="score-scale">
                                        <span class="score-scale-start">ネガティブ</span>
                                        <span class="score-scale-center">▲</span>
                                        <span class="score-scale-end">ポジティブ</span>
                                    </div>
                                </div>
                                <span class="score-value"><?=h($report['document_score'])?></span>
                            </div>
                            <div class="score-item">
                                <span class="score-label">用語感情スコア</span>
                                <div class="score-bar-container">
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?=h($report['terminology_score']*10)?>%"></div>
                                        <div class="score-center-marker"></div>
                                    </div>
                                    <div class="score-scale">
                                        <span class="score-scale-start">ネガティブ</span>
                                        <span class="score-scale-center">▲</span>
                                        <span class="score-scale-end">ポジティブ</span>
                                    </div>
                                </div>
                                <span class="score-value"><?=h($report['terminology_score'])?></span>
                            </div>
                            <div class="score-item">
                                <span class="score-label">表情感情スコア</span>
                                <div class="score-bar-container">
                                    <div class="score-bar">
                                        <div class="score-fill" style="width: <?=h($report['emotion_score']*10)?>%"></div>
                                        <div class="score-center-marker"></div>
                                    </div>
                                    <div class="score-scale">
                                        <span class="score-scale-start">ネガティブ</span>
                                        <span class="score-scale-center">▲</span>
                                        <span class="score-scale-end">ポジティブ</span>
                                    </div>
                                </div>
                                <span class="score-value"><?=h($report['emotion_score'])?></span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="report-footer">
                    <?php
                    $hasLowScore = ($report['document_score'] < 5 || 
                                    $report['terminology_score'] < 5 || 
                                    $report['emotion_score'] < 5);
                    ?>
                    <?php if ($hasLowScore): ?>
                        <button id="stressHandleBtn" class="btn btn-warning">ストレス対処</button>
                    <?php endif; ?>
                    <a href="userreport.php" class="btn btn-secondary" <?= $hasLowScore ? 'disabled' : '' ?>>一覧に戻る</a>
                </div>
            </div>
        </main>
    </div>

    <div class="menu-overlay"></div>

    <div id="stressModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h4>ストレス対処アドバイス</h4>
            </div>
            <div class="modal-body">
                <div id="stressResponse"></div>
            </div>
            <div class="modal-footer">
                <button id="closeStressModal" class="btn btn-secondary">閉じる</button>
            </div>
        </div>
    </div>

    <script type="module">
        import {DisplayModule} from './js/displayUtils.js';
        import {TextEmotionResultRenderer} from './js/TextEmotionResultRenderer.js';

        const isDebug = <?php echo IS_DEBUG;?> || false;
        const isDebugDetail = false;

        const analyzeEL = {
            $input: $('#transcriptArea'),
            $start: $('#analyzebtn'),
            $output: $('#textEmotionResults'),
            $emoreport: $('#emotion-result-display')
        }

        window.DisplayModule = DisplayModule;
        window.TextEmotionResultRenderer = TextEmotionResultRenderer;

        $(document).ready(function() {
            // メニュー操作の初期化
            const $menuButton = $('.menu-button');
            const $navLinks = $('.nav-links');
            const $body = $('body');
            const $overlay = $('.menu-overlay');

            $menuButton.on('click', function() {
                $navLinks.toggleClass('show');
                $overlay.toggleClass('show');
                $body.toggleClass('menu-open');
            });

            $overlay.on('click', function() {
                $navLinks.removeClass('show');
                $overlay.removeClass('show');
                $body.removeClass('menu-open');
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.menu-button, .nav-links').length) {
                    $navLinks.removeClass('show');
                    $overlay.removeClass('show');
                    $body.removeClass('menu-open');
                }
            });

            // レポート内容の表示
            const reportData = <?php echo json_encode($report['gemini_report'] ?? '') ?>;
            const escapedReport = reportData
                .replace(/\\n/g, '\n')
                .replace(/\\/g, '');
            const htmlContent = marked.parse(escapedReport);
            $('#report-dynamic').html(htmlContent);

            // 感情分析結果の表示
            if ($('.face-emotion-analysis').length) {
                initializeFaceEmotionDisplay();
            }

            const textEmotionData = <?php echo json_encode($report['ai_text_emotion']) ?>;
            parseTextEmotionAnalyze(textEmotionData, isDebug);

            // ストレス対処ボタンのイベントハンドラ
            $('#stressHandleBtn').on('click', async function() {
                const button = $(this);
                const modal = $('#stressModal');
                const responseArea = $('#stressResponse');
                
                try {
                    await loadKeywordMap();  // キーワードマップの読み込み
                    button.prop('disabled', true);
                    modal.show();
                    responseArea.html('<div class="text-center"><div class="spinner-border" role="status"></div><div>アドバイスを生成中...</div></div>');
                    
                    // スコアの取得と評価生成（既存のコード）
                    const documentScore = <?= $report['document_score'] ?>;
                    const terminologyScore = <?= $report['terminology_score'] ?>;
                    const emotionScore = <?= $report['emotion_score'] ?>;
                    
                    let stressEvaluation = '';
                    const scores = [documentScore, terminologyScore, emotionScore];
                    if (scores.some(score => score < 2)) stressEvaluation = 'カウンセリングを推奨する';
                    else if (scores.some(score => score < 3)) stressEvaluation = 'かなり感情が高ぶっている';
                    else if (scores.some(score => score < 4)) stressEvaluation = '感情が高ぶっている';
                    else if (scores.some(score => score < 5)) stressEvaluation = 'やや感情が高ぶっている';

                    const requestText = `短期ストレス評価：${stressEvaluation}\n` +
                        `表情感情分析結果：${emotionScore}\n` +
                        `テキスト感情分析結果：${documentScore}\n` +
                        `用語感情分析結果：${terminologyScore}`;

                    // Difyリクエストの実行
                    const response = await fetch('difyrequest.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            inputText: requestText
                        })
                    });

                    const result = await response.json();
                    if (!result.success) {
                        throw new Error(result.error || 'アドバイス生成に失敗しました');
                    }

                    // レスポンステキストのリンク化
                    const linkedText = convertKeywordsToLinks(result.text);
                    responseArea.html(linkedText);

                } catch (error) {
                    console.error('Error:', error);
                    responseArea.html(`<div class="alert alert-danger">エラーが発生しました: ${error.message}</div>`);
                } finally {
                    button.prop('disabled', false);
                }
            });

            // モーダルを閉じるボタンのイベントハンドラ
            $('#closeStressModal').on('click', function() {
                $('#stressModal').hide();
            });
        });

        function initializeFaceEmotionDisplay() {
            const imageEmotionData = <?php echo json_encode($report['image_emotion'] ?? '') ?>;
            if (isDebug&&isDebugDetail) {
                $('#imageEmotionResults-debug').html(imageEmotionData);
            }

            if (imageEmotionData) {
                try {
                    const parsedData = JSON.parse(imageEmotionData);

                    // DBに保存されているデータは追加の階層を持っているため、
                    // data.responses配列を直接利用
                    if (parsedData.success && parsedData.data && parsedData.data.responses) {
                        DisplayModule.displayResults(parsedData.data);
                    } else {
                        throw new Error('Invalid face emotion data structure');
                    }
                } catch (error) {
                    console.error('Error displaying face emotion:', error);
                    $('#resultsContainer').html('<p>表情分析データの表示中にエラーが発生しました</p>');
                }
            }
        }

        async function parseTextEmotionAnalyze(textEmotionData, isDebug) {
            if (isDebug) {
                console.log('parseTextEmotionAnalyze'); 
                console.log(textEmotionData);
            }

            const renderer = new TextEmotionResultRenderer(analyzeEL, isDebug);
            const parsedData = JSON.parse(textEmotionData);

            // 総合解釈の改行コードを処理
            if (parsedData.data && parsedData.data.interpretation && parsedData.data.interpretation.explanation) {
                parsedData.data.interpretation.explanation = parsedData.data.interpretation.explanation
                    .replace(/\\n/g, '\n')
                    .replace(/\\/g, '');
            }

            if (isDebug) {
                console.log('parsedData');
                console.log(parsedData);
            }

            try {
                if (parsedData.success) {
                    analyzeEL.$emoreport.val(parsedData.text);
                    const html = await renderer.generateResultHtml(parsedData.data);
                    analyzeEL.$output.html(html);
                    return html;
                } else {
                    throw new Error(parsedData.error || 'テキスト分析に失敗しました');
                }
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

        // 定数定義
        const URL_BASE = 'https://www.google.com/maps/search/?api=1&query=';
        const GEO_LOCATION = '<?php 
            echo isset($_SESSION["geocode"]) && !empty($_SESSION["geocode"]) 
                ? $_SESSION["geocode"] 
                : "35.6812362,139.7649361"; //東京駅がデフォルト値
        ?>';

        // キーワードの配列を定義
        const keywordList = [];

        // CSVファイルの読み込みと解析を修正
        async function loadKeywordMap() {
            try {
                const response = await fetch('keywords.csv');
                const text = await response.text();
                const lines = text.split('\n');
                
                lines.forEach(line => {
                    const keyword = line.trim();
                    if (keyword) {
                        keywordList.push(keyword);
                    }
                });
            } catch (error) {
                console.error('キーワードの読み込みに失敗:', error);
            }
        }

        // テキスト内のキーワードをリンクに変換する関数を修正
        function convertKeywordsToLinks(text) {
            let result = text;
            keywordList.forEach(keyword => {
                const regex = new RegExp(keyword, 'g');
                const encodedKeyword = encodeURIComponent(keyword);
                const url = `${URL_BASE}${GEO_LOCATION}+${encodedKeyword}`;
                result = result.replace(regex, `<a href="${url}" target="_blank">${keyword}</a>`);
            });
            return result;
        }

    </script>

</body>

</html>