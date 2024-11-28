import {app, storage } from './FirebaseInit.js';
import { getStorage, ref, uploadBytes, uploadBytesResumable, getDownloadURL, listAll, deleteObject } from 'https://www.gstatic.com/firebasejs/11.0.1/firebase-storage.js';

export class FirebaseStorageService {
  constructor(firebaseConfig, isDebug = false) {
      this.isDebug = isDebug;
      this.app = app;
      this.storage = storage;
  }

  logDebug(methodName, params = null, result = null) {
      if (this.isDebug) {
          console.log(`[FirebaseStorage] ${methodName}`);
          if (params) console.log('Parameters:', params);
          if (result) console.log('Result:', result);
      }
  }

  async uploadFile(blob, folderPath, fileName = null) {
      this.logDebug('uploadFile', { folderPath, fileName });
      
      const actualFileName = fileName || `${Date.now()}.webm`;
      const fullPath = `${folderPath}/${actualFileName}`;
      const fileRef = ref(this.storage, fullPath);
      
      await uploadBytes(fileRef, blob);
      const downloadURL = await getDownloadURL(fileRef);
      
      this.logDebug('uploadFile', null, { downloadURL });
      return {
          url: downloadURL,
          path: fullPath,
          fileName: actualFileName
      };
  }
}