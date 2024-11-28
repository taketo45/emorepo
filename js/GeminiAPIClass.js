import { GoogleGenerativeAI } from "https://esm.run/@google/generative-ai";

export class GeminiAPI {
    constructor(apiKey, isDebug = false) {
        this.genAI = new GoogleGenerativeAI(apiKey);
        this.isDebug = isDebug;
    }

    logDebug(methodName, params = null, result = null) {
        if (this.isDebug) {
            console.log(`[GeminiAPI] ${methodName}`);
            if (params) console.log('Parameters:', params);
            if (result) console.log('Result:', result);
        }
    }

    async generateResponse(prompt) {
        try {
            this.logDebug('generateResponse', { prompt });

            // モデルの取得とパラメータの設定
            const model = this.genAI.getGenerativeModel({
                model: "gemini-1.0-pro",
                generationConfig: {
                    temperature: 0.9,
                    topK: 1,
                    topP: 1,
                    maxOutputTokens: 2048,
                },
            });

            // プロンプトのエンコード
            const safePrompt = prompt;

            // 生成リクエストの実行
            const result = await model.generateContent(safePrompt);
            await result.response;
            const text = result.response.text();

            this.logDebug('generateResponse', null, text);
            return {
                success: true,
                text: text
            };
        } catch (error) {
            console.error('GeminiAPI Error:', {
                message: error.message,
                details: error.details,
                stack: error.stack,
                prompt: prompt  // デバッグ用にプロンプトも出力
            });
            return {
                success: false,
                error: `エラーが発生しました: ${error.message}`
            };
        }
    }
}