# OGPタイトル修正の確認 (Walkthrough)

`page-dashboard.php` のタイトルタグ（`<title>`）を修正し、OGP等で表示されるタイトルを "Cockpit" から "Dashboard" に更新しました。

## 修正内容

### テーマファイル

#### [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- 111行目：`<title>Cockpit - Kamakura Stadium NFT PORTAL(β)</title>` を `<title>Dashboard - Kamakura Stadium NFT PORTAL(β)</title>` に変更しました。

## 検証結果

### 表示確認
- ブラウザでダッシュボードを開いた際、タブのタイトルが **Dashboard - Kamakura Stadium NFT PORTAL(β)** と表示されることを確認しました。
- 他のページ（Login, Points, Ranking等）は、それぞれ適切なタイトル（Login -, Points -, Ranking -）が既に設定されており、今回の修正に影響されないことを確認しました。
