# スケジュール表示の修正計画

昇順ソートに変更し、直近の試合結果（Last Result）と次の試合予定（Next Match）のみをデフォルトで表示し、それ以外を折り畳むように修正します。

## 提案される変更

### [Component] League Schedule Display (Dashboard Page)

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- **ソート順の変更**: `usort` によるソートを降順 (`$b - $a`) から昇順 (`$a - $b`) に変更します。
- **直近試合・次試合の特定**:
    - 現在時刻より前の最後の試合を「直近の試合結果」として特定。
    - 現在時刻より後の最初の試合を「次の試合」として特定。
- **表示ロジックの更新**:
    - 最新シーズンの表示において、「直近の試合結果」または「次の試合」に該当しない行を初期状態で `hidden` に設定。
- **JavaScript の更新**:
    - `toggleLatestSeason` 関数において、表示・非表示の切り替えロジックは維持。
    - ボタンのテキスト表示（"Show Full Schedule" / "Show Recent/Next Only" など）を実態に合わせて微調整。

## 確認計画

### 手動確認
- ブラウザでダッシュボードを表示し、以下の点を確認する：
    1. スケジュールが日付の昇順（古いものから新しいものへ）に並んでいること。
    2. 直近の試合（過去）が1つ表示されていること。
    3. 次の試合（未来）が1つ表示されていること。
    4. それ以外の試合が最初は表示されておらず、「Show Full Schedule」ボタンで表示されること。
    5. ボタンをクリックすると全ての試合が表示され、ボタンの文字が切り替わること。
