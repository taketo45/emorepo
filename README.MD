# ①課題名
動画日報アプリ

## ②課題内容（どんな作品か）
- ユーザー目線でみると面倒な日報を音声で作成できるアプリケーションです。
- 管理者目線でみると過度なストレスやメンタルダウンの予兆を、表情分析、文書感情分析により検知できるサービスです。

以下のAPIを利用しています。
Google Vision API、
Google firestore Database,
Google Wep Speech API,
Google NLP API,
Goolge Authentication

↓↓ライブラリを準備するも最終盤へ組み込みできず
Google Cloud storage, 
Google Cloud Functions,
Slack API,


## ③アプリのデプロイURL
 [Googleアカウントでログインしてください](https://transformnavi.sakura.ne.jp/JScompetition/)

## ④アプリのログイン用IDまたはPassword（ある場合）
- Googleアカウントによる認証。ログイン時に信頼されていないサイトに行くという項目を選択する。（ドメインの証明書がないため）

## ⑤工夫した点・こだわった点
- ワンクリックによって複数のAPIリクエストが実行されるような処理を実装した。
- ああ

## ⑥難しかった点・次回トライしたいこと（又は機能）
- 本当はVision APIによる静止画による表情分析ではなく、Video Inteligence APIを利用した動画による表情分析を取り入れたかった。しかし、分析に数分の時間がかかりその処理の終了を確認するポーリング処理の実装が完了しなかったため、泣く泣く実装を見送った。今回もっとも時間をかけた処理だったので残念。。
- 最初に作成したアプリケーションの全体構想としては、ストレス解消のウェルネス活動のレコメンドまで実装するつもりだったが、間に合わなかった。

## ⑦フリー項目（感想、シェアしたいこと等なんでも）
- [感想]
 ・とにかく2日くらいかけたVideo Inteligence APIの実装が終わらなかったことが悔しい。
 ・Webから直接ideo Inteligence APIを叩くこともできたっぽいが、結局数分の動画を録画するとタイム・アウトしてしまうような処理になり、実用てきではないので、であれば静止画のVisionAPIという判断をした。

 ・
- [参考記事]
  - 1. [Public APIs](https://github.com/public-apis/public-apis)
  - 2. 