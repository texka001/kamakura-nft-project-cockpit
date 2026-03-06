# ヘッダーメニューへの DASHBOARD ボタン追加とアクティブ表示

全ページのヘッダーメニューの構成を統一し、Dashboard ページ自身のヘッダーにも「DASHBOARD」ボタンを表示・ハイライトします。

## 提案される変更

### [Component: Theme Headers]

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- ロゴを Dashboard へのリンクに変更します。
- メニューの先頭に `DASHBOARD` ボタンを追加し、アクティブスタイル（`border-kmnft-green text-kmnft-green`）を適用します。

#### [MODIFY] [page-contact.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-contact.php)
- メニューの先頭に `DASHBOARD` ボタンを非アクティブスタイルで追加します。

#### [MODIFY] [page-nft-gallery.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-nft-gallery.php)
- ロゴのホバースタイルを他のページに合わせて変更します。

## 検証プラン

### 手動確認
- Dashboard ページを開き、ヘッダーに「DASHBOARD」ボタンがあり、緑色の枠線で囲まれていることを確認します。
- POINTS, RANKING, CONTACT, NFT GALLERY の各ページを開き、ヘッダーに「DASHBOARD」ボタン（グレー枠）があり、クリックすると Dashboard に遷移することを確認します。
- すべてのページでロゴをクリックすると Dashboard に遷移することを確認します。
