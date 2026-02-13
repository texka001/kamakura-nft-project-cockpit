# 保有ポイント明細ページ実装計画

保有ポイント（KSP）の明細を閲覧できる新しいページを作成し、ヘッダーから簡単にアクセスできるようにします。

## Proposed Changes

### [Component] ポイント明細ページ

#### [NEW] [page-points.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-points.php)
- ログインユーザー専用のポイント明細表示ページ。
- 保有する全てのトークンのポイント獲得履歴を `kmnft_token_ksp` テーブルから取得。
- シーズンによるフィルタリング機能（`page-ranking.php` と同様の UI）。
- 表示項目：日付、トークンID（小画像付き）、獲得ポイント、理由1、理由2。
- **追加機能**:
  - 各カラムヘッダーをクリックして昇順・降順に並び替えができる機能（JavaScriptによるクライアントサイド実装）。
  - 各シーズンごとの集計値（合計KSP、ランキング）をページ上部に表示。
  - **NEW: ページ説明文の追加**: 「このページでは、あなたが現在保有しているNFTが獲得したポイントの明細を表示しています。」という説明文を追加。
- デザインは他のダッシュボードやランキングページと統一（ダークモード、ガラスモーフィズム）。

### [Component] ヘッダー・ナビゲーション

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
#### [MODIFY] [page-ranking.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-ranking.php)
#### [MODIFY] [page-contact.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-contact.php)
#### [MODIFY] [page-nft-gallery.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-nft-gallery.php)
#### [MODIFY] [page-login.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-login.php)
- ヘッダーのナビゲーションメニューに「POINTS」リンクを追加。
- ログイン時のみ「POINTS」リンクを表示するように制御。

---

- **Ranking ページの更新**:
  - **NEW: ページ説明文の追加**: 「ランキングは、全ユーザーおよび全NFTを対象とした集計結果に基づいています。」という説明文を追加。

### Automated Tests
- 自動テスト環境がないため、現在は実施しません。

### Manual Verification
1. ログイン後、ヘッダーに「POINTS」リンクが表示されていることを確認。
2. 「POINTS」をクリックして `page-points.php` （スラッグ: `points`）に遷移することを確認。
3. シーズンセレクターを切り替え、表示されるポイント明細が正しく更新されることを確認。
4. 表の各行に、正しいトークン画像、日付、ポイント、理由が表示されていることを確認。
5. 非ログイン状態で直接 `/points` にアクセスした場合にログインページへリダイレクトされることを確認。
