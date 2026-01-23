# Task List: 全NFTギャラリーページ実装

- [ ] 新規機能の実装計画作成
- [ ] Ajaxハンドラーの拡張 (`inc/class-kmnft-dashboard-ajax.php`)
    - [ ] `kmnft_load_more_gallery` アクションの追加
    - [ ] ページネーションロジックの実装（`offset`, `limit` 対応）
    - [ ] `kmnft_get_remote_thumbnail` を使用したレスポンス生成
- [ ] 新規ページテンプレートの作成 (`page-nft-gallery.php`)
    - [ ] 既存のダッシュボードのデザインを踏襲したレイアウト
    - [ ] 「Load More」ボタンとJavaScriptの実装
- [ ] 固定ページの自動生成ロジック追加 (`functions.php`)
- [ ] ダッシュボード (`page-dashboard.php`) へのリンク追加
- [ ] 動作確認
    - [ ] ページ遷移確認
    - [ ] 「Load More」動作確認
    - [ ] キャッシュ画像が表示されるか確認
