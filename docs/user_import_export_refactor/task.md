# User Import & Tools 修正タスク

- [x] `inc/class-kmnft-user-manager.php` の修正
    - [x] `render_import_page`: CSVフォーマットの説明から `initial_ksp` を削除
    - [x] `process_csv_import`: `initial_ksp` の読み込みと登録ロジックを削除
    - [x] `process_user_export`: 出力項目を `login_id`, `email`, `display_name` に変更
- [x] `assets/sample_users.csv` の修正
    - [x] `initial_ksp` カラムを削除
- [x] 動作確認
    - [x] インポート画面の説明表示の確認
    - [x] サンプルCSVのダウンロードと中身の確認
    - [x] エクスポートCSVの内容の確認
