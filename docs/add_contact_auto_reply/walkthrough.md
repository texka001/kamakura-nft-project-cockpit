# 修正内容の確認 - お問い合わせ自動返信メール追加

お問い合わせフォームからメッセージを送信した際、送信者（ユーザー）に対して自動的に確認メールが送られる機能を実装しました。

## 変更内容

### [page-contact.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-contact.php)

- 管理者宛のメール送信が成功した直後に、入力されたメールアドレス宛に自動返信メールを送信する処理を追加しました。
- メール本文は、ユーザーの要望通り日本語と英語の両方で記載しています。
- 送信された「件名」と「メッセージ内容」を含めています。

```php
// 送信者への自動返信処理（抜粋）
$auto_reply_subject = '【KAMAKURA STADIUM NFT PORTAL】お問い合わせを受け付けました / Thank you for your inquiry';

$auto_reply_body = "<html><body>";
$auto_reply_body .= "<p>" . esc_html($name) . " 様</p>";
$auto_reply_body .= "<p>KAMAKURA STADIUM NFT PORTAL(β)へのお問い合わせありがとうございます。<br>";
$auto_reply_body .= "以下の内容でお問い合わせを受け付けました。返信まで今しばらくお待ちください。</p>";
// ... (英語メッセージ) ...
$auto_reply_body .= "<h3>[ お問い合わせ内容 / Inquiry Details ]</h3>";
$auto_reply_body .= "<p><strong>件名 / Subject:</strong> " . esc_html($subject) . "</p>";
$auto_reply_body .= "<p><strong>本文 / Message:</strong><br>" . nl2br(esc_html($message_content)) . "</p>";
// ...
wp_mail($email, $auto_reply_subject, $auto_reply_body, $auto_reply_headers);
```

## 検証結果

### コード確認
- `wp_mail` の引数が正しいこと（宛先：ユーザーのメール、送信元：システム管理メール）。
- 全ての変数が適切にエスケープ（`esc_html`, `nl2br`）されていること。
- 文字コード、コンテンツタイプ（HTML）が正しく設定されていること。

### 動作確認（推奨）
- 実際にフォームから送信テストを行い、入力したメールアドレスに期待通りの内容のメールが届くことをご確認ください。
