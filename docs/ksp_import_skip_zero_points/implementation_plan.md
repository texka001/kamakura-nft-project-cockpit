# KSPインポート 0ポイントスキップ機能 実装計画

バッチインポートにおいて、ポイントが0、NULL、または空のデータをスキップし、その件数をユーザーに通知するように変更します。

## 変更内容の概要

1. **インポート処理の強化 (`process_token_ksp_import`)**
   - CSVの各行の `acquisition_point` をチェック。
   - 0、NULL、または空の場合は `INSERT` をスキップし、スキップ件数をカウント。
   - 完了後のリダイレクトURLにスキップ件数 (`skipped`) を含める。

2. **通知メッセージの更新 (`render_token_ksp_page`)**
   - 成功メッセージ内にスキップ件合計を表示するロジックを追加。

## 影響範囲と修正ファイル

### [Admin Console]

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- `process_token_ksp_import()`: 
  - `acquisition_point` が有効（1以上、あるいは数値として存在）かチェック。
  - スキップ数をカウント用の変数 `$skip_count` を導入。
  - リダイレクト時に `&skipped=$skip_count` を付与。
- `render_token_ksp_page()`:
  - `status=success` の時に `skipped` パラメータがあれば、「XX件スキップされました（ポイントなし）」等のメッセージを追記。

## 検証計画

### 手動検証
1. **全件有効なCSV**: 全件インポートされ、スキップ数が0と表示されること。
2. **一部0/空ポイントを含むCSV**: 有効なデータのみインポートされ、スキップ件数が正しく表示されること。
3. **全件0ポイントのCSV**: インポート件数0、スキップ全件と表示されること。
4. **NULL/空文字のポイント**: 0と同様にスキップされること。
