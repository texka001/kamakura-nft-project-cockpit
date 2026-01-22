# リーグスケジュール表示の改修

## Goal Description
リーグスケジュールの表示を整理し、ユーザーが重要な情報（最新シーズンの次戦）に集中できるようにする。
1. **過去シーズン**: デフォルトで非表示。「Show All History」ボタンで表示切り替え。
2. **最新シーズン**: デフォルトで「NEXT」の試合（次戦）のみ表示。「Show All Matches」ボタンで全試合を表示切り替え。

## Proposed Changes

### `page-dashboard.php`

#### [MODIFY] League Schedule Section
- `$schedule_history` loop の構造を変更。
- **Index 0 (最新シーズン)**:
    - `is_next` フラグが立っている行を探索。
    - デフォルト表示用の `tbody` (または行) と、全表示用の `tbody` (または行) を分ける、もしくはCSS/JSで制御。
    - JSを使わずPHPで `hidden` クラスを付与し、ボタンクリックで remove する簡易な実装、あるいは Alpine.js / Vanilla JS を使用。
    - 現状の構成に合わせて、Vanilla JS での制御を追加する（フッター付近の `<script>` に追記、またはインライン `onclick`）。
    - マークアップ構造:
        - `div#latest-season-preview` (NEXT試合のみ)
        - `div#latest-season-full` (全試合 - 初期非表示)
        - `button` "Show Full Schedule"
    - あるいは、テーブル内の行に対して `.hidden` をトグルする。
        - 全行出力するが、`is_next` 以外の行には `style="display:none"` (or tailwind `hidden`) を付与。
        - "Show All Matches" ボタンで `hidden` を削除。

- **Index > 0 (過去シーズン)**:
    - コンテナ全体を `<div class="past-season hidden">` で囲む。
    - セクション下部（ループの外）に "Show All History" ボタンを配置。
    - クリックで `.past-season` の `hidden` を削除。

### JavaScript Logic
- シンプルなトグル機能を実装。
- `toggleLatestSeason()`
- `toggleHistory()`

## Verification Plan

### Manual Verification
1. ダッシュボードにアクセス。
2. 最新シーズンが「NEXT」の試合のみ表示されていることを確認。
3. "Show All Matches"（または適切なラベル）ボタンをクリックし、全試合が表示されることを確認。
4. 過去シーズンが非表示であることを確認。
5. "Show All History" ボタンをクリックし、過去シーズンが表示されることを確認。
6. "NEXT" がない場合（シーズン終了後など）の挙動を確認（最新の１試合を表示、あるいは全表示など）。設計としては、NEXTがない場合は「直近の試合」または「全表示」にするのが自然。今回は「NEXTが見つからない場合は全表示」とする。
