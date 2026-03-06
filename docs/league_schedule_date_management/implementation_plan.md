# ACTION列の削除計画

ダッシュボードのリーグスケジュール表において、不要となった「ACTION」列（CSVダウンロードリンク）を削除し、テーブルの表示をスッキリさせます。

## Proposed Changes

### Dashboard

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- テーブルヘッダー（`<thead>`）から `<th class="py-2 px-2">Action</th>` を削除します。
- テーブルボディ（`<tbody>`）内の各行から、アクションリンクを含む `<td>` 要素を削除します。

## Verification Plan

### Manual Verification
- デプロイ後、ダッシュボードの「LEAGUE SCHEDULE / RESULTS」セクションを確認し、右端の「ACTION」列が消えていること。
- テーブル全体のレイアウトが崩れていないこと。
