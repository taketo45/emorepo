export class EmotionScoreCalculator {
    calculateScores(textAnalysisResult, originalText) {
        return {
            documentScore: this.calculateDocumentScore(textAnalysisResult),
            terminologyScore: this.calculateTerminologyScore(originalText)
        };
    }

    calculateDocumentScore(textAnalysisResult) {
        try {
            if (!textAnalysisResult || typeof textAnalysisResult !== 'object') {
                console.warn('Invalid text analysis result');
                return 50;
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

            return Math.round((positiveScore - negativeScore + 1) * 50);
        } catch (e) {
            console.error('Document score calculation error:', e);
            return 50;
        }
    }

    calculateTerminologyScore(text) {
        try {
            // ビジネス用語のリスト（例）
            const businessTerms = [
                '報告', '連絡', '相談', '実施', '確認', '検討', '対応',
                'プロジェクト', 'ミーティング', '課題', '解決', '目標'
            ];
            
            let score = 50; // 基準点
            
            // ビジネス用語の使用頻度をチェック
            businessTerms.forEach(term => {
                if (text.includes(term)) score += 5;
            });
            
            // スコアの上限を100に制限
            return Math.min(score, 100);
        } catch (e) {
            console.error('Terminology score calculation error:', e);
            return 50; // エラー時はデフォルト値
        }
    }

    calculateEmotionScore(imageAnalysisResult) {
        try {
            const result = JSON.parse(imageAnalysisResult);
            // 表情の自然さをスコア化（例：笑顔の度合いを重視）
            const baseScore = result.happiness * 60 + 
                            result.neutral * 40 + 
                            result.surprise * 20;
            
            // 0-100の範囲に正規化
            return Math.round(Math.min(Math.max(baseScore, 0), 100));
        } catch (e) {
            console.error('Emotion score calculation error:', e);
            return 50; // エラー時はデフォルト値
        }
    }
} 