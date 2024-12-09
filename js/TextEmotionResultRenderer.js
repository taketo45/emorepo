import { emotionalWords } from './emotionalDictionary.js';

export class TextEmotionResultRenderer {
    constructor(domElements, isDebug = false) {
        this.isDebug = isDebug;
        this.elements = domElements;
        this.emotionLabels = {
            anger: '怒り',
            sadness: '悲しみ',
            joy: '喜び',
            fear: '不安/恐れ'
        };
        this.logDebug('constructor');
    }

    logDebug(methodName, params = null, result = null) {
        if (this.isDebug) {
            console.log(`[EmotionRenderer] ${methodName}`);
            if (params) console.log('Parameters:', params);
            if (result) console.log('Result:', result);
        }
    }

    /**
     * 分析結果からHTML生成
     */
    generateResultHtml(result) {
        this.logDebug('generateResultHtml', { result });

        const position = ((result.cloudApi.score + 1) / 2) * 100;
        let html = '<div class="analysis-grid">';

        html += this.generateApiResultHtml(result.cloudApi, position);
        html += this.generateDictionaryResultHtml(result.dictionary);
        html += this.generateInterpretationHtml(result.interpretation, result.dictionary);

        html += '</div>';

        this.logDebug('generateResultHtml', null, { htmlLength: html.length });
        return html;
    }

    /**
     * API結果のHTML生成
     */
    generateApiResultHtml(cloudApi, position) {
        this.logDebug('generateApiResultHtml', { cloudApi, position });

        return `
            <div class="analysis-section">
                <h3>自然言語処理による感情分析結果</h3>
                <p>感情スコア: ${cloudApi.score.toFixed(3)}</p>
                <p>感情の強さ: ${cloudApi.magnitude.toFixed(3)}</p>
                <div class="sentiment-meter">
                    <div class="sentiment-pointer" style="left: ${position}%;"></div>
                </div>
                <p style="font-size: 0.9em; color: #666;">
                    ※スコアは-1.0(否定的)から+1.0(建設的)の範囲
                </p>
            </div>
        `;
    }

    /**
     * 感情辞書結果のHTML生成
     */
    generateDictionaryResultHtml(dictionary) {
        this.logDebug('generateDictionaryResultHtml', { dictionary });

        let html = `
            <div class="analysis-section">
                <h3>感情辞書による分析結果</h3>
        `;

        Object.keys(dictionary).forEach(emotion => {
            const data = dictionary[emotion];
            const percentage = data.count > 0 ? 
                (data.score * 100).toFixed(1) + '%' : '0%';
            
            html += this.generateEmotionBarHtml(emotion, percentage, data);
        });

        html += '</div>';
        return html;
    }

    /**
     * 感情バーのHTML生成
     */
    generateEmotionBarHtml(emotion, percentage, data) {
        this.logDebug('generateEmotionBarHtml', { emotion, percentage, data });

        return `
            <div>
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <span style="width: 80px;">${this.emotionLabels[emotion]}</span>
                    <div style="flex-grow: 1; position: relative;">
                        <div class="emotion-bar">
                            <div class="emotion-fill" style="
                                width: ${percentage};
                                background-color: ${emotionalWords[emotion].color};
                                opacity: 0.7;
                            "></div>
                            <span class="emotion-label">${percentage}</span>
                        </div>
                    </div>
                </div>
                ${data.words.length > 0 ? `
                    <p style="font-size: 0.9em; color: #666; margin: 0 0 10px 80px;">
                        検出: ${data.words.join(', ')}
                    </p>` : ''
                }
            </div>
        `;
    }

    /**
     * 解釈結果のHTML生成
     */
    generateInterpretationHtml(interpretation, dictionary) {
        this.logDebug('generateInterpretationHtml', { interpretation, dictionary });

        let html = `
            <div class="analysis-section" style="grid-column: 1 / -1;">
                <h3>総合解釈</h3>
                <p>${interpretation.explanation}</p>
                <p>この表現には以下の感情が含まれています：</p>
                <ul>
        `;

        Object.keys(dictionary).forEach(emotion => {
            if (dictionary[emotion].count > 0) {
                html += `
                    <li>${this.emotionLabels[emotion]}: 
                        ${dictionary[emotion].count}個の関連表現を検出</li>
                `;
            }
        });

        html += '</ul></div>';
        return html;
    }
}