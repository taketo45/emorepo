<?php
session_start();
require_once("lib/funcs.php");
sschk();
require_once('../../emorepoconfig/config.php');
require_once('lib/DatabaseInfo.php');
require_once('lib/DatabaseController.php');

$dbinfo = new DatabaseInfo();
$db = new DatabaseController($dbinfo, IS_DEBUG);

// 感情カテゴリの取得
$sql = "SELECT DISTINCT emotion, color FROM tn_emotional_words ORDER BY emotion";
$emotions = $db->select($sql);

// 全データの取得
$sql = "SELECT id, emotion, word, color FROM tn_emotional_words ORDER BY emotion, word";
$words = $db->select($sql);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>感情辞書管理</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .word-table { margin-top: 20px; }
        .color-preview { 
            width: 20px; 
            height: 20px; 
            display: inline-block;
            vertical-align: middle;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>感情辞書管理</h2>
        
        <!-- 新規追加フォーム -->
        <div class="card mb-4">
            <div class="card-header">新規単語追加</div>
            <div class="card-body">
                <form id="addWordForm">
                    <div class="form-row">
                        <div class="col-md-3">
                            <select class="form-control" id="newEmotion" required>
                                <?php foreach ($emotions as $emotion): ?>
                                    <option value="<?= h($emotion['emotion']) ?>" 
                                            data-color="<?= h($emotion['color']) ?>">
                                        <?= h($emotion['emotion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="newWord" 
                                   placeholder="新しい単語" required>
                        </div>
                        <div class="col-md-3">
                            <input type="color" class="form-control" id="newColor">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">追加</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- 単語一覧テーブル -->
        <div class="word-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>感情カテゴリ</th>
                        <th>単語</th>
                        <th>色</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $currentEmotion = '';
                    foreach ($words as $word): 
                        if ($currentEmotion !== $word['emotion']): 
                            $currentEmotion = $word['emotion'];
                    ?>
                        <tr class="table-secondary">
                            <td colspan="4"><strong><?= h($word['emotion']) ?></strong></td>
                        </tr>
                    <?php endif; ?>
                        <tr>
                            <td><?= h($word['emotion']) ?></td>
                            <td><?= h($word['word']) ?></td>
                            <td>
                                <span class="color-preview" style="background-color: <?= h($word['color']) ?>"></span>
                                <?= h($word['color']) ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-word" 
                                        data-id="<?= h($word['id']) ?>">更新</button>
                                <button class="btn btn-sm btn-danger delete-word" 
                                        data-id="<?= h($word['id']) ?>">削除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- JS生成ボタン -->
        <div class="text-center mb-4">
            <button id="generateJs" class="btn btn-success">
                感情辞書ファイルを生成
            </button>
        </div>

        <!-- 現在のJSファイル内容 -->
        <div class="card">
            <div class="card-header">現在の感情辞書ファイル内容</div>
            <div class="card-body">
                <pre id="currentJsContent">
                    <?php
                    if (file_exists('js/emotionalDictionary.js')) {
                        echo htmlspecialchars(file_get_contents('js/emotionalDictionary.js'));
                    } else {
                        echo 'ファイルが存在しません';
                    }
                    ?>
                </pre>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/emotional_dictionary_manager.js"></script>
</body>
</html> 