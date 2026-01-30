# Token KSP Aggregation Walkthrough

## Overview
Token KSP管理画面に、指定年度（Season）のKSP集計バッチ実行機能を追加しました。
この機能により、現在の保有トークンに基づいたユーザーごとの獲得KSP合計や、トークンごとの獲得KSP合計を算出し、専用の集計テーブルに保存します。

## Changes

### 1. Database Schema
以下の2つの新しいテーブルを追加しました。データは「洗替（Delete & Insert）」方式で更新されます。

*   **`kmnft_ksp_token_summary`**:
    *   年度ごとのトークン単体の獲得ポイント合計。
    *   Columns: `token_id`, `season`, `total_points`
*   **`kmnft_ksp_user_summary`**:
    *   年度ごとのユーザー単位の獲得ポイント合計（**現在の**保有トークンに基づく）。
    *   Columns: `user_id`, `season`, `total_points`

### 2. Admin UI (Token KSP Management)
管理画面に「Aggregation Batch」セクションを追加しました。
*   **Season Input**: 集計対象の年度（例: 2026）を入力。
*   **Run Aggregation Button**: 処理を実行します。
*   **Export Aggregated KSP**: 集計後のデータをCSVでダウンロードします。
    *   Token Summary / User Summary それぞれのボタンを用意。
    *   年度（Season）を指定可能。

### 3. Backend Logic
`inc/class-kmnft-user-manager.php` に集計ロジックを実装しました。

**Logic Details:**
1.  **Token Aggregation**: `kmnft_token_ksp` テーブルから、指定 `season` のレコードを集計し、`token_id` ごとに `acquisition_point` を合計して `kmnft_ksp_token_summary` に保存。
2.  **User Aggregation**: `kmnft_holdings`（現在の保有状況）と `kmnft_token_ksp`（各トークンのポイント）を `token_id` で結合。指定 `season` のポイントを `user_id` ごとに合計して `kmnft_ksp_user_summary` に保存。

### 4. CSV Export
*   `kmnft_ksp_token_summary` および `kmnft_ksp_user_summary` テーブルから、指定年度のデータをCSVとしてエクスポートする機能を追加しました。
*   User Summaryのエクスポートには、利便性のため `user_login` と `display_name` を `wp_users` テーブルから結合して出力します。

## Verification Rules

### Manual Verification Steps
1.  Wordpress管理画面 > **KMNFT Console** > **Token KSP** にアクセス。
2.  新しい「Aggregation Batch」セクションが表示されていることを確認。
3.  Token KSPデータが存在する年度（例: `2026`）を入力し、「Run Aggregation」をクリック。
4.  完了メッセージ（"Aggregation for Season 2026 completed..."）が表示されることを確認。
5.  （可能であれば）データベースを確認し、`wp_kmnft_ksp_token_summary` および `wp_kmnft_ksp_user_summary` に期待通りのデータが格納されているか確認。
6.  **CSV Export確認**:
    *   「Export Aggregated KSP」セクションで年度を入力。
    *   「Export Token Summary」をクリック → CSVがダウンロードされるか確認。
    *   「Export User Summary」をクリック → CSVがダウンロードされるか確認。
