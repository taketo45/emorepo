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

$sql = "SELECT 
    r.report_id,
    r.created_time,
    r.speech_text,
    r.gemini_report,
    r.report_summary,
    r.image_url,
    r.ai_text_emotion,
    r.image_emotion,
    r.document_score,
    r.terminology_score,
    r.emotion_score,
    u.name as user_name
FROM tn_report_table r
JOIN tn_user_table u ON r.user_id = u.id
WHERE r.report_id = :report_id";
$report = $db->select($sql, [':report_id' => $reportId]);

if (!$report) {
    header('Location: userreport.php');
    exit;
}


$report = $report[0];
// error_log("report['GeminiReport']: " . $report['GeminiReport']);


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レポート詳細 - Transform Navi</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
</head>

<body>
    <div class="container">
        <h2>レポート詳細</h2>
        <div class="report-detail">
            <div class="report-header">
                <h3>作成日時: <?= h($report['created_time'] ? date('Y年m月d日 H:i', strtotime($report['created_time'])) : '未設定') ?></h3>
            </div>

            <div class="report-content">
                <h4>日報内容</h4>
                <div id="report-dynamic" class="report-text">
                </div>

                <h4>感情分析結果</h4>
                <div class="emotion-analysis">
                    <?php if ($report['image_url']): ?>
                        <div class="face-emotion-analysis">
                            <h5>表情分析</h5>
                            <img src="<?= h($report['image_url']) ?>"
                                alt="表情分析画像"
                                class="emotion-image">
                            <div class="emotion-result-debug" id="imageEmotionResults-debug">
                            </div>
                            <div id="resultsContainer"></div>
                            <div style="max-width: 300px; margin: auto;">
                                <canvas id="emotionChart"></canvas>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="text-emotion-analysis">
                        <h5>テキスト感情分析</h5>
                        <div id="textEmotionResults-debug"></div>
                        <div class="emotion-result" id="emotion-result-display"></div>
                        <div id="textEmotionResults"></div>
                    </div>
                </div>

                <div class="score-section">
                    <h4>評価スコア</h4>
                    <div class="scores">
                        <div class="score-item">
                            <label>文書品質:</label>
                            <span><?= h($report['document_score']) ?>点</span>
                        </div>
                        <div class="score-item">
                            <label>用語適切性:</label>
                            <span><?= h($report['terminology_score']) ?>点</span>
                        </div>
                        <div class="score-item">
                            <label>感情スコア:</label>
                            <span><?= h($report['emotion_score']) ?>点</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="userreport.php" class="btn btn-secondary">一覧に戻る</a>
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
            const reportData = <?php echo json_encode($report['gemini_report'] ?? '') ?>;
            const escapedReport = reportData
                .replace(/\\n/g, '\n') // エスケープされた改行を実際の改行に変換
                .replace(/\\/g, ''); // 残りのバックスラッシュを削除
            const htmlContent = marked.parse(escapedReport);
            $('#report-dynamic').html(htmlContent);

            if ($('.face-emotion-analysis').length) { // 画像がある場合のみ
                initializeFaceEmotionDisplay();
            }

            const textEmotionData = <?php echo json_encode($report['ai_text_emotion']) ?>;
            parseTextEmotionAnalyze(textEmotionData ,isDebug);

            // initializeTextEmotionDisplay();
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

            if (isDebug) {
                console.log('parsedData');
                console.log(parsedData);
            }

            try {
                if (parsedData.success) {
                    analyzeEL.$emoreport.val(parsedData.text);
                    const html = await renderer.generateResultHtml(parsedData.data);
                    console.log('html');
                    console.log(html);
                    analyzeEL.$output.html(html);
                } else {
                    throw new Error(parsedData.error || 'テキスト分析に失敗しました');
                }
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        }

// @@@@@@@@@@@@@   Back Up  @@@@@@@@@@@@@@@@@@@
        function initializeTextEmotionDisplay() {
            const textEmotionData = <?php echo json_encode($report['ai_text_emotion'] ?? '') ?>;

            if (textEmotionData) {
                try {
                    // JSONパースとエスケープ文字の処理
                    const parsedData = JSON.parse(textEmotionData);

                    if (isDebug) {
                        $('#textEmotionResults-debug').html(parsedData);
                    }

                    // if (parsedData.success) {
                    // controls.$reportArea.val(response.text);
                    // const html = renderer.generateResultHtml(response.data);
                    // analyzeEL.$output.html(html);

                    // interpretationの説明文のエスケープを解除
                    if (parsedData.interpretation && parsedData.interpretation.explanation) {
                        parsedData.interpretation.explanation = parsedData.interpretation.explanation
                            .replace(/\\n/g, '\n')
                            .replace(/\\/g, '');
                    }


                    console.log('parsedData');
                    conso.le.log(parsedData);

                    const renderer = new TextEmotionResultRenderer({
                        container: document.getElementById('textEmotionResults')
                    });

                    const html = renderer.generateResultHtml(parsedData);
                    $('#textEmotionResults').html(html);
                    // 既存の表示は維持
                    if (!isDebug) {
                        $('#emotion-result-display').hide(); // JSON表示は隠す
                    }

                } catch (error) {
                    console.error('Error displaying text emotion:', error);
                    console.error('Error details:', error.message);
                    $('#textEmotionResults').html('<p>テキスト感情分析データの表示中にエラーが発生しました</p>');
                }
            }
        }
    </script>

</body>

</html>