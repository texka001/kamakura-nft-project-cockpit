# Task Checklist: Grouping Goal Images by Goal

- [x] 既存の実装の調査と実装計画の作成
    - [x] `class-kmnft-user-manager.php` 内の画像管理コードの特定
    - [x] `page-dashboard.php` での画像表示ロジックの確認
    - [x] 実装計画の作成
- [x] 管理画面の修正 (`class-kmnft-user-manager.php`)
    - [x] `goal_images` を `textarea` に変更
    - [x] メディアアップローダー JS の修正（textarea への追記ロジック）
    - [x] 保存時のデータ正規化ロジック追加
- [x] ダッシュボード表示の修正 (`page-dashboard.php`)
    - [x] 画像のパース処理（改行・カンマ）の更新
    - [x] ゴール番号バッジと動画リンクの紐付け修正
- [ ] 動作確認・検証
- [ ] デプロイと最終確認
