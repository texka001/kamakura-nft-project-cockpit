# パスワードリセットメールの文面変更計画

## ゴール
パスワードリセットのリクエスト時に送信されるメールの文面を、日本語と英語の併記形式に変更する。

## 変更内容

### KMNFT Login Template
- `wp-content/themes/kamakura-cockpit-theme/page-login.php`
    - パスワードリセット処理 (`kmnft_forgot_password`) 内のメール送信ロジックを変更する。
    - 件名を `[KAMAKURA STADIUM NFT PORTAL(β)] Password Reset Request / パスワードリセットのリクエスト` に変更。
    - 本文を日英併記に変更する。

**変更後の本文イメージ:**
```text
You requested a password reset.
パスワードリセットのリクエストを受け付けました。

Click the link below to reset your password (valid for 1 hour):
以下のリンクをクリックしてパスワードを再設定してください（1時間有効）:

[URL]

If you did not request this, please ignore this email.
お心当たりがない場合は、このメールを無視してください。
```

## 検証計画

### 手動検証
1.  ログアウト状態で `/login/?view=forgot` にアクセスする。
2.  登録済みの自分のメールアドレスを入力し、「SEND LINK」ボタンを押下する。
3.  受信したメールの件名と本文が、計画通りの日英併記になっていることを確認する。
