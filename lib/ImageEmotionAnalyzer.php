<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

class ImageEmotionAnalyzer {
    private string $apiKey;
    private bool $isDebug;
    private string $apiEndpoint;
    
    public function __construct(array $config, bool $isDebug = false) {
        $this->isDebug = $isDebug;
        $this->apiEndpoint = $config['apiEndpoint'];
        $this->apiKey = $config['apiKey'];
    }

    private function logDebug(string $methodName, ?array $params = null, $result = null): void {
        if($this->isDebug) {
            error_log("[ImageEmotionAnalyzer] {$methodName}");
            if($params) error_log("Parameters: " . json_encode($params));
            if($result) error_log("Result: " . json_encode($result));
        }
    }

    public function analyzeFaceAndEmotions(string $imageData): array {
        $this->logDebug('analyzeFaceAndEmotions', ['imageLength' => strlen($imageData)]);

        // Base64データからヘッダーを除去
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        
        $requestData = [
            'requests' => [
                [
                    'image' => [
                        'content' => $imageData
                    ],
                    'features' => [
                        [
                            'type' => 'FACE_DETECTION',
                            'maxResults' => 10
                        ]
                    ]
                ]
            ]
        ];

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

            if ($httpCode !== 200) {
                throw new Exception("API returned error status: " . $httpCode);
            }

            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg());
            }

            $this->logDebug('analyzeFaceAndEmotions', null, $responseData);
            return $responseData;

        } catch (Exception $e) {
            $this->logDebug('analyzeFaceAndEmotions', null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function analyzeImageUrl($imageUrl) {
        if ($this->isDebug) {
            error_log("Analyzing image from URL: " . $imageUrl);
        }

        // 画像ファイルの絶対パスを取得
        $absolutePath = realpath(dirname(__DIR__) . '/' . $imageUrl);
        if (!$absolutePath || !file_exists($absolutePath)) {
            throw new Exception('Image file not found: ' . $absolutePath);
        }

        // 画像をバイナリとして読み込み
        $imageContent = file_get_contents($absolutePath);
        if ($imageContent === false) {
            throw new Exception('Failed to read image file: ' . $absolutePath);
        }

        // MIMEタイプの確認
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $absolutePath);
        finfo_close($finfo);

        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            throw new Exception('Invalid image type: ' . $mimeType);
        }

        // Base64エンコード（プレィックスなし）
        $imageData = base64_encode($imageContent);

        if ($this->isDebug) {
            error_log("Image MIME type: " . $mimeType);
            error_log("Image size: " . strlen($imageContent) . " bytes");
            error_log("Base64 size: " . strlen($imageData) . " bytes");
        }

        $requestData = [
            'requests' => [
                [
                    'image' => [
                        'content' => $imageData
                    ],
                    'features' => [
                        [
                            'type' => 'FACE_DETECTION',
                            'maxResults' => 10
                        ]
                    ],
                    'imageContext' => [
                        'languageHints' => ['ja']
                    ]
                ]
            ]
        ];

        if ($this->isDebug) {
            error_log("Request data: " . json_encode($requestData, JSON_PARTIAL_OUTPUT_ON_ERROR));
        }

        return $this->sendRequest($requestData);
    }

    private function sendRequest($requestData) {
        $ch = curl_init();
        $url = "https://vision.googleapis.com/v1/images:annotate?key=" . $this->apiKey;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));

        // デバッグ用の詳細情報を取得
        if ($this->isDebug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {
            $errorMessage = "API Error: ";
            
            // エラーの種類に応�たメッセージ
            switch($httpCode) {
                case 400:
                    $responseData = json_decode($response, true);
                    $errorMessage .= "Bad Request - " . 
                        ($responseData['error']['message'] ?? 'Invalid request format');
                    break;
                case 401:
                    $errorMessage .= "Unauthorized - Invalid API key";
                    break;
                case 403:
                    $errorMessage .= "Forbidden - Insufficient permissions";
                    break;
                default:
                    $errorMessage .= "HTTP Status $httpCode";
            }

            if ($this->isDebug) {
                rewind($verbose);
                error_log("CURL Debug: " . stream_get_contents($verbose));
                error_log("API Response: " . $response);
                error_log($errorMessage);
            }

            throw new Exception($errorMessage);
        }

        curl_close($ch);
        return json_decode($response, true);
    }
} 