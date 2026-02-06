# owner プロファイルのコンパクト化 実装計画

Dashboard サイドバーの「Owner Profile」セクションをよりコンパクトにし、画面の垂直方向のスペースを節約します。

## 提案される変更点

### Dashboard テーマ (`page-dashboard.php`)

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A8%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- **カードのパディング調整**: `p-6` から `p-4` に変更。
- **アバターサイズの縮小**: `w-16 h-16` から `w-12 h-12` に変更。
- **アバター配置の最適化**: 右上に絶対配置されているアバターを、タイトルの横または情報の横に配置し、情報の垂直方向の積み重なりを抑えます。
- **情報間隔の調整**: `space-y-4` から `space-y-2` に変更。
- **テキストサイズの微調整**:
    - User ID / Nickname の値を `text-lg` から `text-base` に変更。
    - Registered ID のラベルをよりコンパクトな配置に検討。

## 検証計画

### 手動確認
- ローカル環境での表示確認（実際の画面でのバランス調整）。
- アバターの「CHANGE」オーバーレイが縮小後も正しく機能することを確認。
- パスワード変更モーダルへのリンクが正しく機能することを確認。
