# League Standings Manager ダウンロード機能追加の実装計画

順位表管理画面の履歴テーブルに、保存済みのデータを CSV としてダウンロードできるボタンを追加します。

## Proposed Changes

### KMNFT_User_Manager クラスの変更

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **コンストラクタ**: `admin_post_kmnft_download_standings` アクションを登録します。
- **`render_standings_page` メソッド**: 履歴テーブルの「Edit」と「Delete」の間に「Download」ボタン（リンク）を追加します。
- **`process_standings_download` メソッド (新規)**: 指定された ID の順位表データを取得し、JSON から CSV に変換してブラウザにダウンロードさせます。

## Verification Plan

### Manual Verification
1.  WordPress 管理画面の「KMNFT Console」>「Standings」にアクセスします。
2.  履歴テーブルの各行に「Download」ボタンが表示されていることを確認します。
3.  「Download」ボタンをクリックし、CSV ファイルがダウンロードされることを確認します。
4.  ダウンロードされた CSV の内容が、元のデータ（rank, clubname, PL, W, D, L, GD, PT）と一致していることを確認します。
