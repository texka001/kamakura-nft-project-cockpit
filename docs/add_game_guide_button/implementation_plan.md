# 実装計画: ゲーム説明ページへのリンクボタン追加

## 目的
コックピット（ダッシュボード）に、ゲームの説明ページ（SHOOT ZONE）へのリンクボタンを見やすい位置に追加する。

## 提案される変更

### [Theme]
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- 左カラムの「Project Logo Module」と「KSP Module」の間に、新しい「SHOOT ZONE GUIDE」モジュールを追加します。
- 視認性を高めるため、`glass-card` スタイルを使用し、アクセントカラー（kmnft-greenなど）を取り入れます。
- ボタンクリックで `https://kamakura-stadium-nft.com/shootzone/` に遷移（別タブ表示 `target="_blank"` 推奨）するようにします。

## 検証計画
### 手動検証
- コード上で配置位置（ロゴの下、KSPステータスの上）を確認。
- リンク先URLが正しいか確認。
- `target="_blank"` 属性が付与されているか確認。
