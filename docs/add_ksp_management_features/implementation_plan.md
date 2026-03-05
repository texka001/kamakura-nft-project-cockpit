# KSP管理機能の拡張 実装計画

Token KSP管理画面において、削除機能の拡張と、エクスポート機能へのフィルター追加を行います。

## 変更内容の概要

1. **削除機能の拡充**
    - 「Delete Token KSP」を「Delete Token KSP (Token ID)」に改称。
    - 新機能「Delete Token KSP (Date)」を追加し、指定した日付のデータを一括削除可能にする。
    - 既存の「Delete Token KSP (By Token ID & Date)」内のバグを修正。

2. **エクスポート機能の高度化**
    - 「Export Token KSP」に `season`, `token_id`, `acquisition_date` のフィルターを追加。
    - **重要**: サーバー負荷軽減のため、**少なくとも1つのフィルター指定を必須**とします。すべて空の場合はエラーを表示してリダイレクトします。

## 影響範囲と修正ファイル

### [Admin Console]
管理画面のメニュー登録、フォーム表示、およびバックエンド処理を修正します。

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- `__construct()`: `admin_post_kmnft_delete_token_ksp_by_only_date` アクションを追加。
- `render_token_ksp_page()`:
    - 削除フォームのラベル変更と新フォーム（日付指定削除）の追加。
    - エクスポートフォームへの入力フィールド（season, token_id, acquisition_date）追加。
    - 少なくとも1つの指定が必要である旨の注記を追加。
- `process_token_ksp_export()`: 
    - パラメータがすべて空でないかチェックするバリデーションを追加。
    - SQLクエリを動的に構築してデータを絞り込むように変更。
- `process_token_ksp_delete_by_date()`: 行ループ内の `$token_id` が未定義である不具合を修正。
- `process_token_ksp_delete_by_only_date()`: [NEW] 指定された日付に合致するすべてのレコードを削除する処理を実装。

## 検証計画

### 自動テスト / 手動検証
- **削除機能**: 
    - トークンID指定で正しく削除されるか。
    - 日付指定でその日の全データが削除されるか（他の日のデータが残るか）。
    - ID+日付ペア指定での個別削除が正しく動作するか。
- **エクスポート機能**:
    - **フィルターなし（すべて空）で実行した場合、エラーメッセージが表示され、ダウンロードが開始されないこと。**
    - seasonのみ、token_idのみ、日付のみ、または組み合わせでの絞り込みが期待通りか。
    - 不正な日付形式を入力した場合の挙動確認。
