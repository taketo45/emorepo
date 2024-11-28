import { VISION_API_KEY } from './AuthkeysModule.js';
import { db } from './FirebaseInit.js';
import { getFirestore, collection, doc, Timestamp, addDoc, deleteDoc, onSnapshot, query, orderBy, getDoc ,getDocs } 
from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";   
import { AuthService } from './authService.js';

// 画像分析関連の機能を管理するモジュール
export const ImageAnalysisModule = {
    // 画像分析の実行
    async analyzeFaceAndEmotions(imageData) {
        try {
            let fileToAnalyze;
            
            if (imageData instanceof Blob) {
                fileToAnalyze = imageData;
            } else if (typeof imageData === 'string' && imageData.startsWith('data:')) {
                fileToAnalyze = this.base64ToBlob(imageData);
            } else {
                throw new Error('Unsupported file format');
            }
            
            return await this.analyzeImageFile(fileToAnalyze);
        } catch (error) {
            console.error('Error in face analysis:', error);
            throw error;
        }
    },

    // API リクエストの実行
    async analyzeImageFile(file) {
        const endpoint = `https://vision.googleapis.com/v1/images:annotate?key=${VISION_API_KEY}`;

        try {
            const base64Content = await this.fileToBase64(file);
            
            const response = await $.ajax({
                url: endpoint,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    requests: [{
                        image: {
                            content: base64Content
                        },
                        features: [
                            {
                                type: 'FACE_DETECTION',
                                maxResults: 10
                            }
                        ]
                    }]
                })
            });

            // Firestoreに結果を保存
            await this.saveResultToFirestore(response);

            return response;
        } catch (error) {
            console.error('Error in image analysis:', error);
            throw error;
        }
    },

    // Firestoreに結果を保存
    async saveResultToFirestore(result) {
        const currentUser = AuthService.getCurrentUser();
        if (!currentUser) {
            console.error('User not authenticated');
            return;
        }

        try {
            const timestamp = new Date();
            const userRef = collection(db, 'users', currentUser.uid, 'analysisResults');
            
            await addDoc(userRef, {
                result: result,
                timestamp: timestamp,
                facesDetected: result.responses[0].faceAnnotations?.length || 0,
                userName: currentUser.displayName,
                userEmail: currentUser.email
            });
        } catch (error) {
            console.error('Error saving to Firestore:', error);
        }
    },

    // ユーティリティ関数
    async fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                const base64Data = reader.result.split(',')[1];
                resolve(base64Data);
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    },

    base64ToBlob(base64) {
        const base64Content = base64.split(',')[1];
        const contentType = base64.split(',')[0].split(':')[1].split(';')[0];
        
        const byteCharacters = atob(base64Content);
        const byteArrays = [];
        
        for (let offset = 0; offset < byteCharacters.length; offset += 512) {
            const slice = byteCharacters.slice(offset, offset + 512);
            const byteNumbers = new Array(slice.length);
            
            for (let i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }
            
            const byteArray = new Uint8Array(byteNumbers);
            byteArrays.push(byteArray);
        }
        
        return new Blob(byteArrays, { type: contentType });
    }


};
