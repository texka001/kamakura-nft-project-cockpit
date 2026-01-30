# 実装計画: 「アセットを追加購入」ボタンのレイアウト調整

「アセットを追加購入」ボタンを「OWNED ASSETS」タイトルの横（同じ高さ）に移動し、ダッシュボードの縦方向のスペースを節約します。

## 変更内容

### [Component Name] Dashboard Page

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- `OWNED ASSETS` セクションのヘッダー部分を修正し、ボタンを右側に配置します。
- セクション下部にあった元のボタンと説明テキストを削除します。
- ボタンのサイズを微調整し、ヘッダーに収まりやすくします。

## 確認事項

- [ ] ボタンが「OWNED ASSETS」のタイトルの右側に正しく表示されているか。
- [ ] ボタンクリック時に公式ストアへ遷移するか。
- [ ] モバイル表示時にレイアウトが崩れていないか。
