# グラウンド画像のSVG化実装計画

グラウンドの白線が拡大時にぼやける問題を解決するため、現在のPNG画像 (`ground_map.png`) をSVG画像 (`whiteLine.svg`) に置き換えます。

## ユーザーへの確認事項
- 添付された `whiteLine.svg` のコード内容をこちらで読み取ることができませんでした（画像として表示されています）。恐れ入りますが、SVGのコード（`<svg ...>` で始まるテキスト）をチャットに貼り付けていただけますでしょうか？

## 変更内容

### テーマアセット
#### [NEW] [whiteLine.svg](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/assets/images/whiteLine.svg)
- 提供されたSVGデータを保存します。

### ダッシュボードページ
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- `assets/images/ground_map.png` を `assets/images/whiteLine.svg` に差し替えます。
- 画像の `opacity` や CSS クラスを調整し、背景色との兼ね合いを確認します。

## 検証計画
### 手動確認
- ブラウザでダッシュボードを表示し、`My Asset Map` と `LATEST MATCH RESULTS` のグラウンド画像が鮮明に表示されていることを確認します。
- 画面幅を変更しても線が細くなったり消えたりしないか確認します。
