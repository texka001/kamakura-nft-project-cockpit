# KSP表示ロジックの更新タスク

- [x] 現状の表示ロジックの調査
- [x] 実装計画の作成と承認
- [x] KMNFT_User_Manager クラスの拡張 (`get_user_ksp_summary`)
- [x] ダッシュボード表示の更新 (`page-dashboard.php`)
- [x] 修正内容の更新
- [x] トークン（NFT）単位のKSPポイント・ランク表示
    - [x] `KMNFT_User_Manager` に `get_tokens_ksp_summary` メソッドを追加
    - [x] `page-dashboard.php` でトークンごとのサマリデータを取得
    - [x] アセットカードのUIを更新してポイントとランクを表示
- [x] ダッシュボードのNFTクリックモーダルの詳細化
    - [x] トークン詳細表示用のモーダルHTML実装 (page-dashboard.php)
    - [x] モーダル表示制御用のJavaScript実装 (openTokenModal, closeTokenModal)
    - [x] 「保有資産」カードのクリックイベント更新
    - [x] 小マップ・大マップ上のプロットのクリックイベント更新
- [x] 最終検証
