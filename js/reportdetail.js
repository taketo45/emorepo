$(document).ready(function() {
    // メニュー操作の初期化
    initializeMenu();
    
    // レポート内容の表示
    initializeReportContent();
    
    // 感情分析結果の表示
    if ($('.face-emotion-analysis').length) {
        initializeFaceEmotionDisplay();
    }
    initializeTextEmotionDisplay();
});

function initializeMenu() {
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
}

function initializeReportContent() {
    const reportData = reportContent; // PHP側で定義された変数
    if (reportData) {
        const escapedReport = reportData
            .replace(/\\n/g, '\n')
            .replace(/\\/g, '');
        const htmlContent = marked.parse(escapedReport);
        $('#report-dynamic').html(htmlContent);
    }
}

async function initializeTextEmotionDisplay() {
    const textEmotionData = textEmotionContent; // PHP側で定義された変数
    if (!textEmotionData) return;

    try {
        const parsedData = JSON.parse(textEmotionData);
        const renderer = new TextEmotionResultRenderer(analyzeEL, isDebug);
        
        if (parsedData.success) {
            const html = await renderer.generateResultHtml(parsedData.data);
            analyzeEL.$output.html(html);
            
            if (!isDebug) {
                $('#emotion-result-display').hide();
            }
        }
    } catch (error) {
        console.error('Error displaying text emotion:', error);
        $('#textEmotionResults').html('<p>テキスト感情分析データの表示中にエラーが発生しました</p>');
    }
}

function initializeFaceEmotionDisplay() {
    const imageEmotionData = imageEmotionContent; // PHP側で定義された変数
    if (!imageEmotionData) return;

    try {
        const parsedData = JSON.parse(imageEmotionData);
        if (parsedData.success && parsedData.data && parsedData.data.responses) {
            DisplayModule.displayResults(parsedData.data);
        }
    } catch (error) {
        console.error('Error displaying face emotion:', error);
        $('#resultsContainer').html('<p>表情分析データの表示中にエラーが発生しました</p>');
    }
} 