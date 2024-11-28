import { getAuth, signInWithPopup, GoogleAuthProvider, signOut, onAuthStateChanged } 
from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";
import { app } from './FirebaseInit.js';

export const AuthService = {
    auth: null,
    provider: null,
    currentUser: null,

    init(callback) { //コールバックを追加→カメラ初期化エラー対策
        this.auth = getAuth(app);
        this.provider = new GoogleAuthProvider();
        this.authStateCallback = callback;  // コールバックを保存
        
        // 認証状態の監視
        onAuthStateChanged(this.auth, async (user) => {
            this.currentUser = user;
            await this.updateUI(user);
            // コールバックがあれば実行
            if (this.authStateCallback) {
                this.authStateCallback(user);
            }
        });

        // イベントリスナーの設定
        this.setupEventListeners();
    },

    // setupEventListeners() {
    //     $('#loginButton').on('click', () => this.signIn());
    //     $('#logoutButton').on('click', () => this.signOut());
    // },

    async signIn() {
        try {
            const result = await signInWithPopup(this.auth, this.provider);
            this.currentUser = result.user;
        } catch (error) {
            console.error('ログインエラー:', error);
            alert('ログインに失敗しました: ' + error.message);
        }
    },

    async signOut() {
        try {
            await signOut(this.auth);
            this.currentUser = null;
            location.reload();
        } catch (error) {
            console.error('ログアウトエラー:', error);
            alert('ログアウトに失敗しました: ' + error.message);
        }
    },

    updateUI(user) {
        if (user) {
            $('#userName').text(`${user.displayName}さん`);
            $('#userInfo').show();
            $('#loginButton').hide();
            $('#results').show();
        } else {
            $('#userInfo').hide();
            $('#loginButton').show();
            $('#results').hide();
        }
    },

    getCurrentUser() {
        return this.currentUser;
    }
};
