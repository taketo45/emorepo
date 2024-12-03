//@@@@@@@@@@@@@@@@@@@  注意！！！！  @@@@@@@@@@@@@@@@@@@@@@@@@
//本クラスは、比較検討の結果、「webspeechService.js」に置き換えられたため、
//現在どの処理からも呼ばれていません。
//変換精度が高いのでどこかのタイミングで処理に組み込むことを想定して
//ファイルを保持しています。  by 生貝


class SpeechToText {
    constructor(controls, isDebug = false) {
        this.recognition = null;
        this.isRecording = false;
        this.transcriptText = '';
        this.isDebug = isDebug;
        this.controls = controls;
        
        if (!SPEECHTOTEXT_CONFIG || !SPEECHTOTEXT_CONFIG.GOOGLE_API_KEY) {
            console.error('Google Cloud APIキーが設定されていません');
        }
    }

    logDebug(methodName, params = null, result = null) {
        if (this.isDebug) {
            console.log(`[SpeechToText] ${methodName}`);
            if (params) console.log('Parameters:', params);
            if (result) console.log('Result:', result);
        }
    }

    initialize() {
        this.logDebug('initialize');
        
        if (!this.recognition) {
            this.recognition = {
                audioContext: new (window.AudioContext || window.webkitAudioContext)(),
                mediaRecorder: null,
                audioChunks: []
            };
        }
    }

    start() {
        this.logDebug('start');
        this.initialize();

        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                this.recognition.mediaRecorder = new MediaRecorder(stream);
                this.recognition.audioChunks = [];
                
                this.recognition.mediaRecorder.ondataavailable = event => {
                    this.recognition.audioChunks.push(event.data);
                };

                this.recognition.mediaRecorder.onstop = () => {
                    this.processAudioData();
                };

                this.recognition.mediaRecorder.start(100);
                this.isRecording = true;
                this.updateStatus('録音中...');
            });
    }

    stop() {
        this.logDebug('stop');
        this.isRecording = false;
        if (this.recognition && this.recognition.mediaRecorder) {
            this.recognition.mediaRecorder.stop();
        }
        this.updateStatus('停止済み');
        
    }

    clear() {
        this.logDebug('clear');
        this.transcriptText = '';
        this.updateTranscript();
        this.updateStatus('クリアしました');
    }

    processAudioData() {
        const audioBlob = new Blob(this.recognition.audioChunks, { type: 'audio/wav' });
        this.sendToGoogleAPI(audioBlob);
    }

    sendToGoogleAPI(audioBlob) {
        const reader = new FileReader();
        reader.onload = () => {
            const base64Audio = reader.result.split(',')[1];
            
            const requestData = {
                audio: {
                    content: base64Audio
                },
                config: {
                    encoding: 'LINEAR16',
                    sampleRateHertz: 16000,
                    languageCode: 'ja-JP'
                }
            };

            $.ajax({
                url: `${SPEECHTOTEXT_CONFIG.SPEECH_API_ENDPOINT}?key=${SPEECHTOTEXT_CONFIG.GOOGLE_API_KEY}`,
                method: 'POST',
                data: JSON.stringify(requestData),
                contentType: 'application/json',
                success: (response) => {
                    if (response.results) {
                        const transcript = response.results
                            .map(result => result.alternatives[0].transcript)
                            .join('\n');
                        this.transcriptText += transcript + '\n';
                        this.updateTranscript();
                    }
                },
                error: (xhr, status, error) => {
                    // this.updateStatus('APIエラー: ' + error);
                    //エラー対応のため記載詳細化
                    let errorMessage = 'APIエラー:\n';
    
                    // ステータスコードとステータステキストを追加
                    errorMessage += `Status: ${xhr.status} ${xhr.statusText}\n`;
                    
                    // レスポンスボディのエラー詳細を追加
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage += `Details: ${JSON.stringify(errorResponse, null, 2)}`;
                        console.log('Google API Error:', errorResponse);  // コンソールにも詳細を出力
                    } catch (e) {
                        errorMessage += `Raw Error: ${xhr.responseText}`;
                    }
                    
                    this.updateStatus(errorMessage);
                    this.logDebug('API Error', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });

                }
            });
        };

        reader.readAsDataURL(audioBlob);
    }

    updateStatus(message) {
        this.controls.$status.text(message);
        this.controls.$startButton.prop('disabled', message === '録音中...');
        this.controls.$stopButton.prop('disabled', message !== '録音中...');
    }

    updateTranscript(interimText = '') {
        const displayText = this.transcriptText + (interimText ? '(認識中: ' + interimText + ')' : '');
        this.controls.$transcriptArea.val(displayText);
        this.controls.$transcriptArea.scrollTop(this.controls.$transcriptArea[0].scrollHeight);
    }
}
