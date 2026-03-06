# OGPタイトル修正計画

`page-dashboard.php` の `<title>` タグに含まれる "Cockpit" という文言を "Dashboard" に変更します。これにより、OGP（リンクプレビュー）などで表示されるタイトルが更新されます。

## ユーザーレビューが必要な項目

特になし。単純な文言置換です。

## 変更内容

### テーマファイル

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- 111行目の `<title>Cockpit - Kamakura Stadium NFT PORTAL(β)</title>` を `<title>Dashboard - Kamakura Stadium NFT PORTAL(β)</title>` に変更します。

## 検証計画

### 手動確認
- ブラウザでダッシュボードページを開き、ブラウザタブのタイトルが「Dashboard - Kamakura Stadium NFT PORTAL(β)」になっていることを確認します。
- 必要に応じて、OGP確認ツール（Facebook Sharing Debuggerなど）での表示確認をユーザーに依頼します（本環境では外部公開されていないため直接確認は困難）。
