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
                    <a href="userreport.php" class="btn btn-secondary">一覧に戻る</a>
                </div>
            </div>
        </main>
    </div>

    <div class="menu-overlay"></div>

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
                } else {
                    throw new Error(parsedData.error || 'テキスト分析に失敗しました');
                }
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

    </script>

</body>

</html>