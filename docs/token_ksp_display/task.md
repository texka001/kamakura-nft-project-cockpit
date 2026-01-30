# KSP表示ロジックの更新タスク

- [x] 現状の表示ロジックの調査
- [x] 実装計画の作成と承認
- [x] KMNFT_User_Manager クラスの拡張 (`get_user_ksp_summary`)
- [x] ダッシュボード表示の更新 (`page-dashboard.php`)
- [x] 修正内容の確認 (SQL予約語 `rank` の問題修正)
- [/] トークン（NFT）単位のKSPポイント・ランク表示
    - [ ] `KMNFT_User_Manager` に `get_tokens_ksp_summary` メソッドを追加
    - [ ] `page-dashboard.php` でトークンごとのサマリデータを取得
    - [ ] アセットカードのUIを更新してポイントとランクを表示
- [ ] 最終検証
