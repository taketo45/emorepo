import { setupGoogleCloud } from "../AuthkeysModule.js";


export async function analyzeAll(inputText) {
  const text = inputText;
  if (!text) {
    return "テキストを入力してください";
  }

  // Google Cloud Natural Language API分析
  const config = setupGoogleCloud();
  const requestData = {
      document: {
          type: 'PLAIN_TEXT',
          content: text
      },
      encodingType: 'UTF8'
  };

  const cloudResult = await $.ajax({
      url: `${config.endpoint}?key=${config.apiKey}`,
      type: 'POST',
      data: JSON.stringify(requestData),
      contentType: 'application/json'
  });
  const resultHtml = displayCombinedResults(text, cloudResult);
  return resultHtml;
  
}

function analyzeEmotionWithDictionary(text) {
  let emotions = {};
  let totalWords = 0;

  Object.keys(emotionalWords).forEach(emotion => {
      const matches = emotionalWords[emotion].words.filter(word => text.includes(word));
      emotions[emotion] = {
          count: matches.length,
          words: matches
      };
      totalWords += matches.length;
  });

  // 感情スコアの正規化（0-1の範囲に）
  if (totalWords > 0) {
      Object.keys(emotions).forEach(emotion => {
          emotions[emotion].score = emotions[emotion].count / totalWords;
      });
  }
  console.log("analyzeEmotionWithDictionary() : ");
  console.log(emotions);
  return emotions;
}

function interpretSentiment(score, magnitude) {
  let interpretation = {
      type: '',
      strength: '',
      explanation: ''
  };

  if (score > 0.25) {
      interpretation.type = '建設的/明確';
  } else if (score < -0.25) {
      interpretation.type = '否定的/批判的';
  } else {
      interpretation.type = '中立的';
  }

  if (magnitude > 0.6) {
      interpretation.strength = '強い';
  } else if (magnitude > 0.3) {
      interpretation.strength = '中程度';
  } else {
      interpretation.strength = '弱い';
  }

  interpretation.explanation = 
      `このテキストは${interpretation.strength}${interpretation.type}な表現です。\n` +
      `感情スコア(${score.toFixed(2)})は表現の肯定/否定を、\n` +
      `強度(${magnitude.toFixed(2)})は感情の強さを示しています。`;
      console.log(interpretation);
  return interpretation;
}

function displayCombinedResults(text, cloudResult) {
  console.log('Call displayCombinedResults()');
  const score = cloudResult.documentSentiment.score;
  const magnitude = cloudResult.documentSentiment.magnitude;
  const position = ((score + 1) / 2) * 100;
  
  const dictionaryResults = analyzeEmotionWithDictionary(text);
  const interpretation = interpretSentiment(score, magnitude);

  let resultHtml = '<div class="analysis-grid">';
  
  // Google Cloud API結果セクション
  resultHtml += `
      <div class="analysis-section">
          <h3>Google Natural Language API 分析結果</h3>
          <p>感情スコア: ${score.toFixed(3)}</p>
          <p>感情の強さ: ${magnitude.toFixed(3)}</p>
          <div class="sentiment-meter">
              <div class="sentiment-pointer" style="left: ${position}%;"></div>
          </div>
          <p style="font-size: 0.9em; color: #666;">
              ※スコアは-1.0(否定的)から+1.0(建設的)の範囲
          </p>
      </div>
  `;

  // 辞書ベースの感情分析セクション
  resultHtml += `
      <div class="analysis-section">
          <h3>感情辞書による分析結果</h3>
  `;

  Object.keys(emotionalWords).forEach(emotion => {
      const emotionData = dictionaryResults[emotion];
      const percentage = emotionData.count > 0 ? 
          (emotionData.score * 100).toFixed(1) + '%' : '0%';
      
      resultHtml += `
          <div>
              <div style="display: flex; align-items: center; margin-bottom: 5px;">
                  <span style="width: 80px;">${getEmotionLabel(emotion)}</span>
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
              ${emotionData.words.length > 0 ? 
                  `<p style="font-size: 0.9em; color: #666; margin: 0 0 10px 80px;">
                      検出: ${emotionData.words.join(', ')}
                  </p>` : ''}
          </div>
      `;
  });

  resultHtml += '</div>';
  
  // 解釈セクション
  resultHtml += `
      <div class="analysis-section" style="grid-column: 1 / -1;">
          <h3>総合解釈</h3>
          <p>${interpretation.explanation}</p>
          <p>この表現には以下の感情が含まれています：</p>
          <ul>
  `;

  Object.keys(dictionaryResults).forEach(emotion => {
      if (dictionaryResults[emotion].count > 0) {
          resultHtml += `
              <li>${getEmotionLabel(emotion)}: ${dictionaryResults[emotion].count}個の関連表現を検出</li>
          `;
      }
  });

  resultHtml += `
          </ul>
      </div>
  </div>
  `;
  // console.log(resultHtml);
  return resultHtml;
}

function getEmotionLabel(emotion) {
  const labels = {
      anger: '怒り',
      sadness: '悲しみ',
      joy: '喜び',
      fear: '不安/恐れ'
  };
  return labels[emotion] || emotion;
}
