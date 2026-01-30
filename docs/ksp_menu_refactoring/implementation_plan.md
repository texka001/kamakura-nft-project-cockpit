# KSP管理機能のメニュー分割計画

現状の「Token KSP」画面にある「集計バッチ」と「集計結果エクスポート」の2つの機能を、別の新しい管理メニューとして独立させます。

## 推奨される変更

### [Component] class-kmnft-user-manager.php

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **`add_admin_menu`**:
  - 新しいサブメニュー `Aggregation` (スラッグ: `kmnft-token-ksp-aggregation`) を追加します。
  - 既存の `Token KSP` メニューの順序を適切に保ちます。

- **`render_ksp_aggregation_page` [NEW]**:
  - `render_token_ksp_page` から以下の機能を移動します。
    - **Aggregation Batch (集計バッチ)**
    - **Export Aggregated KSP (集計結果エクスポート)**

- **`render_token_ksp_page`**:
  - 移動したセクション（集計とエクスポート）を削除し、データインポートと管理機能に焦点を絞ります。
  - タイトルを「Token KSP Data Management」などに調整し、インポート/エクスポート/削除機能のみを残します。
  - **[NEW]** インポート後に「Aggregation」メニューでの集計が必要である旨の案内を追加します。

## 検証プラン

### 動作確認
1. 管理画面の KMNFT Console 配下に「Aggregation」メニューが追加されていることを確認。
2. 「Aggregation」画面で「Run Aggregation」および「Export User Summary/Token Summary」が正しく動作することを確認。
3. 元の「Token KSP」画面から集計セクションが消え、インポートと一括削除機能のみが残っていることを確認。
