# NFT詳細モーダルの複数シーズン対応 実装計画

NFT詳細モーダルにおいて、過去のシーズンを含むKSPとランクを切り替えて表示できるようにします。

## Proposed Changes

### [Component Name] Backend (KMNFT_User_Manager)

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- `get_tokens_ksp_history($token_ids)` メソッドを追加し、指定されたトークンIDsの全シーズンの集計データを取得できるようにします。

### [Component Name] Dashboard Page

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- PHP: ページロード時に全トークンのシーズン履歴データを取得します。
- JS: 取得したデータを `const tokensHistory = {...}` としてJavaScriptグローバル変数にセットします。
- HTML: モーダル内の年度表示を静的テキストから `<select>` 要素に変更します。
- JS: `openTokenModal` 関数を更新し、履歴データに基づいてプルダウンの選択肢を生成・更新し、選択されたシーズンのポイントとランクを動的に反映させるロジックを追加します。

### [Component Name] Ranking Page

#### [MODIFY] [page-ranking.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-ranking.php)

- PHP: ランキング一覧に表示される全トークンのシーズン履歴データを `get_tokens_ksp_history` を使って取得します。
- HTML/CSS: モーダルのHTML構造とCSSクラスを `page-dashboard.php` と完全に一致するように更新します（画像セクション 3/5, 情報セクション 2/5 のレスポンシブレイアウト等）。
- JS: `tokensHistory` データの保持、`openTokenModal` の更新、および `updateModalSeasonData` 関数の実装を行い、Dashboardと同じ動的表示切り替えを実現します。

## Verification Plan

### Automated Tests
- なし（UIの挙動確認が主のため）

### Manual Verification
1. ダッシュボードでNFTをクリックしてモーダルを開く。
2. 「Season」のプルダウン内容を確認。
3. シーズンを切り替えた際、Total KSP と Season Rank が正しく更新されることを確認。
4. 複数学年（2025, 2026等）のデータがあるトークンで、各年ごとの数値が正しいことを確認。
