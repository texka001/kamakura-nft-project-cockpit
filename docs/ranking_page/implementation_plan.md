# ランキング画面実装計画

年度別のKSP（Kamakura Support Points）保有状況を可視化するため、トークン別およびユーザー別のランキング画面を新規に作成します。

## 変更内容

### [テーマ] kamakura-cockpit-theme

#### [NEW] [page-ranking.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-ranking.php)
- 新規ページテンプレート。
- `wp_kmnft_ksp_token_summary` テーブルからトークン別ランキング（Top 30）を取得。
- `wp_kmnft_ksp_user_summary` テーブルからユーザー別ランキング（Top 30）を取得。
- Tailwind CSS を使用して、2つのランキングをタブで切り替えられるUIを実装。
- 最新のシーズンデータをデフォルトで表示。

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- ヘッダーメニューに「RANKING」へのリンクを追加。
- リンク先は `/ranking`（または作成したページのパーマリンク）とする。

## 検証計画

### 自動テスト
- 特になし（既存のユニットテストフレームワークがないため）。

### 手動確認
1. **ページ遷移確認**: コクピット画面上部の「RANKING」リンクをクリックし、ランキングページへ遷移することを確認。
2. **ランキング表示確認**: 
    - トークンランキングタブに Top 30 のデータが表示されていること。
    - ユーザーランキングタブに Top 30 のデータが表示されていること。
3. **タブ切り替え確認**: タブをクリックして表示内容が正しく切り替わることを確認。
4. **デザイン確認**: コクピット画面のトンマナ（ダークモード、ネオングリーン）に合致していることを確認。
