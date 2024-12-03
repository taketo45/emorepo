<?php
/**
 * Google Gemini APIとの通信を行うクラス
 * 
 */
class GeminiAPIService {
    private string $apiKey;
    private bool $isDebug;
    private string $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.0-pro:generateContent';
    
    public function __construct(string $apiKey, bool $isDebug = false) {
        $this->apiKey = $apiKey;
        $this->isDebug = $isDebug;
    }
    
    private function logDebug(string $methodName, ?array $params = null, $result = null): void {
        if ($this->isDebug) {
            error_log("[GeminiAPI] {$methodName}");
            if ($params) error_log('Parameters: ' . json_encode($params));
            if ($result) error_log('Result: ' . json_encode($result));
        }
    }

    /**
     * Gemini APIにプロンプトを送信し、レスポンスを取得
     */
    public function generateResponse(string $prompt): array {
        $this->logDebug('generateResponse', ['prompt' => $prompt]);

        
        
        $data = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => [
                'temperature' => 0.9,
                'topK' => 1,
                'topP' => 1,
                'maxOutputTokens' => 2048,
            ]
        ];
        //デバッグ用
        $this->logDebug('Request Body', ['data' => $data]);


        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiEndpoint . '?key=' . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        try {
            $response = curl_exec($ch);
            //デバッグ用
            $this->logDebug('Response', ['response' => $response]);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode !== 200) {
              $error = curl_error($ch);
              throw new Exception("API request failed:  {$httpCode}, Error: {$error}");
            }

            $result = json_decode($response, true);
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            $this->logDebug('generateResponse', null, $text);
            
            return [
                'success' => true,
                'text' => $text
            ];
            
        } catch (Exception $e) {
            error_log('GeminiAPI Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => "エラーが発生しました: {$e->getMessage()}"
            ];
        } finally {
            curl_close($ch);
        }
    }
}