# Owned Assetsの表示改善の確認

「OWNED ASSETS」一覧のデザインを改善し、Token IDと座標を非表示にすることで、KSP（ポイント）とRANK（順位）がより際立つように調整しました。

## 修正内容

### [kamakura-cockpit-theme]

#### [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- **Token IDの非表示**: 一覧から削除しました。詳細モーダルでは引き続き確認可能です。
- **X,Y座標の非表示**: 一覧から削除しました。
- **KSP / RANKの強調**:
  - フォントサイズを大きくし、太字にすることで視認性を向上させました。
  - レイアウトを整理し、各カードの中央にステータスが綺麗に並ぶように調整しました。

render_diffs(file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

## 動作確認結果

### 修正後の表示
- 「OWNED ASSETS」セクションの各アセットにおいて、Token IDや座標のテキストが表示されなくなっています。
- 代わりに、KSP（例: 210pt）と順位（例: #1）が大きく分かりやすく表示されています。
