// カメラ関連の機能を管理するモジュール
export const CameraModule = {
    video: null,
    stream: null,

    // カメラの初期化
    async init() {
        try {
            this.video = $('video')[0];
            if (!this.video) {
                throw new Error('Video element not found');
            }

            // カメラの設定を最適化
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1920 },  // より高解像度に
                    height: { ideal: 1080 },
                    facingMode: 'user',      // フロントカメラを指定
                    // 以下のオプションはブラウザ/デバイスがサポートしている場合のみ有効
                    brightness: { ideal: 100 },
                    contrast: { ideal: 100 },
                    exposureMode: { ideal: 'continuous' },
                    focusMode: { ideal: 'continuous' }
                }
            });
            
            this.video.srcObject = this.stream;
        } catch (error) {
            console.error('カメラの初期化に失敗しました:', error);
            throw error;
        }
    },

    // // 写真撮影時の品質向上
    captureImage() {
        const $canvas = $('<canvas>');
        const context = $canvas[0].getContext('2d');
        
        // キャプチャ時の解像度を設定
        $canvas[0].width = this.video.videoWidth;
        $canvas[0].height = this.video.videoHeight;
        
        // ビデオフレームをキャンバスに描画
        context.drawImage(this.video, 0, 0, $canvas[0].width, $canvas[0].height);
        
        // JPEG形式で出力（品質は0.9に設定）
        return $canvas[0].toDataURL('image/jpeg', 0.9);
    },

    // カメラのリセット
    async reset() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
        await this.init();
    },

    // 写真撮影
    // captureImage() {
    //     const $canvas = $('<canvas>');
    //     const context = $canvas[0].getContext('2d');
        
    //     $canvas[0].width = this.video.videoWidth;
    //     $canvas[0].height = this.video.videoHeight;
    //     context.drawImage(this.video, 0, 0, $canvas[0].width, $canvas[0].height);
        
    //     return $canvas[0].toDataURL('image/png');
    // }
};
