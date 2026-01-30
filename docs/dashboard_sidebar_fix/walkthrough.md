# ダッシュボード・サイドバー Smart Sticky 実装確認

## 変更内容
### [Theme] `kamakura-cockpit-theme`
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- **HTML構造の変更**:
    - サイドバーの `div` に `id="dashboard-sidebar"` を付与し、JSで制御可能にしました。
    - 以前の修正で追加した `max-h-[...]` や `overflow-y-auto` 、`custom-scrollbar` クラスを削除しました。
    - `md:sticky` と `md:self-start` は維持しています。
- **JavaScriptの追加**:
    - ファイル下部にスクリプトを追加しました。
    - **動作ロジック**:
        1.  画面サイズとサイドバーの高さを比較します。
        2.  **サイドバーの方が長い場合**: `bottom: 24px` を適用し、スクロール時に下部に固定されるようにします（My Asset Mapが見えるように）。
        3.  **サイドバーの方が短い場合**: `top: 96px` を適用し、上部に固定します。
        4.  `ResizeObserver` により、アコーディオン開閉などで高さが変わっても自動追従します。

## 検証結果
### コード検証
- Sidebar ID: `dashboard-sidebar` が正しく設定されています。
- Tailwind Class: `md:sticky md:self-start` のみが適用されています。
- JS Script: `</body>` 直前にリサイズ監視ロジックを含むスクリプトが追加されています。

### 目視確認項目（推奨）
ユーザー様の方でブラウザにて以下の動作をご確認ください：
1. **通常表示**: サイドバーが上部に固定されること。
2. **スクロール**: サイドバーが長い場合（アセットが多いなど）、ページスクロールに合わせてサイドバーが動き、最下部が見えたところで止まること。
3. **リサイズ**: ブラウザの高さを変えた際に、挙動が「上付き」「下付き」で切り替わること。
