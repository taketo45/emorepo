<?php
/**
 * Anthropic(Claude) APIとの通信を行うクラス
 */
class AnthropicAPIService {
    private string $apiKey;
    private bool $isDebug;
    private string $apiEndpoint = 'https://api.anthropic.com/v1/messages';
    private string $model = 'claude-3-sonnet-20240229';
    
    public function __construct(string $apiKey, bool $isDebug = false) {
        $this->apiKey = $apiKey;
        $this->isDebug = $isDebug;
    }
    
    private function logDebug(string $methodName, ?array $params = null, $result = null): void {
        if ($this->isDebug) {
            error_log("[AnthropicAPI] {$methodName}");
            if ($params) error_log('Parameters: ' . json_encode($params));
            if ($result) error_log('Result: ' . json_encode($result));
        }
    }

    /**
     * Anthropic APIにプロンプトを送信し、レスポンスを取得
     */
    public function generateResponse(string $prompt): array {
        $this->logDebug('generateResponse', ['prompt' => $prompt]);
        
        $data = [
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'model' => $this->model,
            'max_tokens' => 2048,
            'temperature' => 0.9,
            'top_k' => 1,
            'top_p' => 1
        ];
        
        $this->logDebug('Request Body', ['data' => $data]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        try {
            $response = curl_exec($ch);
            $this->logDebug('Response', ['response' => $response]);
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode !== 200) {
                $error = curl_error($ch);
                throw new Exception("API request failed: {$httpCode}, Error: {$error}");
            }

            $result = json_decode($response, true);
            $text = $result['content'][0]['text'] ?? null;
            
            $this->logDebug('generateResponse', null, $text);
            
            return [
                'success' => true,
                'text' => $text
            ];
            
        } catch (Exception $e) {
            error_log('AnthropicAPI Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => "エラーが発生しました: {$e->getMessage()}"
            ];
        } finally {
            curl_close($ch);
        }
    }
}