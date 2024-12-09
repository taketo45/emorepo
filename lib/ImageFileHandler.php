<?php
class ImageFileHandler {
    private $uploadDir;
    private $isDebug;

    public function __construct($isDebug = false) {
        $this->uploadDir = dirname(__DIR__) . '/uploads/faces/';
        $this->isDebug = $isDebug;
    }

    public function saveImage($base64Image, $userId) {
        if (empty($base64Image)) {
            return null;
        }

        // Base64データから画像データを抽出
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
        
        // ファイル名生成（ユニーク性を確保）
        $fileName = date('Ymd_His') . '_' . $userId . '_' . uniqid() . '.jpg';
        $filePath = $this->uploadDir . $fileName;
        
        if ($this->isDebug) {
            error_log("Saving image to: " . $filePath);
        }

        // ファイル保存
        if (!file_put_contents($filePath, $imageData)) {
            throw new Exception('画像ファイルの保存に失敗しました');
        }

        // 相対パスを返す
        return 'uploads/faces/' . $fileName;
    }
} 