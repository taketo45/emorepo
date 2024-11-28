


class VideoService{
  constructor(isDebug = false){
    this.isDebug = isDebug;
    this.video = $('video')[0];
    this.mediaRecorder = null;
    this.recordedChunks = [];
    this.stream = null;
    // this.firebaseConfig = firebaseConfig;
  }

  logDebug = function(methodName, params = null, result = null) {
      if (this.isDebug) {
          console.log(`[VideoAnalyzer] ${methodName}`);
          if (params) console.log('Parameters:', params);
          if (result) console.log('Result:', result);
      }
  };

  async initializeCamera() {
        this.logDebug('initializeCamera', 'Starting camera initialization');
        
        this.stream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                facingMode: 'user'
            }
        });
        this.video.srcObject = this.stream;
        this.logDebug('initializeCamera', null, 'Camera initialized');
    };

    startRecording() {
        this.logDebug('startRecording', 'Starting recording');
        
        this.recordedChunks = [];
        this.mediaRecorder = new MediaRecorder(this.stream, {
            mimeType: 'video/webm;codecs=vp9'
        });

        this.mediaRecorder.ondataavailable = function(event) {
            if (event.data.size > 0) {
                this.recordedChunks.push(event.data);
            }
        }.bind(this);

        this.mediaRecorder.start();
    };

      stopRecording = function() {
          this.logDebug('stopRecording', 'Stopping recording');
          
          return new Promise((resolve) => {
              this.mediaRecorder.onstop = () => {
                  const blob = new Blob(this.recordedChunks, {
                      type: 'video/webm'
                  });
                  resolve(blob);
              };
              this.mediaRecorder.stop();
          });
      };


}