<?php
/**
 * Dify APIとの通信を行うクラス
 */
class DifyAPIService {
    private string $apiKey;
    private bool $isDebug;
    private string $apiEndpoint;
    
    public function __construct(string $apiKey, string $apiEndpoint, bool $isDebug = false) {
        $this->apiKey = $apiKey;
        $this->apiEndpoint = $apiEndpoint;
        $this->isDebug = $isDebug;
    }
    
    private function logDebug(string $methodName, ?array $params = null, $result = null): void {
        if ($this->isDebug) {
            error_log("[DifyAPI] {$methodName}");
            if ($params) error_log('Parameters: ' . json_encode($params));
            if ($result) error_log('Result: ' . json_encode($result));
        }
    }

    /**
     * Dify APIにプロンプトを送信し、レスポンスを取得
     */
    public function generateResponse(string $prompt): array {
        $this->logDebug('generateResponse', ['prompt' => $prompt]);
        
        $data = [
            'inputs' => [],
            'query' => $prompt,
            'response_mode' => 'blocking',
            'conversation_id' => '',
            'user' => 'default'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiEndpoint . '/chat-messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        try {
            $response = curl_exec($ch);
            $this->logDebug('Response', ['response' => $response]);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode !== 200) {
                $error = curl_error($ch);
                $responseData = json_decode($response, true);
                $errorMessage = $responseData['message'] ?? $error;
                throw new Exception("API request failed: {$httpCode}, Error: {$errorMessage}");
            }

            $result = json_decode($response, true);
            $text = $result['answer'] ?? null;
            
            $this->logDebug('generateResponse:result', null, $text);
            
            return [
                'success' => true,
                'text' => $text
            ];
            
        } catch (Exception $e) {
            error_log('DifyAPI Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => "エラーが発生しました: {$e->getMessage()}"
            ];
        } finally {
            curl_close($ch);
        }
    }
} 