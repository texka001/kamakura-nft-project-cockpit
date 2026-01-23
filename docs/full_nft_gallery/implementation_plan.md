# 全NFTギャラリー実装計画

## 目標
ダッシュボードとは別に、全ての保有NFTを閲覧できる「NFT Gallery」ページを作成する。
パフォーマンスを考慮し、Ajaxを用いた「Load More（もっと見る）」形式で段階的に画像を読み込む。

## 実装内容

### 1. Ajaxハンドラーの強化
`inc/class-kmnft-dashboard-ajax.php` に新しいメソッド `handle_load_more_gallery()` を追加する。

- **パラメータ**:
    - `page`: 現在のページ番号（またはオフセット）
    - `limit`: 1回あたりの取得数（例: 24）
- **処理**:
    - `kmnft_holdings` テーブルから指定範囲のトークンIDを取得。
    - 各トークンについて、`kmnft_get_remote_thumbnail()` を呼び出し、サムネイルURLを取得。
    - JSON形式で `token_id`, `image_url` (thumbnail), `original_url` (modal用) を返却。
    - `has_more` フラグを返却し、次があるか判定させる。

### 2. 新規ページテンプレート: `page-nft-gallery.php`
- ダッシュボードと同様のヘッダー・フッター・背景スタイル (`page-dashboard.php` をベースにする)。
- メインコンテンツ:
    - タイトル "Full NFT Gallery"
    - グリッドコンテナ（CSS Grid）
    - "Load More" ボタン（全件表示後は非表示）
    - "Back to Dashboard" リンク
- JavaScript:
    - ページロード時に初回データを取得（またはPHPで初回分だけ埋め込む）。
    - "Load More" ボタンクリックでAjaxリクエストを送信し、結果をグリッドに追記（Append）。
    - 画像クリック時のモーダル表示ロジック（ダッシュボードから流用）。

### 3. ダッシュボードからのリンク
`page-dashboard.php` のNFT Galleryセクションに "View All ->" リンクを追加し、`/nft-gallery/` へ遷移させる。

### 4. ページ作成
`functions.php` の `kmnft_create_core_pages` に `nft-gallery` を追加し、自動で固定ページが生成されるようにする。

## ファイル変更
#### [MODIFY] [inc/class-kmnft-dashboard-ajax.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-dashboard-ajax.php)
#### [NEW] [page-nft-gallery.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-nft-gallery.php)
#### [MODIFY] [functions.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/functions.php)
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

## 検証計画
1. `/nft-gallery` にアクセスし、初期画像が表示されるか。
2. "Load More" を押して次の画像セットが読み込まれるか。
3. サムネイルが表示され、クリックで高画質モーダルが出るか。
4. ダッシュボードのリンクから正しく遷移できるか。
