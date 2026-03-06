# 日付管理の完全化（YYYY/MM/DD対応）の実装計画

スケジュールの「日付」を `MM/DD` から `YYYY/MM/DD` または `YYYY-MM-DD` 形式に変更し、年度をまたぐ管理や厳密な日付判定を可能にします。

## 提案される変更

### [Component] League Schedule Manager (Admin)

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **`render_league_schedule_page`**:
    - 説明文内の日付フォーマット指定を `Date(m/d)` から `Date(YYYY/MM/DD)` に更新します。
    - 例示を `4/6` から `2025/04/06` などに変更します。
- **`process_download_sample_league_schedule_csv`**:
    - ヘッダーを `Date(YYYY/MM/DD)` に変更します。
    - サンプルデータを `2025/04/06` などのフル形式に変更します。
- **`process_league_schedule_save`**:
    - 読み取られた `date` 値に対して、特に変換は行わず柔軟に保存します（保存形式は入力に依存させますが、推奨をフル形式とします）。

### [Component] Dashboard Page

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- **タイムスタンプ生成ロジックの更新**:
    - 入力された日付文字列を解析します。
    - `YYYY/MM/DD`, `YYYY-MM-DD`, `YYYY/M/D` のように年が含まれる形式を柔軟に処理します（`strtotime` を活用）。
    - 月日の2パート（`M/D` または `MM/DD`）のみの場合は、従来通り `season_year` で補完します。
- **表示の調整**:
    - テーブル上の表示は、データの形式を維持しつつ、必要に応じて見やすく整形します。

## 確認計画

### 手動確認
1.  **サンプルダウンロード**: 管理画面からサンプルCSVをダウンロードし、日付が `YYYY/MM/DD` 形式になっていることを確認する。
2.  **アップロード**: 新しい形式（`2026/01/01` など）のCSVをアップロードし、エラーなく保存されることを確認する。
3.  **ダッシュボード表示**:
    - 新しい形式でアップロードしたシーズンの試合が、正しいタイムスタンプでソートされ、直近・次の試合判定が正しく行われていることを確認する。
    - 既存の `MM/DD` 形式のデータも、引き続き正しく（`season_year` で補完されて）表示されることを確認する（後方互換性の維持）。
