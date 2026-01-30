# トークン単位のKSP表示実装の確認

ダッシュボードの「OWNED ASSETS」セクションにおいて、各NFT（トークン）ごとのKSPポイントとランクが表示されるようになりました。

## 修正内容

### 1. KMNFT_User_Manager クラスの拡張
- `get_tokens_ksp_summary($token_ids, $season)` メソッドを追加。
- `kmnft_ksp_token_summary` テーブルから、複数のトークンIDと特定シーズンのデータを一括で取得するようにしました。

### 2. ダッシュボード（page-dashboard.php）の更新
- **データ取得:** ページ読み込み時に、ユーザーが保有する全トークンの最新シーズンのKSPサマリデータを一括取得するようにしました。
- **UI表示:** 「OWNED ASSETS」の各カード内に、Token IDと並んでそのトークンの獲得KSPポイントとランクを表示するセクションを追加しました。
- **スタイル:** ポイントは白、ランクはゴールド（kmnft-gold）で表示し、視認性を高めています。

## テスト内容

- [x] `get_tokens_ksp_summary` メソッドが正しいトークンID群に対してデータを返していること。
- [x] ダッシュボードの各アセットカードに、個別のポイントとランクが表示されていること。
- [x] データがないトークンに対しては表示をスキップし、レイアウトが崩れないこと。

## 完了したタスク
- [x] 現状の表示ロジックの調査
- [x] 実装計画の作成と承認
- [x] KMNFT_User_Manager クラスの拡張 (`get_user_ksp_summary`)
- [x] ダッシュボード表示の更新 (`page-dashboard.php`)
- [x] 修正内容の確認 (SQL予約語 `rank` の問題修正)
- [x] トークン（NFT）単位のKSPポイント・ランク表示
    - [x] `KMNFT_User_Manager` に `get_tokens_ksp_summary` メソッドを追加
    - [x] `page-dashboard.php` でトークンごとのサマリデータを取得
    - [x] アセットカードのUIを更新してポイントとランクを表示
- [x] Deployment to Local WP
- [x] Git Commit
