# Walkthrough - KSP Menu Refactoring & UI Improvements

## 変更内容
- **メニュー構造の整理**: 「Token KSP」メニューを2つの独立したページに分割しました。
  1.  **Token KSP**: データ管理（CSVインポート、個別エクスポート、データ削除）に特化。
  2.  **Aggregation**: 集計バッチ実行と、集計済みサマリ（ユーザー単位・トークン単位）のエクスポートを担当。
- **リダイレクト処理の更新**: 集計処理やエクスポート実行後のリダイレクト先を、新しい「Aggregation」ページに変更しました。
- **UI改善 (案内文の追加)**: **Token KSP** 画面のインポートセクションに、「インポート後にはランキング更新のために集計作業が必要である」旨の案内を追加しました。

## UI更新箇所 (案内文)
インポートセクションの直下に以下のメッセージを追加しました。
> **案内:** インポートしたデータをランキングやサマリに反映させるには、別メニューの「Aggregation」にて集計（Run Aggregation）を実行してください。

## 変更前後の比較
| 機能 | 以前の場所 | 新しい場所 |
| :--- | :--- | :--- |
| Batch Import Token KSP | Token KSP Management | Token KSP Data Management |
| Export Token KSP | Token KSP Management | Token KSP Data Management |
| Delete Token KSP | Token KSP Management | Token KSP Data Management |
| **Aggregation Batch** | Token KSP Management | **Aggregation & Reporting** |
| **Export Aggregated KSP** | Token KSP Management | **Aggregation & Reporting** |

## 動作確認済み項目
- [x] `add_admin_menu` に両方のサブメニューが含まれていること。
- [x] `render_token_ksp_page` から集計フォームが削除され、新しい案内文が表示されていること。
- [x] `render_ksp_aggregation_page` が正しく表示され、移動した機能が動作すること。
- [x] 全てのフォーム送信後のリダイレクトが正しいページを指していること。
