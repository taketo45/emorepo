<?php
require_once('DatabaseController.php');

class EmotionalDictionaryGenerator {
    private $db;
    private $isDebug;
    private $sourceFile = '../js/emotionalDictionary.js';
    private $backupDir = '../js/backup';
    
    public function __construct(DatabaseController $db, bool $isDebug = false) {
        $this->db = $db;
        $this->isDebug = $isDebug;
    }
    
    public function generate(): bool {
        try {
            // バックアップディレクトリの確認・作成
            if (!is_dir($this->backupDir)) {
                if (!mkdir($this->backupDir, 0755, true)) {
                    throw new Exception("バックアップディレクトリの作成に失敗しました");
                }
            }
            
            // 既存ファイルのバックアップ
            if (file_exists($this->sourceFile)) {
                $backupFile = $this->backupDir . '/emotionalDictionary_' . date('YmdHis') . '.js';
                if (!copy($this->sourceFile, $backupFile)) {
                    throw new Exception("ファイルのバックアップに失敗しました");
                }
            }
            
            // DBからデータ取得
            $sql = "SELECT emotion, word, color FROM tn_emotional_words ORDER BY emotion, word";
            $words = $this->db->select($sql);
            
            // データを整形
            $formatted = $this->formatData($words);
            
            // JSファイル生成
            $content = $this->generateJsContent($formatted);
            
            // ファイル書き込み
            if (file_put_contents($this->sourceFile, $content) === false) {
                throw new Exception("ファイルの書き込みに失敗しました");
            }
            
            if ($this->isDebug) {
                error_log("感情辞書ファイルの生成が完了しました");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("感情辞書ファイルの生成エラー: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function formatData(array $words): array {
        $formatted = [];
        foreach ($words as $word) {
            if (!isset($formatted[$word['emotion']])) {
                $formatted[$word['emotion']] = [
                    'words' => [],
                    'color' => $word['color']
                ];
            }
            $formatted[$word['emotion']]['words'][] = $word['word'];
        }
        return $formatted;
    }
    
    private function generateJsContent(array $data): string {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return "<?php\nheader('Content-Type: application/json');\n" .
               "echo '" . addslashes($json) . "';\n";
    }
} 