import {FUNCTIONS_VIDEO_CONFIG} from './AuthkeysModule.js';


export class VideoTransferService {
  constructor(isDebug = false) {
      this.endpoint = FUNCTIONS_VIDEO_CONFIG.API_ENDPOINT;
      this.statusEndpoint = FUNCTIONS_VIDEO_CONFIG.STATUS_ENDPOINT; 
      this.isDebug = isDebug;
      this.apiKey = FUNCTIONS_VIDEO_CONFIG.API_KEY;  //AuthkeysModuleに未登録！！
      this.statusCheckInterval = 3000; // ミリ秒
      this.maxRetries = 20; // 最大リトライ回数
      this.clientId = FUNCTIONS_VIDEO_CONFIG.CLIENTID; //AuthkeysModuleに未登録！！
      this.initializeGoogleAuth(this.apiKey, this.clientId);
  }

  logDebug(methodName, params = null, result = null) {
      if (this.isDebug) {
          console.log(`[VideoAnalyzer] ${methodName}`);
          if (params) console.log('Parameters:', params);
          if (result) console.log('Result:', result);
      }
  }
//コメント
  async initializeGoogleAuth(apiKey, clientId) {
      await new Promise((resolve) => gapi.load('client', resolve));
      await gapi.client.init({
          apiKey: apiKey,
          clientId: clientId,
          scope: 'https://www.googleapis.com/auth/cloud-platform'
      });

      this.tokenClient = google.accounts.oauth2.initTokenClient({
          client_id: clientId,
          scope: 'https://www.googleapis.com/auth/cloud-platform',
          callback: (response) => {
              this.accessToken = response.access_token;
          }
      });
  }

  async analyzeVideo(videoUrl, options = {}, $output) {
      this.logDebug('analyzeVideo', { videoUrl, options });
      try {
          // 分析開始
          const operation = await this.startAnalysis(videoUrl);
          
          // 進捗表示用
          this.updateStatus('分析中...', $output);
          
          // 結果取得（タイムアウト付き）
          const result = await this.waitForResult(operation.name);
          
          return result;
      } catch (error) {
          console.error('Analysis failed:', error);
          throw error;
      }


    //   const request = new Request(this.endpoint, {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //     },
    //     body: JSON.stringify({
    //         videoUrl: videoUrl,
    //         ...options
    //     })
    // });

    // this.logDebug('Request', request);
      
    //   try {
    //     const response = await fetch(request);
    //     this.logDebug('fetch', null, response);
    //     if (!response.ok) {
    //         throw new Error(`HTTP error! status: ${response.status}`);
    //     }
        
    //     const result = await response.json();
    //     this.logDebug('analyzeVideo result', null, result);
    //     return result;

    //   } catch (error) {
    //     console.error('Video analysis error:', error);
    //     throw error;
    //   }

  }

  formatResults(analysisData) {
      this.logDebug('formatResults', { analysisData });
      
      const formattedResults = {
          faces: [],
          timestamp: new Date().toISOString()
      };

      if (analysisData.faceAnnotations) {
          formattedResults.faces = analysisData.faceAnnotations.map(face => ({
              joy: this.calculateScore(face.joyLikelihood),
              sorrow: this.calculateScore(face.sorrowLikelihood),
              anger: this.calculateScore(face.angerLikelihood),
              surprise: this.calculateScore(face.surpriseLikelihood)
          }));
      }

      this.logDebug('formatResults', null, formattedResults);
      return formattedResults;
  }

  calculateScore(likelihood) {
      const scores = {
          'VERY_UNLIKELY': 0,
          'UNLIKELY': 25,
          'POSSIBLE': 50,
          'LIKELY': 75,
          'VERY_LIKELY': 100
      };
      return scores[likelihood] || 0;
  }

    async checkAnalysisStatus(operationName) {
      this.logDebug('checkAnalysisStatus', { operationName });

      try {
          const response = await fetch(this.statusEndpoint, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
              },
              body: JSON.stringify({ operationName })
          });

          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }

          const result = await response.json();
          this.logDebug('checkAnalysisStatus', null, result);
          return result;
      } catch (error) {
          console.error('Status check error:', error);
          throw error;
      }
  }

  async waitForAnalysis(operationName, interval = 3000) {
      this.logDebug('waitForAnalysis', { operationName, interval });
      
      while (true) {
          const status = await this.checkAnalysisStatus(operationName);
          if (status.done) {
              return status.result;
          }
          await new Promise(resolve => setTimeout(resolve, interval));
      }
  }



  //テスト用の記載


    async startAnalysis(videoUrl) {
      const response = await fetch('https://videointelligence.googleapis.com/v1/videos:annotate', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'Authorization': 'Bearer ' + this.apiKey
          },
          body: JSON.stringify({
              inputUri: videoUrl,
              features: ['FACE_DETECTION']
          })
      });
      
      if (!response.ok) throw new Error('Analysis request failed');
      return await response.json();
  }

  async waitForResult(operationName) {
      let retries = 0;
      
      while (retries < this.maxRetries) {
          const status = await this.checkStatus(operationName);
          
          if (status.done) return status.result;
          
          await new Promise(resolve => setTimeout(resolve, this.statusCheckInterval));
          retries++;
      }
      
      throw new Error('Analysis timed out');
  }

  updateStatus(message, $output) {
      // UI更新用の処理
      $output.textContent = message;
  }


}
