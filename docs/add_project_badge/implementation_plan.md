# ロゴ配置の修正計画

## 目標
プロジェクトロゴを「Total KSP Status」の内部ではなく、独立したエリア（カード）として、「Total KSP Status」の上に配置する。

## ユーザー要望への対応
- **エリアを分ける**: KSP Statusのカードとは別の `glass-card` を作成し、そこにロゴを配置する。
- **Total KSP Statusと同じサイズぐらい**: 新しいカードのサイズ感を、下のKSP Statusカードと同程度になるように調整する。
- **creative-logoを表示**: `uploaded_image_0_1767944041093.png` を使用する。

## 変更内容

### テーマファイル
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- **Revert**: 前回追加した `img` タグを削除。
- **New Element**: `<!-- KSP Module -->` の直前に、新しい `div.glass-card` を追加。
  - 内部に `assets/images/creative_logo.png` を配置。
  - `object-contain` などを使い、綺麗に収まるようにスタイリング。

### アセット
#### [NEW] [creative_logo.png](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/assets/images/creative_logo.png)
- 新しいロゴ画像を配置。

## 検証計画
- ダッシュボードの左カラム最上部にロゴだけのカードが表示されているか確認。
- その下に「Total KSP Status」カードが表示されているか確認。
- ロゴのカードサイズが適切か確認。
