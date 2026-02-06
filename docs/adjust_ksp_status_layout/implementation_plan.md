# KSP Status レイアウト調整

KSP Status エリアの表示を調整し、最新のシーズン情報をポイントの上に、順位をポイントと同じ高さに表示するように変更します。

## 修正内容

### Dashboard テンプレート
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- KSP Module 内のレイアウト構造を変更します。
- 以前はポイントの下にあったシーズン情報を、ポイントの上に移動します。
- ポイントと順位を `flex` を使用して同じ行に配置します。
- 順位の表示スタイルを調整し、ポイントとのバランスを整えます。

## 検証計画

### 手動確認
- ダッシュボードを表示し、以下の点を確認します：
    - シーズン名（例: 2025 SEASON）がポイント数値の上に表示されていること。
    - ポイント数値と順位（例: 1位）が同じ高さ（横並び）で表示されていること。
    - デザインが崩れていないこと。
