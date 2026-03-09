# ログイン画面の視認性向上と日本語併記の実装計画

ログイン画面の「Forgot Access Code?」リンクが見えにくいため、テキストの色を明るくし、日本語を併記することで利便性を向上させます。

## 提案される変更

### ログインページ (`page-login.php`)

#### [MODIFY] [page-login.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-login.php)

- **文言の更新**:
  - `Forgot Access Code?` → `Forgot Password? / パスワードをお忘れですか？`
  - `Back to Login` → `Back to Login / ログイン画面に戻る`
  - `Remember Me` → `Remember Me / ログイン状態を保持する`
- **スタイルの更新**:
  - `text-gray-500` を `text-gray-300` に変更し、コントラストを大幅に高めます。
  - `text-gray-400` も必要に応じて `text-gray-300` に引き上げ、全体的な視認性を確保します。
  - ホバー時の色（`hover:text-kmnft-green`）は維持します。

## 検証計画

### 手動確認
- ブラウザでログイン画面を表示し、以下の点を確認します：
  - 各リンクが日本語併記になっていること。
  - テキストが背景に対して十分な視認性を持っていること。
  - ホバー時に色が変わり、クリック可能であることが明確であること。
