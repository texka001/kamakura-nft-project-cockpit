# User Import & Tools 修正タスク

- [x] `inc/class-kmnft-user-manager.php` の修正 (初期対応)
    - [x] `render_import_page`: CSVフォーマットの説明から `initial_ksp` を削除
    - [x] `process_csv_import`: `initial_ksp` の読み込みと登録ロジックを削除
    - [x] `process_user_export`: 出力項目を `login_id`, `email`, `display_name` に変更 (4列構成)
- [x] `assets/sample_users.csv` の修正
    - [x] `initial_ksp` カラムを削除
- [x] インポート時の重複スキップ & ログ出力機能
    - [x] `process_csv_import`: 既存ユーザーの更新をスキップするロジックの実装
    - [x] `process_csv_import`: スキップされたユーザー情報を保存（Transientなど）
    - [x] `render_import_page`: スキップされたユーザーのログを表示するUIの追加
- [x] 動作確認
    - [x] 既存ユーザーが更新されないことの確認
    - [x] スキップされたユーザーが画面に一覧表示されることの確認
