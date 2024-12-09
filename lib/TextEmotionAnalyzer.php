<?php
/**
 * Google Cloud(NLP Analyze Service)との通信を行うクラス
 * 
 */

 error_reporting(E_ALL);
ini_set('display_errors', 0); // ブラウザへの表示を無効化
ini_set('log_errors', 1);  

class TextEmotionAnalyzer {
    private string $apiKey;
    private bool $isDebug;
    private string $apiEndpoint;
    private array $emotionalWords; // プロパティを追加
    
    public function __construct(array $config, array $emotionalWords, bool $isDebug = false) {
        $this->isDebug = $isDebug;
        $this->apiEndpoint = $config['apiEndpoint'];
        $this->apiKey = $config['apiKey'];
        $this->emotionalWords = $this->formatEmotionalWords($emotionalWords);
    }

    private function logDebug(string $methodName, ?array $params = null, $result = null): void{
        if($this->isDebug) {
            error_log("[TextEmotionAnalyzer] {$methodName}");
            if($params) error_log("Parameters: {$params}");
            if($result) error_log("Result: {$result}");
        }
    }
    
    private function logDebugJ(string $methodName, ?array $params = null, $result = null): void {
        if ($this->isDebug) {
            error_log("[NLP API] {$methodName}");
            if ($params) error_log('Parameters: ' . json_encode($params));
            if ($result) error_log('Result: ' . json_encode($result));
        }
    }

    /**
     * DBから取得した感情辞書データを整形
     */
    private function formatEmotionalWords(array $words): array {
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

    /**
     * テキストの感情分析を実行
     */
    public function analyzeText(string $inputText): array {
        $this->logDebugJ('analyzeText', ['text' => $inputText], null);
        error_log("Endpoint: ".$this->apiEndpoint);
        $requestData = [
            'document' => [
                'type' => 'PLAIN_TEXT',
                'content' => $inputText
            ],
            'encodingType' => 'UTF8'
        ];

        if ($this->isDebug) {
            error_log("API Request URL: {$this->apiEndpoint}?key={$this->apiKey}");
            error_log("API Request Data: " . json_encode($requestData));
        }

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "{$this->apiEndpoint}?key={$this->apiKey}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($requestData)
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new Exception('Curl error: ' . curl_error($ch));
            }
            curl_close($ch);

            // HTTPステータスコードのチェック
            if ($httpCode !== 200) {
                throw new Exception("API returned error status: " . $httpCode);
            }

            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg());
            }

            // レスポンスの構造チェック
            if (!isset($responseData['documentSentiment']) || 
                !isset($responseData['documentSentiment']['score']) || 
                !isset($responseData['documentSentiment']['magnitude'])) {
                throw new Exception("Invalid API response structure");
            }

            $responseData = json_decode($response, true);
            $emotionDict = $this->analyzeEmotionWithDictionary($inputText);
            $sentiment = $this->interpretSentiment(
                $responseData['documentSentiment']['score'],
                $responseData['documentSentiment']['magnitude']
            );

            $result = [
                'cloudApi' => [
                    'score' => $responseData['documentSentiment']['score'],
                    'magnitude' => $responseData['documentSentiment']['magnitude']
                ],
                'dictionary' => $emotionDict,
                'interpretation' => $sentiment,
                'text' => $inputText
            ];

            $this->logDebugJ('analyzeText', null, $result);
            return $result;

        } catch (Exception $e) {
            $this->logDebugJ('analyzeText', null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 感情辞書を使用したテキスト分析
     */
    private function analyzeEmotionWithDictionary(string $text): array {
        $this->logDebugJ('analyzeEmotionWithDictionary', ['text' => $text], null);

        $emotions = [];
        $totalWords = 0;

        foreach ($this->emotionalWords as $emotion => $data) {
            $matches = array_filter($data['words'], function($word) use ($text) {
                return strpos($text, $word) !== false;
            });
            
            $emotions[$emotion] = [
                'count' => count($matches),
                'words' => array_values($matches)
            ];
            $totalWords += count($matches);
        }

        if ($totalWords > 0) {
            foreach ($emotions as $emotion => $data) {
                $emotions[$emotion]['score'] = $data['count'] / $totalWords;
            }
        }

        $this->logDebugJ('analyzeEmotionWithDictionary', null, $emotions);
        return $emotions;
    }

    /**
     * 感情スコアの解釈
     */
    private function interpretSentiment(float $score, float $magnitude): array {
        $this->logDebugJ('interpretSentiment', [
            'score' => $score,
            'magnitude' => $magnitude
        ], null);

        $type = $score > 0.25 ? '建設的/明確' : 
               ($score < -0.25 ? '否定的/批判的' : '中立的');

        $strength = $magnitude > 0.6 ? '強い' : 
                   ($magnitude > 0.3 ? '中程度' : '弱い');

        $result = [
            'type' => $type,
            'strength' => $strength,
            'explanation' => sprintf(
                'このテキストは%s%sな表現です。\n感情スコア(%.2f)は表現の肯定/否定を、\n強度(%.2f)は感情の強さを示しています。',
                $strength,
                $type,
                $score,
                $magnitude
            )
        ];

        $this->logDebugJ('interpretSentiment', null, $result);
        return $result;
    }
}