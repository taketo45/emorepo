<?php



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
                        <h3 class="section-title">レポート内容</h3>
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
                                <span class="score-label">文書品質</span>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?=h($report['document_score'])?>%"></div>
                                    <span class="score-value"><?=h($report['document_score'])?></span>
                                </div>
                            </div>
                            <div class="score-item">
                                <span class="score-label">用語適切性</span>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?=h($report['terminology_score'])?>%"></div>
                                    <span class="score-value"><?=h($report['terminology_score'])?></span>
                                </div>
                            </div>
                            <div class="score-item">
                                <span class="score-label">感情スコア</span>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?=h($report['emotion_score'])?>%"></div>
                                    <span class="score-value"><?=h($report['emotion_score'])?></span>
                                </div>
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

    <!-- 既存のJavaScript部分は維持 -->
    <script type="module">
        // 既存のJavaScript処理を維持
    </script>
</body>
</html>