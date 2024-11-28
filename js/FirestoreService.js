import { getFirestore, collection, doc, Timestamp, addDoc, setDoc, updateDoc, arrayUnion, arrayRemove, deleteDoc, onSnapshot, query, orderBy, getDoc, getDocs, where, and, or, collectionGroup } 
from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";


import { app, db, storage } from './FirebaseInit.js';

export class FirestoreClass{
  docRef;
  constructor(collectionName,isDebug=false){
    this.isDebug = isDebug;
    this.collectionName = collectionName;
    this.docRef = collection(db, collectionName);
    if(this.isDebug){
      console.log(this.docRef);
    }
  }

  logDebug(methodName, params = null, result = null) {
      if (this.isDebug) {
          console.log(`[EmotionAnalyzer] ${methodName}`);
          if (params) console.log('Parameters:', params);
          if (result) console.log('Result:', result);
      }
  }

  //特定のコレクションの配下にあるドキュメントIDの一覧を取得する
  //引数はコレクション名のString
  //返り値は配列としてドキュメントIDを戻す
  async getDocumentIds(){
    // コレクションの参照を取得
    const collectionRef = collection(db, this.collectionName);
    const querySnapshot = await getDocs(collectionRef);
    
    // ドキュメントIDの配列を作成
    const documentIds = querySnapshot.docs.map(doc => doc.id);
    if(this.isDebug){
      console.log("documentIds: ");
      console.log(documentIds);
    }
    return documentIds;
  }

  async getDictionaryAll(){
    this.stateQuery = query(this.docRef);
    //クエリー実行
    const querySnapshot = await getDocs(this.stateQuery);
    const returnObj = this.getDocsObject(querySnapshot);
    // console.log(returnObj);
    return returnObj;
  }
  
  // クエリー結果をObjファイルとして取得する（感情分析で利用する形態）
  getDocsObject(query){
    let returnObj = {};
    // console.log(querySnapshot);
    query.forEach((doc) => {
      // console.log(doc.id, " => ", doc.data());
      returnObj[doc.id] = doc.data();
    }); 
    // console.log(returnObj);
    return returnObj;

  }


  async getOneDoc(documentId){
    if(this.isDebug){
      console.log("documentId: " + documentId);
    }

    const docId = doc(this.docRef, documentId);
    const docSnap = await getDoc(docId);
    if (docSnap.exists()) {
      console.log("Document data:", docSnap.data());
      return docSnap.data();
    }else{
      console.log("No such document!");
      return undefined;
    }
  }

  setDocToCollection(user) {
    setDoc(doc(this.docRef, user.uid), {
      name: user.displayName,
      color: user.email});
  }

  async getSelectMember (uid, ) {
    const query = query(this.docRef, where("role", "==", "member"), where("manager", "==", uid));
    return await getDocs(query);

  }


  async setDocToSubCollectionAll( reportinfo) {
      

    const collectionRef = collection(db, "emoreportusers",reportinfo.uid, "report");
    return await addDoc(collectionRef, reportinfo);
  }

  async updateDocumentField(documentId, fieldName, value) {
      if (this.isDebug) {
          console.log(' > updateDictionaryField()');
          console.log('documentId:', documentId);
          console.log('Field:', fieldName);
          console.log('Value:', value);
      }

      // 値が配列かStringかを判定
      let processedValue;
      if (Array.isArray(value)) {
          // 配列の場合は重複を除去してスプレッド構文で展開
          const uniqueValues = [...new Set(value)];
          processedValue = arrayUnion(...uniqueValues);
      } else if (typeof value === 'string') {
          // 文字列の場合はそのままarrayUnionに渡す
          processedValue = arrayUnion(value);
      } else {
          throw new Error('Valueが配列か文字列以外です');
      }

      // Firestoreの更新処理
      await updateDoc(doc(this.docRef, documentId), {
          [fieldName]: processedValue
      });
      return { success: true };
  }


  async removeDocumentField(documentId, fieldName, value){
      if (this.isDebug) {
          console.log(' > removeDocumentField()');
          console.log('documentId:', documentId);
          console.log('Field:', fieldName);
          console.log('Value:', value);
      }

      // 値が配列かStringかを判定
      let processedValue;
      if (Array.isArray(value)) {
          // 配列の場合は重複を除去してスプレッド構文で展開
          const uniqueValues = [...new Set(value)];
          processedValue = arrayRemove(...uniqueValues);
      } else if (typeof value === 'string') {
          // 文字列の場合はそのままarrayUnionに渡す
          processedValue = arrayRemove(value);
      } else {
          throw new Error('Valueが配列か文字列以外です');
      }

      await updateDoc(doc(this.docRef, documentId), {
          [fieldName]: processedValue
      });
      return { success: true };
  }

  //データ投入用コード（一回だけ実行）
  setDefaultDictionary() {
    if(this.isDebug){
      console.log(' > setDefaultDictionary()');
      console.log(this.docRef);
    }
      setDoc(doc(this.docRef, "anger"), {
          words: ['怒り', '許さない', '憎い', '激怒', '腹立たしい', '殺す', '死ね', 'ふざけるな', 'しね', 'にくい', 'ファック', 'ゆるさない', 'ゆるせない',
            'バカ', 'アホ', 'クソ', '害悪', '最低', '頭来る', '頭に来る', 'むかつ', 'ムカつ'],
          color: '#ff4444'});
      setDoc(doc(this.docRef, "sadness"), {
          words: ['悲しい', '辛い', '寂しい', '苦しい', '切ない', '落ち込む', '絶望', '虚しい',
            '後悔', '失望', 'つらい', '悔しい', '泣きたい', '心が痛い'],
          color: '#4444ff'});
      setDoc(doc(this.docRef, "joy"), {
          words: ['嬉しい', '楽しい', '幸せ', '最高', 'すばらしい', '素晴らしい', '良かった',
            '感謝', 'ありがとう', '大好き', '嬉し泣き', 'わくわく'],
          color: '#44ff44'});
      setDoc(doc(this.docRef, "fear"), {
          words: ['怖い', '不安', '心配', '恐ろしい', 'こわい', 'ドキドキ', 'ビクビク',
            '恐怖', 'やばい', 'まずい', '危険', 'こわばる'],
          color: '#ffff44'});
  }

}