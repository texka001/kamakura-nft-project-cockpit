# お問い合わせ通知へのCC設定追加 - 修正内容の確認

お問い合わせ通知メールにCCを追加できる機能を実装し、Local WP環境にデプロイしました。

## 変更内容

### 1. 管理画面設定の修正
- **ファイル:** [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)
- 既存の「通知先メールアドレス (Recipient Emails)」を「**TO通知先メールアドレス (TO Recipient Emails)**」に名称変更しました。
- 新規に「**CC通知先メールアドレス (CC Recipient Emails)**」の入力フィールドを追加しました。
- 保存処理にて `kmnft_contact_cc_recipients` オプションの保存・更新を実装しました。

### 2. メール送信ロジックの修正
- **ファイル:** [page-contact.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-contact.php)
- 管理者向けの通知メール送信時に、設定されたCCアドレスを `Cc:` ヘッダーとして付与するようにしました。
- コードの実装例（`page-contact.php` より抜粋）:
```php
// Get CC Recipients from Settings
$cc_recipients_option = get_option('kmnft_contact_cc_recipients', '');
if (!empty($cc_recipients_option)) {
    $cc = preg_split('/[\r\n,]+/', $cc_recipients_option);
    $cc = array_map('trim', $cc);
    $cc = array_filter($cc, 'is_email');
    if (!empty($cc)) {
        $headers[] = 'Cc: ' . implode(',', $cc);
    }
}
```

## 確認手順

1. 管理画面の **KMNFT Console > Settings** を表示します。
2. 「TO通知先メールアドレス」と「CC通知先メールアドレス」がそれぞれ表示されていることを確認します。
3. CC通知先（自分などのテストアドレス）を入力して「設定を保存」をクリックし、値が保持されるか確認します。
4. 表側のコンタクトフォームからテスト送信を行います。
5. TO宛とCC宛の双方にメールが届くことを確認してください。
