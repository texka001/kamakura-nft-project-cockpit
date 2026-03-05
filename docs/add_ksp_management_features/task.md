# Token KSP管理機能の拡張

KSP（Kamakura Spark Points）データの管理機能を強化し、特定の日付による一括削除や、フィルター条件を指定したエクスポート機能を追加します。

## タスクリスト

- [x] 実装計画の作成と承認
- [x] Delete機能の拡張
    - [x] `Delete Token KSP` を `Delete Token KSP (Token ID)` に名称変更
    - [x] `Delete Token KSP (Date)` 機能の追加（日付指定での一括削除）
    - [x] 既存の `Delete Token KSP (By Token ID & Date)` のバグ修正
- [x] Export機能の拡張
    - [x] エクスポート時のフィルター（season, token_id, acquisition_date）追加
    - [x] フィルター条件に基づくSQLクエリの動的生成の実装
    - [x] 少なくとも1つのフィルター指定を必須化 [NEW]
- [ ] 動作確認と検証
    - [ ] 各削除機能のデータ整合性確認
    - [ ] フィルター条件を組み合わせたエクスポートの確認
    - [ ] フィルターなしでのエクスポート制限の確認 [NEW]
- [/] 完了報告（Walkthrough作成）
