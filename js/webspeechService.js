
export class WebSpeechService {
    constructor(controls, isDebug = false) {
        this.recognition = null;
        this.isRecording = false;
        this.transcriptText = '';
        this.isDebug = isDebug;
        this.controls = controls;
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
            this.recognition = new webkitSpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.lang = 'ja-JP';

            this.recognition.onstart = () => {
                this.isRecording = true;
                this.updateStatus('録音中...');
            };

            this.recognition.onend = () => {
                if (this.isRecording) {
                    // まだ録音中なら再開
                    this.recognition.start();
                } else {
                    this.updateStatus('停止済み(init)');
                }
            };

            this.recognition.onresult = (event) => {
                this.logDebug('onresult', event);
                let interimTranscript = '';
                let finalTranscript = '';

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += transcript + '\n';
                    } else {
                        interimTranscript += transcript;
                    }
                }

                if (finalTranscript) {
                    this.transcriptText += finalTranscript;
                }
                this.updateTranscript(interimTranscript);
            };

            this.recognition.onerror = (event) => {
                this.logDebug('onerror', event);
                let errorMessage = 'APIエラー:\n';
                errorMessage += `Status: ${event.error}\n`;
                errorMessage += `Details: ${event.message || '詳細なし'}`;
                
                this.updateStatus(errorMessage);
                this.logDebug('API Error', {
                    error: event.error,
                    message: event.message
                });
            };
        }
    }

    start() {
        this.logDebug('start');
        this.initialize();
        this.recognition.start();
    }

    stop() {
        this.logDebug('stop');
        this.isRecording = false;
        if (this.recognition) {
            this.recognition.stop();
        }
        
        if(!this.transcriptText.length || this.transcriptText === ""){
            this.controls.$status.text('停止済み(stop):音声データが認識されませんでした。マイクを確認してください。');
        } else {
            this.updateStatus('停止済み(stop):');
        }
    }

    clear() {
        this.logDebug('clear');
        this.transcriptText = '';
        this.updateTranscript();
        this.updateStatus('クリアしました');
        this.controls.$transcriptArea.empty();
        this.controls.$reportArea.empty();
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
