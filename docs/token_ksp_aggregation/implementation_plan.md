# Token KSP Aggregation Implementation Plan

## Goal Description
Token KSP管理画面において、指定した年度（Season）の集計バッチを手動実行できる機能を追加します。
実行時には、以下の2つの観点で集計を行い、専用の集計テーブルに結果を保存（洗替更新）します。

1.  **年度 x トークン単位**: 各トークンがその年度に獲得したKSPの合計
2.  **年度 x ユーザー単位**: 各ユーザーが**現在保有している**トークンの、その年度の獲得KSP合計

## Background Context
現在、トークンごとのKSP獲得履歴（Token KSP）と、ユーザーのトークン保有状況（Holdings）は別々に管理されています。
ランキング表示やユーザーへの還元計算のために、これらを紐付けた集計データが必要となります。
リアルタイム集計ではなく、管理者が任意のタイミングで実行するバッチ処理として実装します。

## Proposed Changes

### Database Schema
`inc/class-kmnft-db-migration.php` に以下の2つのテーブル定義を追加します。

1.  **kmnft_ksp_token_summary** (トークン別集計)
    *   `id` (BIGINT, PK)
    *   `token_id` (VARCHAR)
    *   `season` (VARCHAR)
    *   `total_points` (INT)
    *   `updated_at` (DATETIME)
    *   Unique Key: `(token_id, season)`

2.  **kmnft_ksp_user_summary** (ユーザー別集計)
    *   `id` (BIGINT, PK)
    *   `user_id` (BIGINT)
    *   `season` (VARCHAR)
    *   `total_points` (INT)
    *   `updated_at` (DATETIME)
    *   Unique Key: `(user_id, season)`

### Backend Logic
`inc/class-kmnft-user-manager.php` を修正します。

1.  **UI追加**: `render_token_ksp_page` メソッド内に、「Aggregation Batch」セクションを追加します。
    *   年度入力フィールド（例: 2026）
    *   実行ボタン（`admin-post.php` へのPOST送信）
2.  **処理ロジック**: `process_token_ksp_aggregation` メソッドを実装します。
    *   **入力**: `season`
    *   **Step 1**: トークン別集計
        *   `DELETE FROM kmnft_ksp_token_summary WHERE season = %s`
        *   `INSERT INTO ... SELECT token_id, season, SUM(acquisition_point) ... FROM kmnft_token_ksp WHERE season = %s GROUP BY token_id`
    *   **Step 2**: ユーザー別集計
        *   `DELETE FROM kmnft_ksp_user_summary WHERE season = %s`
        *   `INSERT INTO ... SELECT h.user_id, %s as season, SUM(tk.acquisition_point) as total_points FROM kmnft_holdings h JOIN kmnft_token_ksp tk ON h.token_id = tk.token_id WHERE tk.season = %s GROUP BY h.user_id`
    *   **完了後**: 元の画面にリダイレクトし、完了メッセージを表示。

## Verification Plan

### Automated/Code Verification
*   新しく作成されたテーブルがデータベースに存在することを確認。

### Manual Verification
1.  **データ準備**:
    *   `kmnft_token_ksp` にテストデータを投入（例: Token A, 2026, 10pt / Token A, 2026, 20pt / Token B, 2026, 5pt）。
    *   `kmnft_holdings` にテストユーザーの保有情報を設定（例: User 1 owns Token A, User 2 owns Token B）。
2.  **バッチ実行**:
    *   管理画面「Token KSP」ページから、年度「2026」を指定して集計ボタンを押下。
3.  **結果確認**:
    *   `kmnft_ksp_token_summary`: Token A = 30pt, Token B = 5pt となっているか。
    *   `kmnft_ksp_user_summary`: User 1 = 30pt, User 2 = 5pt となっているか。
