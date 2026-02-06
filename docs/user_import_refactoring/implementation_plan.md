# インポート時の重複スキップ & ログ出力 実装計画

ユーザーインポート時に既存の `login_id`（ユーザー名）または `email` が存在する場合、その行の処理をスキップし、どのユーザーがスキップされたかを画面に表示するように変更します。

## Proposed Changes

### [Admin Console]

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- `process_csv_import()`:
    - `username_exists()` または `email_exists()` でユーザーが存在するか確認。
    - 存在する場合は、`wp_create_user`/`wp_update_user` を行わずにスキップリスト（配列）に追加。
    - 処理終了後、スキップリストが存在すれば `set_transient()` で一時保存（有効期限1分程度）。
    - Transient の ID をリダイレクト URL に含める。
- `render_import_page()`:
    - URL パラメータから Transient ID を取得。
    - スキップされたユーザーのリストを取得して、通知エリアに表形式で表示。

## Verification Plan

### Manual Verification
1. 既存のユーザー（IDまたはメールが重複）を含むCSVを作成してインポート。
2. 「○件のユーザーをスキップしました」というメッセージと、スキップされたユーザーの一覧（ID, Email, Display Name）が表示されることを確認。
3. スキップされたユーザーのデータ（パスワードや表示名）が更新されていないことを確認。
4. 新規ユーザーのみが正しく作成されることを確認。
