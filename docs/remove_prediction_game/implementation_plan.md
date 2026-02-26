# 実装計画：ゲーム予測セクションの削除

ダッシュボードから「PREDICTION GAME」セクションを削除します。ユーザーの要望に基づき、今回はこの機能を不要とします。

## 変更内容

### [Dashboard]

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- 1365行目から1372行目付近にある「PREDICTION GAME」を表示している `div` セクションを削除します。

> [!NOTE]
> `inc/class-kmnft-db-migration.php` 内に予測に関連するデータベースカラムの定義がありますが、将来的に機能を復旧させる可能性を考慮し、今回はデータベースの変更は行わず、UI のみの削除に留めます。

## 検証計画

### 手動確認
- ダッシュボードページをブラウザで表示し、「PREDICTION GAME」のカードが表示されていないことを確認します。
- 他のセクション（マッチ結果など）のレイアウトが崩れていないか確認します。
