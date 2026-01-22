# リーグスケジュールへの開催場所項目の追加

## 目標
リーグスケジュール機能に「開催場所（Location / Stadium）」フィールドを追加し、管理者が各試合の開催場所を指定できるようにし、フロントエンドに表示できるようにする。

## 変更内容

### バックエンド: `inc/class-kmnft-user-manager.php`
1.  **`process_download_sample_league_schedule_csv` の修正**:
    -   CSVヘッダーに `Location` を追加します。
    -   サンプル行にサンプルの開催場所データ（例：「鎌倉スタジアム」）を追加します。
2.  **`process_league_schedule_save` の修正**:
    -   CSVから `Location` フィールドを含めるようにバリデーション/解析ロジックを更新します。
    -   `wp_kmnft_league_schedule` テーブルの `data` JSONカラムに `Location` が保存されるようにします。
3.  **`render_league_schedule_page` の修正**:
    -   新しい「Location」カラムについて言及するように説明文を更新します。

### フロントエンド: `page-dashboard.php`
1.  **リーグスケジュールセクションの修正**:
    -   テーブルヘッダーに `<th>STADIUM</th>` を追加します。
    -   各試合の行に `location` フィールドを表示する `<td>` を追加します。
    -   スタイルが既存のカラムと一致することを確認します。

## 検証計画
1.  **サンプルCSVのダウンロード**:
    -   "Download Sample CSV" をクリックし、ファイルに "Location" カラムが含まれていることを確認します。
2.  **CSVのアップロード**:
    -   開催場所データを含むCSVをアップロードします。
    -   データが正しく保存されることを確認します。
3.  **フロントエンド表示**:
    -   「Dashboard」->「League Schedule」セクションを確認します。
    -   "STADIUM" カラムが存在し、正しいデータが表示されていることを確認します。
