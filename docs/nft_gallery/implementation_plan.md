# NFTギャラリー実装計画

## 目標
ダッシュボードの「OWNED ASSETS」セクションの上に、画像が横に流れる「NFTギャラリー」セクションを追加する。

## 実装内容
### 1. ギャラリーセクションの追加 (`page-dashboard.php`)
- **位置**: 右カラム (`md:col-span-8`) の最上部、条件分岐 `is_logged_in` の前（または直後だが、レイアウト上は「OWNED ASSETS」の上）。
- **構造**: `glass-card` スタイルを使用したコンテナ内に、横スクロールする画像リストを配置。
- **データ**: `kmnft_holdings` テーブルからランダムに最大15件のトークンIDを取得して表示。データがない場合はプレースホルダー（ID 1-15）を使用。

### 2. デザインとアニメーション
- **CSS Animation**: CSSの `@keyframes` を使用して、無限ループのマーキー（marquee）アニメーションを実装。
- **レスポンシブ**: スマートフォンでもPCでもスムーズに見えるように `overflow-hidden` と `flex` レイアウトを使用。

### 3. ファイル変更
#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- `<style>` ブロックに `.marquee-track` 等のアニメーション用CSSを追加。
- PHPブロックにてギャラリー用トークンID取得ロジックを追加。
- HTMLのメインコンテンツ部分にギャラリーのマークアップを追加。

## 検証計画
- **表示確認**: ダッシュボードを開き、「OWNED ASSETS」の上に新しいセクションが表示されているか確認。
- **動作確認**: 画像が左へスムーズに流れているか確認。
- **データ確認**: 表示されている画像が実際に存在するトークン画像か（データベースから取得できているか）確認。
