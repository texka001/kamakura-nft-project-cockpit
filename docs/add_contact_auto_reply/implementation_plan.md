# お問い合わせ自動返信メール機能の追加

お問い合わせフォームからメッセージが送信された際、送信者（ユーザー）に対しても自動返信メールを送るようにします。

## Proposed Changes

### [Component] WordPress Theme (KMNFT Contact Page)

#### [MODIFY] [page-contact.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-contact.php)

- お問い合わせ内容が正常に送信された後、送信者に対しても `wp_mail` を実行する処理を追加します。
- メールの件名は日本語と英語を併記します： `【KAMAKURA STADIUM NFT PORTAL】お問い合わせを受け付けました / Thank you for your inquiry`
- メールの本文は以下の内容を含み、日本語と英語で併記します：
    - 送信者名への挨拶
    - お問い合わせを受け付けた旨の通知
    - 送信された内容（件名、本文）のコピー
    - 署名（プロジェクト名）

## Verification Plan

### Manual Verification
- お問い合わせフォームからテストメッセージを送信し、以下の点を確認します（開発環境のメールログまたは実際に届くメールにて）：
    - 管理者にこれまで通りメールが届くこと。
    - 送信者（入力したメールアドレス）に対して自動返信メールが送信されること。
    - 自動返信メールの内容が指定通り（日・英併記、内容コピー含む）であること。

### Automated Verification (Logging check)
- 一時的に `wp_mail` の内容をエラーログに出力するコードを挿入し、送信処理が正しく呼び出されているか、引数（宛先、件名、本文）が正しいかを確認します。
