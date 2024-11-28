import { db } from './FirebaseInit.js';
import { collection, query, orderBy, limit, getDocs } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";
import { AuthService } from './authService.js';

// 表示関連のユーティリティを管理するモジュール
export const DisplayModule = {
    emotionChart: null,

    // 分析結果の表示
    async displayResults(response) {
        const $resultsContainer = $('#resultsContainer');
        $resultsContainer.empty();

        if (!response.responses[0].faceAnnotations) {
            $resultsContainer.html('<p>顔が検出されませんでした。</p>');
            return;
        }

        // 現在の分析結果を表示
        response.responses[0].faceAnnotations.forEach((face, index) => {
            const $faceDiv = $('<div>', { class: 'face-result' });
            
            $faceDiv.html(`
                <h3>顔 ${index + 1}</h3>
                <p>検出確率: ${(face.detectionConfidence * 100).toFixed(2)}%</p>
                <p>喜び: ${this.getEmotionLevel(face.joyLikelihood)}</p>
                <p>怒り: ${this.getEmotionLevel(face.angerLikelihood)}</p>
                <p>悲しみ: ${this.getEmotionLevel(face.sorrowLikelihood)}</p>
                <p>驚き: ${this.getEmotionLevel(face.surpriseLikelihood)}</p>
            `);
            
            $resultsContainer.append($faceDiv);
        });

        // チャートの更新
        await this.updateEmotionChart(response.responses[0].faceAnnotations[0]);

        // 履歴に追加
        this.addToHistory(response);
    },

    // 感情レベルの数値変換
    getEmotionScore(likelihood) {
        const scores = {
            'VERY_UNLIKELY': 0,
            'UNLIKELY': 25,
            'POSSIBLE': 50,
            'LIKELY': 75,
            'VERY_LIKELY': 100
        };
        return scores[likelihood] || 0;
    },

    // 感情レベルの文字変換
    getEmotionLevel(likelihood) {
        const levels = {
            'VERY_UNLIKELY': '非常に低い',
            'UNLIKELY': '低い',
            'POSSIBLE': '可能性あり',
            'LIKELY': '高い',
            'VERY_LIKELY': '非常に高い'
        };
        return levels[likelihood] || '不明';
    },

    // チャートの更新
    async updateEmotionChart(currentFace) {
        // 過去のデータを取得
        const historicalData = await this.getHistoricalEmotionData();
        
        // 現在のデータを準備
        const currentData = {
            joy: this.getEmotionScore(currentFace.joyLikelihood),
            anger: this.getEmotionScore(currentFace.angerLikelihood),
            sorrow: this.getEmotionScore(currentFace.sorrowLikelihood),
            surprise: this.getEmotionScore(currentFace.surpriseLikelihood)
        };

        // チャートの描画
        const ctx = document.getElementById('emotionChart').getContext('2d');
        
        if (this.emotionChart) {
            this.emotionChart.destroy();
        }

        this.emotionChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['喜び', '怒り', '悲しみ', '驚き'],
                datasets: [
                    {
                        label: '現在の感情',
                        data: [currentData.joy, currentData.anger, currentData.sorrow, currentData.surprise],
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)'
                    },
                    {
                        label: '過去の平均',
                        data: [
                            historicalData.joy,
                            historicalData.anger,
                            historicalData.sorrow,
                            historicalData.surprise
                        ],
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)'
                    }
                ]
            },
            options: {
                scales: {
                    r: {
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    },

    // 過去の感情データを取得
    async getHistoricalEmotionData() {
        try {
            const q = query(
                collection(db, 'analysisResults'),
                orderBy('timestamp', 'desc'),
                limit(5)
            );

            const querySnapshot = await getDocs(q);
            let totals = { joy: 0, anger: 0, sorrow: 0, surprise: 0 };
            let count = 0;

            querySnapshot.forEach(doc => {
                const data = doc.data();
                const face = data.result.responses[0].faceAnnotations[0];
                if (face) {
                    totals.joy += this.getEmotionScore(face.joyLikelihood);
                    totals.anger += this.getEmotionScore(face.angerLikelihood);
                    totals.sorrow += this.getEmotionScore(face.sorrowLikelihood);
                    totals.surprise += this.getEmotionScore(face.surpriseLikelihood);
                    count++;
                }
            });

            // 平均を計算
            if (count > 0) {
                Object.keys(totals).forEach(key => {
                    totals[key] = totals[key] / count;
                });
            }

            return totals;
        } catch (error) {
            console.error('Error getting historical data:', error);
            return { joy: 0, anger: 0, sorrow: 0, surprise: 0 };
        }
    },

    // 履歴への追加
    addToHistory(response) {
        const $historyContainer = $('#historyContainer');
        const $historyEntry = $('<div>', { class: 'history-entry' });
        
        const timestamp = new Date().toLocaleString();
        const facesDetected = response.responses[0].faceAnnotations?.length || 0;
        
        $historyEntry.html(`
            <p>${timestamp} - 検出された顔: ${facesDetected}</p>
        `);
        
        $historyContainer.prepend($historyEntry);
    },

    // プレビュー画像の更新
    updatePreview(imageData) {
        $('#imagePreview')
            .attr('src', imageData)
            .show();
        $('video').hide();
    },

    // プレビューのリセット
    resetPreview() {
        $('#imagePreview').hide();
        $('video').show();
    }

    
};
