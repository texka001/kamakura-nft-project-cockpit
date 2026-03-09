# お問い合わせ通知へのCC設定追加

お問い合わせがあった際の管理者向け通知メールにおいて、現在の「TO（通知先）」に加えて「CC」を設定できるようにします。

## Proposed Changes

### [Component] WordPress Theme (KMNFT Console & Contact Page)

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- 管理画面の「Settings」ページ（お問い合わせフォーム設定）において：
    - 既存の「通知先メールアドレス (Recipient Emails)」を「**TO通知先メールアドレス (TO Recipient Emails)**」に名称変更します。
    - 新しく「**CC通知先メールアドレス (CC Recipient Emails)**」の入力フィールド（textarea）を追加します。
- 保存処理にて、新しいオプション `kmnft_contact_cc_recipients` を保存するように修正します。

#### [MODIFY] [page-contact.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-contact.php)

- 通知メール（管理者向け）の送信処理において、`kmnft_contact_cc_recipients` オプションからCCアドレスを取得します。
- 取得したアドレスを整形し、`wp_mail` のヘッダーに `Cc: ...` として追加します。
- メールのヘッダー構成を、TO通知先とCC通知先を両立させる構成に修正します。

## Verification Plan

### Manual Verification
1. 管理画面の KMNFT Console > Settings を開き、新しい「CC通知先メールアドレス」フィールドが表示されていることを確認します。
2. CC用のアドレスを入力して保存し、正しく保持されることを確認します。
3. 表側のお問い合わせフォームからテスト送信を行い、以下の点を確認します：
    - 「通知先」に届くこと。
    - 「CC通知先」にもメールが届くこと。
    - （CCが空の場合はエラーにならず、通常通り送信されること）

### Automated Verification (Logging check)
- 必要に応じて `wp_mail` に渡されるヘッダーの内容をログ出力し、`Cc: ...` が正しく構成されているかを確認します。
