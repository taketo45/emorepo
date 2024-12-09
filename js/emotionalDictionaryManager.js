// 感情カテゴリ選択時のカラー自動設定
$('#newEmotion').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const defaultColor = selectedOption.data('color');
    $('#newColor').val(defaultColor);
});

// 初期表示時のデフォルトカラー設定
$(document).ready(async function() {
    const defaultColor = $('#newEmotion option:first').data('color');
    $('#newColor').val(defaultColor);
    
    await loadCurrentDictionary();
});

// JS生成ボタンの処理
$('#generateJs').on('click', function() {
    const $button = $(this);
    $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> 生成中...');

    $.ajax({
        url: 'generate_dictionary.php',
        method: 'POST',
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            alert(response.message);
            loadCurrentDictionary();
        } else {
            alert('エラーが発生しました: ' + response.error);
        }
    })
    .fail(function() {
        alert('通信エラーが発生しました');
    })
    .always(function() {
        $button.prop('disabled', false).text('感情辞書ファイルを生成');
    });
});

// 現在の辞書内容を取得する関数
async function loadCurrentDictionary() {
    try {
        const response = await fetch('js/emotionalDictionary.php');
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const content = await response.text();
        $('#currentJsContent').text(content);
    } catch (error) {
        $('#currentJsContent').text('ファイルの読み込みに失敗しました');
        console.error('Error:', error);
    }
} 