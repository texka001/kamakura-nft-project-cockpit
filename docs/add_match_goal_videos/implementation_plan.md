# 実装計画: 試合のゴールごとの動画リンク追加

試合管理画面で、各ゴールに対応する動画URLを入力・保存できるようにします。

## ユーザーレビューが必要な項目
- **データベースの変更**: `kmnft_match_results` テーブルに `goal_videos` カラムを追加します。
- **UIのデザイン**: 複数の動画URLを縦に並べて入力できるテキストエリアを追加します（カンマ区切りまたは1行につき1URL形式）。

## 予定される変更

### データベースと管理機能
#### [MODIFY] [inc/class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)
- `ensure_match_table()` を更新し、`goal_videos` カラムを追加するSQLを含めます。
- `render_match_results_page()` を更新し、動画URL入力用のフィールド（テキストエリア）を追加します。
- `process_match_save()` を更新し、`goal_videos` フィールドの値をサニタイズして保存するようにします。

## 検証プラン

### 自動テスト
- 現時点では既存の自動テストスイートが見当たらないため、手動検証を優先します。

### 手動検証
1. 管理画面（KMNFT Console > Match Results）にアクセスします。
2. 「Add New Match」フォームに新しい「Goal Videos」フィールドが表示されていることを確認します。
3. 試合データを入力し、動画URLを複数行（またはカンマ区切り）で入力して保存します。
4. 保存後、データベースまたは編集画面で動画URLが正しく保持されていることを確認します。
5. 既存の試合の「編集」画面で、動画URLが正しく読み込まれることを確認します。
