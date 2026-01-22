# リーグ日程・結果機能 実装タスク

- [x] 既存の「LEAGUE STANDINGS」（順位表）の実装調査
- [x] リーグ日程用のデータベーススキーマ/保存形式の設計 <!-- id: 1 -->
- [ ] バックエンド管理機能の実装（管理画面UI & インポート） <!-- id: 2 -->
    - [ ] リーグ日程用のメニュー/ページを追加 <!-- id: 3 -->
    - [ ] CSVインポート機能の実装 <!-- id: 4 -->
    - [ ] 管理画面でのリスト表示実装 <!-- id: 5 -->
- [ ] フロントエンド表示機能の実装（ダッシュボード） <!-- id: 6 -->
    - [ ] 年度（シーズン）ごとのスケジュールデータ取得クエリ作成 <!-- id: 7 -->
    - [ ] スケジュールテーブルの描画 <!-- id: 8 -->
    - [ ] 集計（勝ち/負け/引き分け）の描画 <!-- id: 9 -->
- [x] Create `wp_kmnft_league_schedule` table
- [x] Add 'League Schedule' submenu to Admin
- [x] Implement Admin UI for CSV Upload & Management (`render_league_schedule_page`)
- [x] Implement CSV Parsing & Saving Logic (`process_league_schedule_save`)
- [x] Implement Frontend Display in `page-dashboard.php`->
- [ ] 実装の検証 <!-- id: 10 -->
