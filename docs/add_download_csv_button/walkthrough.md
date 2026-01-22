# リーグスケジュール履歴 CSVダウンロード機能 検証手順

## 変更内容
- `class-kmnft-user-manager.php` を修正し、リーグスケジュール履歴に CSV ダウンロード機能を追加しました。
    - 履歴テーブルに「Download」ボタンを追加。
    - `process_download_league_schedule_csv` メソッドを実装。

## 検証手順

### 1. 管理画面へのアクセス
1. WordPress管理画面にログインします。
2. 左側メニューから「KMNFT Dashboard」>「League Schedule」をクリックします。

### 2. CSVダウンロードの確認
1. 画面下部の「History」セクションを確認します。
2. 登録されている過去のシーズンの行に「Download」ボタンが表示されていることを確認します。
3. 任意のシーズンの「Download」ボタンをクリックします。
4. `league_schedule_{年}.csv` というファイル名でCSVファイルがダウンロードされることを確認します。

### 3. CSV内容の確認
1. ダウンロードしたCSVファイルを開きます。
2. 以下の項目が正しく出力されていることを確認します。
    - Header: `Section`, `Date(m/d)`, `Time`, `Score(H - A)`, `Opponent`, `Location`
    - データが正しく格納されていること（文字化けがないこと）。
