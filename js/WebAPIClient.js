// API通信を担当するクラス
// PHPのPOST処理を想定し、method: 'POST''Content-Type': 'application/x-www-form-urlencoded'を設定
// 
export class WebApiClient {
  constructor(baseUrl, isDebug = false) {
      this.baseUrl = baseUrl;
      this.isDebug = isDebug;
  }

  logDebug(methodName, params = null, result = null) {
      if (this.isDebug) {
          console.log(`[ApiClient] ${methodName}`);
          if (params) console.log('Parameters:', params);
          if (result) console.log('Result:', result);
      }
  }

  async postFormData(endpoint, data) {
    const url = `${this.baseUrl}${endpoint}`;
    const formData = new URLSearchParams();
    
    for (const [key, value] of Object.entries(data)) {
        formData.append(key, value);
    }

    this.logDebug('postFormData', { url, data });

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    // 非同期のチェーンを維持するための実装（念の為）
    return response.text().then(responseText => {
        this.logDebug('postFormData:result', null, responseText);
        return JSON.parse(responseText);
    });
  }
}