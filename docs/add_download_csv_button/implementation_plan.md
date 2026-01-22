# リーグスケジュール履歴のCSVダウンロード機能実装

## Goal Description
管理画面の「リーグスケジュール管理」ページにて、過去のシーズンデータのCSVを再ダウンロード可能にする。
履歴テーブルの「Edit」「Delete」ボタンの横に「Download」ボタンを追加する。

## Proposed Changes

### `inc/class-kmnft-user-manager.php`

#### [MODIFY] `__construct`
- `admin_post_kmnft_download_league_schedule_csv` アクションフックを追加。

#### [MODIFY] `render_league_schedule_page`
- 履歴一覧テーブルの "Actions" カラムに "Download" ボタンを追加。
- ボタンのリンク先は `admin-post.php` へのリンクとし、`action=kmnft_download_league_schedule_csv` と `item_id` をパラメータに含める。

#### [NEW Method] `process_download_league_schedule_csv`
1. 権限チェック (`manage_options`)。
2. `item_id` の取得と検証。
3. データベース (`kmnft_league_schedule`) から該当データを取得。
4. JSONデータをデコード。
5. CSVヘッダーを出力 (`Section`, `Date(m/d)`, `Time`, `Score(H - A)`, `Opponent`, `Location`)。
6. データをループして `fputcsv` で出力。
7. ファイル名: `league_schedule_{season_year}.csv`

## Verification Plan

### Manual Verification
1. 管理画面 > LEAGUE SCHEDULE にアクセス。
2. Historyテーブルに「Download」ボタンが表示されていることを確認。
3. 「Download」ボタンをクリックし、CSVファイルがダウンロードされることを確認。
4. ダウンロードされたCSVファイルの内容が正しいか確認（文字化け、列のズレなど）。
