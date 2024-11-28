class EmotionAnalyzer {
  constructor(isDebug = false) {
      this.isDebug = isDebug;
      this.emotionLabels = {
          anger: '怒り',
          sadness: '悲しみ',
          joy: '喜び',
          fear: '不安/恐れ'
      };
      this.config = setupGoogleCloud();
      this.logDebug('constructor');
      if(!this.config) console.log("Authkeys.jsが存在しないか、その中にsetupGoogleCloud()が存在しません");
  }

  logDebug(methodName, params = null, result = null) {
      if (this.isDebug) {
          console.log(`[EmotionAnalyzer] ${methodName}`);
          if (params) console.log('Parameters:', params);
          if (result) console.log('Result:', result);
      }
  }

  //Authkeys.js内で定義されている関数を呼び出し
  

  async analyzeText(inputText) {
      this.logDebug('analyzeText', inputText);

      const requestData = {
          document: {
              type: 'PLAIN_TEXT',
              content: inputText
          },
          encodingType: 'UTF8'
      };

      try {
          const response = await $.ajax({
              url: `${this.config.endpoint}?key=${this.config.apiKey}`,
              type: 'POST',
              data: JSON.stringify(requestData),
              contentType: 'application/json'
          });

          const emotionDict = this.analyzeEmotionWithDictionary(inputText);
          const sentiment = this.interpretSentiment(
              response.documentSentiment.score,
              response.documentSentiment.magnitude
          );

          const result = {
              cloudApi: {
                  score: response.documentSentiment.score,
                  magnitude: response.documentSentiment.magnitude
              },
              dictionary: emotionDict,
              interpretation: sentiment,
              text: inputText
          };

          this.logDebug('analyzeText', null, result);
          return result;

      } catch (error) {
          console.error('API Error:', {
              message: error.message,
              status: error.status,
              statusText: error.statusText,
              responseText: error.responseText
          });
          throw error;
      }
  }

  analyzeEmotionWithDictionary(text) {
      this.logDebug('analyzeEmotionWithDictionary', { text });

      let emotions = {};
      let totalWords = 0;

      Object.keys(emotionalWords).forEach(function(emotion) {
          const matches = emotionalWords[emotion].words.filter(function(word) {
              return text.includes(word);
          });
          emotions[emotion] = {
              count: matches.length,
              words: matches
          };
          totalWords += matches.length;
      });

      if (totalWords > 0) {
          Object.keys(emotions).forEach(function(emotion) {
              emotions[emotion].score = emotions[emotion].count / totalWords;
          });
      }

      this.logDebug('analyzeEmotionWithDictionary', null, emotions);
      return emotions;
  }

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

  generateApiResultHtml(cloudApi, position) {
      this.logDebug('generateApiResultHtml', { cloudApi, position });

      const html = `
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

      this.logDebug('generateApiResultHtml', null, { htmlLength: html.length });
      return html;
  }

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

      this.logDebug('generateDictionaryResultHtml', null, { htmlLength: html.length });
      return html;
  }

  generateEmotionBarHtml(emotion, percentage, data) {
      this.logDebug('generateEmotionBarHtml', { emotion, percentage, data });

      const html = `
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

      this.logDebug('generateEmotionBarHtml', null, { htmlLength: html.length });
      return html;
  }

  interpretSentiment(score, magnitude) {
      this.logDebug('interpretSentiment', { score, magnitude });

      const type = score > 0.25 ? '建設的/明確' : 
                  score < -0.25 ? '否定的/批判的' : '中立的';

      const strength = magnitude > 0.6 ? '強い' : 
                      magnitude > 0.3 ? '中程度' : '弱い';

      const result = {
          type: type,
          strength: strength,
          explanation: `このテキストは${strength}${type}な表現です。\n` +
                      `感情スコア(${score.toFixed(2)})は表現の肯定/否定を、\n` +
                      `強度(${magnitude.toFixed(2)})は感情の強さを示しています。`
      };

      this.logDebug('interpretSentiment', null, result);
      return result;
  }

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

      this.logDebug('generateInterpretationHtml', null, { htmlLength: html.length });
      return html;
  }
}