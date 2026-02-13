# Goal Imagesの複数行登録（ゴール別管理）の実装計画

ゴール（得点）ごとに画像を管理できるようにするため、「Goal Images」の入力形式を「1行 = 1ゴール」に変更します。1つのゴールに複数の画像がある場合は、同じ行内でカンマ区切りで管理します。

## 変更内容

### [テーマ] kamakura-cockpit-theme

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **管理画面 UI (`render_match_results_page`)**:
  - `goal_images` を hidden input から `textarea` に変更します。
  - プレースホルダーと説明文を「1行 = 1ゴール」「複数画像はカンマ区切り」というルールに合わせて更新します。
- **JavaScript 処理**:
  - メディアアップローダーで画像を選択した際、現在選択されている画像URLを `textarea` に（カンマ区切りで）追記するよう修正します。
  - プレビュー表示 (`#goal-images-container`) は、`textarea` の内容をリアルタイムまたは変更時に反映して表示するように調整します。
- **保存処理 (`process_match_save`)**:
  - `goal_images` の正規化ロジックを追加します（空行の削除、各行内のトリミングなど）。
- **一覧表示 (`Existing Matches`)**:
  - 改行とカンマの両方を区切り文字として扱い、すべての画像を一覧表示できるように修正します。

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- **画像パース処理**:
  - `goal_images` をまず改行で分割し、ゴールごとの画像個別の配列を作成します。
- **画像レンダリング**:
  - 各画像がどのゴール（1点目、2点目...）に属するかを正確に判定し、正しいゴール番号バッジと動画リンクを紐付けます。

---

## 検証方法

### 手動検証
1. 管理画面で「Goal Images」に複数行のデータを入力する。
   - 1行目: 画像AのURL, 画像BのURL
   - 2行目: 画像CのURL
2. プレビューが正しく更新されるか確認する。
3. 保存後、ダッシュボードを確認する。
   - 画像A、画像Bには「1」のバッジが表示され、1点目の動画リンクが紐付いていること。
   - 画像Cには「2」のバッジが表示され、2点目の動画リンクが紐付いていること。
