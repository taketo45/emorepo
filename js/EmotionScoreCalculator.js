export class EmotionScoreCalculator {
    calculateScores(textAnalysisResult, imageAnalysisResult) {
        return {
            documentScore: this.calculateDocumentScore(textAnalysisResult),
            terminologyScore: this.calculateTerminologyScore(textAnalysisResult),
            emotionScore: this.calculateEmotionScore(imageAnalysisResult)
        };
    }

    calculateDocumentScore(textAnalysisResult) {
        try {
            if (!textAnalysisResult || typeof textAnalysisResult !== 'object') {
                console.warn('Invalid text analysis result');
                return 5;
            }

            // 感情スコアの計算（textAnalysisResultの構造に応じて調整）
            const positiveScore = (
                (textAnalysisResult.joy || 0) * 1.2 + 
                (textAnalysisResult.trust || 0) * 1.0 + 
                (textAnalysisResult.anticipation || 0) * 0.8
            ) / 3;

            const negativeScore = (
                (textAnalysisResult.anger || 0) * 1.2 + 
                (textAnalysisResult.disgust || 0) * 1.0 + 
                (textAnalysisResult.fear || 0) * 0.8
            ) / 3;

            // 10点満点に変更
            return Math.round((positiveScore - negativeScore + 1) * 5);
        } catch (e) {
            console.error('Document score calculation error:', e);
            return 5;
        }
    }

    calculateTerminologyScore(textAnalysisResult) {
        try {
            if (!textAnalysisResult || !textAnalysisResult.dictionary) {
                console.warn('Invalid text analysis result');
                return 5;
            }

            // 感情表現の使用頻度からスコアを計算
            let totalScore = 0;
            let categories = 0;
            
            for (const emotion in textAnalysisResult.dictionary) {
                const emotionData = textAnalysisResult.dictionary[emotion];
                if (emotionData.score !== undefined) {
                    totalScore += emotionData.score;
                    categories++;
                }
            }

            // 平均スコアを計算し、10点満点に変換
            const averageScore = categories > 0 ? totalScore / categories : 0.5;
            return Math.round(averageScore * 10);
        } catch (e) {
            console.error('Terminology score calculation error:', e);
            return 5;
        }
    }

    calculateEmotionScore(imageAnalysisResult) {
        try {
            const result = JSON.parse(imageAnalysisResult);
            // 表情の自然さをスコア化（10点満点）
            const baseScore = (result.happiness * 6 + 
                             result.neutral * 4 + 
                             result.surprise * 2) / 12 * 10;
            
            // 0-10の範囲に正規化
            return Math.round(Math.min(Math.max(baseScore, 0), 10));
        } catch (e) {
            console.error('Emotion score calculation error:', e);
            return 5;
        }
    }
} 