# User Import & Tools 修正計画

管理画面のユーザーインポートおよびエクスポート機能を修正し、`initial_ksp` の取り扱いを廃止するとともに、エクスポート項目をインポート形式に合わせます。

## Proposed Changes

### [Admin Console]

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- `render_import_page()`:
    - CSVフォーマットの説明テキストから `initial_ksp` を削除します。
- `process_csv_import()`:
    - CSVの5列目（`initial_ksp`）の取得を停止します。
    - `kmnft_ksp_ledger` テーブルへの初期KSP登録ロジックを削除します。
- `process_user_export()`:
    - ヘッダーを `login_id`, `email`, `display_name` に変更します。
    - 出力データから `rank`, `current_ksp` を削除し、インポート形式（パスワードなし）に合わせます。

#### [MODIFY] [sample_users.csv](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/assets/sample_users.csv)

- `initial_ksp` カラムを削除したサンプルデータに更新します。

## Verification Plan

### Automated Tests
- なし（UIおよびロジックの修正が主のため）

### Manual Verification
1. 管理画面の「User Import & Tools」を開き、説明文が修正されていることを確認。
2. 「Sample CSV Download」でダウンロードしたファイルに `initial_ksp` が含まれていないことを確認。
3. 「Download User CSV」でエクスポートされたファイルが `login_id`, `email`, `display_name` の3列構成であることを確認。
4. 実際に `login_id`, `email`, `password`, `display_name` の4列構成のCSVをインポートし、ユーザーが正しく作成/更新されることを確認。
