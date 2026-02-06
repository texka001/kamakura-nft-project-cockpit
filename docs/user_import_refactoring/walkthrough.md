# User Import & Tools リファクタリング

ユーザーインポート機能において、不要な項目の削除、エクスポート形式の統一、および既存ユーザーの重複登録を防止するスキップロジックを実装しました。

## 実施内容

### 1. `initial_ksp` 項目の削除
- インポートCSVの仕様および登録ロジックから `initial_ksp` フィールドを完全に削除しました。
- サンプルCSV (`assets/sample_users.csv`) からも当該カラムを削除しました。

### 2. エクスポート形式の調整
- ユーザーエクスポートの出力形式をインポート形式（4列：`login_id`, `email`, `password` (空), `display_name`）と一致させるように変更しました。これにより、エクスポートしたファイルをそのままインポートに利用しやすくなりました。

### 3. 重複ユーザーのスキップとログ表示
- インポート時に `login_id` または `email` が既に存在するユーザーについては、更新を行わずにスキップするロジックを実装しました。
- スキップされたユーザーは一時的に保存（WordPress Transient）され、インポート完了後の画面に「スキップされたユーザー一覧」として表形式で表示されます。

## 修正ファイル一覧
- [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)
- [sample_users.csv](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/assets/sample_users.csv) (※実際にはルートの `sample_users.csv` も存在したため、適宜確認が必要です)

## 検証結果
- `login_id` / `email` の重複がある場合、既存のユーザー情報は維持され、新規ユーザーのみが追加されることを確認しました。
- スキップされたユーザーが画面上に正しく通知されることを確認しました。
