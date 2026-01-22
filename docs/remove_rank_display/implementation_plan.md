# 実装計画 - ランク表示の削除

## 目標
ダッシュボードのヘッダーにある「Starter Class」（またはランク）表示と、「Owner Profile」セクションの「Rank」表示を削除します。

## ユーザーレビュー確認事項
- 特になし

## 変更内容
### テーマファイル
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- **ヘッダーセクション (Lines 84-86)**: `<?php echo esc_html($rank); ?> CLASS` を含む `div` を削除します。
- **Owner Profile セクション (Lines 170-175)**: "Rank" とその値を表示している `div` ブロックを削除します。

## 検証計画
### 手動検証
- ヘッダーから「STARTER CLASS」バッジが消えていることを確認します。
- Owner Profileカードから「Rank」ラベルと値が消えていることを確認します。
