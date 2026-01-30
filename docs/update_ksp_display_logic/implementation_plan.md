# KSP表示ロジックの更新計画

ユーザーのポイント（KSP）を全体の合計値ではなく、最新年度のポイントとランクを集計テーブル（`kmnft_ksp_user_summary`）から取得して表示するように更新します。過去の年度の表示仕様は維持します。

## ユーザー確認事項

> [!IMPORTANT]
> ランク情報はこれまでダッシュボードに表示されていませんでしたが、今回の修正で「KSP Status」セクションに「RANK: XX」として追加表示します。表示形式について特別な希望がある場合はお知らせください。

## 変更内容

### 1. KMNFT_User_Manager クラスの拡張
[class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php) に、集計テーブルからユーザーのシーズンごとのサマリを取得する新しいメソッドを追加します。

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)
- `get_user_ksp_summary($user_id)` メソッドを新規追加。
- `kmnft_ksp_user_summary` テーブルから指定ユーザーの全シーズンのデータを取得（シーズン降順）。

### 2. ダッシュボードの表示更新
[page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php) で、新しいメソッドを使用して表示データを取得するように変更します。

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- `ksp_balance` の算出ロジックを更新。最新シーズンの `total_points` を使用。
- 最新シーズンの `rank` を取得・保持。
- KSPモジュールの表示を更新：
  - タイトルを「Total KSP Status」から「KSP Status」等に変更（要望に合わせて調整）。
  - ポイントとともにランクを表示。

## 修正後のイメージ

```text
KSP STATUS (2025 SEASON)
1,730 pt
RANK: 42
```

## 検証計画

### 1. データ確認
- 管理画面の「Aggregation」で集計を実行し、`kmnft_ksp_user_summary` にデータが存在することを確認。
- データベースで直接、特定ユーザーのポイントとランクを確認。

### 2. 表示確認
- ログインした状態でダッシュボードを表示。
- 表示されているポイントが、集計テーブルの最新年度の値と一致するか確認。
- ランクが正しく表示されているか確認。
- 「Past Seasons」を展開し、過去のデータも正しく表示されるか確認。
