# Goal Token IDsの登録形式変更の実装計画

ゴール（得点）ごとにToken IDを管理しやすくするため、管理画面での入力形式を「1行 = 1ゴール」に変更し、1つのゴールに複数のトークンが関わる場合はカンマ区切りで入力できるようにします。また、ユーザーが「1点目：」などの補助的な文字を入力しても正しく処理されるようサニタイズを強化します。

## 変更内容

### [テーマ] kamakura-cockpit-theme

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- `render_match_results_page`: プレースホルダーから混乱を招く「1点目：」等の表記を削除し、純粋なID入力例に変更。説明文に「文字入力不要」の旨を追記。
- `process_match_save`: 正規表現を使用して、各行から数字とカンマ以外の文字を除去する処理を追加。これにより、ラベル入力があった場合もToken IDのみを抽出・保存可能に。

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- `goal_token_ids` のパース処理を修正。改行で分割後、各行内のカンマ区切りIDを抽出し、同一行内のIDを一つのゴール（シーケンス番号）として扱うよう更新。

## 検証方法

### 手動検証
1. 管理画面で、ラベル付き（例：`1点目：12345678901`）や複数行の形式でデータを登録する。
2. ダッシュボードを確認し、マップの番号や画像バッジが正しく表示されることを確認する。
3. 再度編集画面を開き、保存されたデータからラベルが除去され、クリーンな形式になっていることを確認する。
