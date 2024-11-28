'use strict';

import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";


import { getFirestore, collection, doc, Timestamp, addDoc, setDoc, deleteDoc, onSnapshot, query, orderBy, getDoc, getDocs, where, and, or, collectionGroup, limit } 
from "https://www.gstatic.com/firebasejs/11.0.1/firebase-firestore.js";

import { getStorage, ref, uploadBytesResumable, getDownloadURL, listAll, deleteObject } from 'https://www.gstatic.com/firebasejs/11.0.1/firebase-storage.js';

// import { getDatabase, ref, push, set, remove, onChildAdded, onChildRemoved, onChildChanged, update } 
// from "https://www.gstatic.com/firebasejs/11.0.1/firebase-database.js";

import { getAuth, signInAnonymously, signInWithPopup, GoogleAuthProvider, signOut, onAuthStateChanged } 
  from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";

import { firebaseConfig } from "./AuthkeysModule.js";


const app = initializeApp(firebaseConfig);
// const db = getDatabase(app); // realtime DBを使用
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

// Firestore 初期化
const db = getFirestore(app);

// Cloud Storage 初期化
const storage =  getStorage(app);

const logout_url = "index.html"; 
provider.addScope('https://www.googleapis.com/auth/contacts.readonly');


function googleAuthLaterProcess(auth, provider,to_url){
  console.log("googleAuthLaterProcess(auth, provider,to_url)" + auth + provider + to_url);
  //Google認証完了後の処理
  signInWithPopup(auth, provider).then((result) => {
      //Login後のページ遷移
      location.href=to_url;  
  }).catch((error) => {
      // Handle Errors here.
      const errorCode = error.code;
      const errorMessage = error.message;
      // The email of the user's account used.
      const email = error.email;
      // The AuthCredential type that was used.
      const credential = GoogleAuthProvider.credentialFromError(error);
      // ...
  });
}

function logOut(auth){
  signOut(auth).then(() => {
      location.href = logout_url;
  }).catch((error) => {
      console.error('ログアウトエラー:', error);
      alert('ログアウトに失敗しました: ' + error.message);
  });
}


export {auth, provider, app, db, storage, googleAuthLaterProcess, logOut};