




function googleAuthLaterProcess(auth, provider,to_url){
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


