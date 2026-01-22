# 実装計画 - リーグ日程・結果表示機能

## ゴール
ダッシュボードの「LEAGUE STANDINGS」（順位表）の下に、「リーグ日程・結果」セクションを追加します。このセクションには、シーズンごとの試合日程と結果、および勝敗の集計（勝ち・負け・引き分け）を表示します。データは管理画面からCSVアップロードで管理できるようにし、既存の順位表（LEAGUE STANDINGS）と同様の仕組みを採用します。

## ユーザーレビューが必要な事項
> [!IMPORTANT]
> 新しいデータベーステーブル `wp_kmnft_league_schedule` を作成します。
> データ保存形式は、「LEAGUE STANDINGS」のアーキテクチャに合わせ、JSONカラムを使用してシーズンごとのスケジュールリストを保存する方式を採用します。

## 提案する変更内容

### データベース
#### [新規] `wp_kmnft_league_schedule` テーブル
- カラム構成:
    - `id` (INT, AI, PK)
    - `season_year` (VARCHAR(10)) - 例: "2025"
    - `data` (LONGTEXT) - 試合データのJSON配列
        - 構造: `[{ section: "1", date: "...", time: "...", score: "3 - 1", opponent: "..." }, ...]`
    - `summary_stats` (TEXT) - 集計データのJSONオブジェクト
        - 構造: `{ total: 19, win: 8, lose: 7, draw: 4 }`
    - `created_at` (DATETIME)

### バックエンド (管理画面)
#### [修正] `class-kmnft-user-manager.php`
- **メニュー追加**: KMNFT Console配下に「League Schedule」を追加。
- **描画関数**: `render_league_schedule_page()`
    - CSVアップロードフォームと対象年度（シーズン）の入力欄を表示。
    - アップロード済みのスケジュール履歴（年度別）を表示。
- **処理関数**: `process_league_schedule_save()`
    - CSVをパース。
    - **CSVフォーマット**: `節`, `日付`, `時間`, `スコア`, `対戦相手`
    - **ロジック**:
        - 各行を処理。
        - スコア（例: "3 - 1"）を解析して勝敗（Win/Loss/Draw）を判定。
        - 通算成績を集計。
        - データ行と集計結果をJSONにエンコード。
        - `season_year` をキーとして `wp_kmnft_league_schedule` に保存（INSERTまたはUPDATE）。

### フロントエンド
#### [修正] `page-dashboard.php`
- **配置**: 「LEAGUE STANDINGS」セクションの下。
- **ロジック**:
    - `wp_kmnft_league_schedule` からデータを `season_year` の降順で取得。
    - 年度ごとにループ処理（「年度ごとに表示」という要件に対応）。
        - ヘッダー: "Season [Year] Schedule & Results"
        - テーブル表示: 節 | 日付 | 時間 | スコア | 対戦相手
        - 集計表示: 試合数: X | 勝ち: Y | 負け: Z | 引き分け: W

## 検証計画

### 手動検証
1. **管理画面**:
    - 「League Schedule」メニューにアクセス。
    - ユーザーから提供されたサンプルCSV（2025年度用）をアップロード。
    - エラーなく保存されることを確認。
    - 自動計算された集計（8勝 7敗 4分）が正しいか確認。
2. **ダッシュボード**:
    - ダッシュボードにアクセスし、新しいセクションが表示されているか確認。
    - テーブルの内容がCSVと一致しているか確認。
    - 集計データが正しく表示されているか確認。
