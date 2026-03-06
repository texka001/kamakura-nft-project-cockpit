# 実装計画 - Match Results Manager の Goal Images ボタン修正

## 課題
Match Results Manager の「Goal Images」セクションにある「画像を追加」ボタンが動作していません。
調査の結果、`inc/class-kmnft-user-manager.php` 内の `render_match_results_page` メソッドに含まれる JavaScript コードに構文エラー（`function` が `functi o n` となっている）があり、スクリプト全体の実行が妨げられていることが判明しました。

## 変更内容

### Proposed Changes
### Theme Admin Logic

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- `render_match_results_page` 内の JavaScript を修正し、構文エラー（`functi o n`）を解消。
- インデントと空白の整理。
- **Goal Images の説明文を更新し、複数ゴール時の入力ルールを明示。**

## 検証プラン

### 手動確認
1.  WordPress 管理画面で「KMNFT Console」>「Match Results」に移動します。
2.  「Goal Images」セクションの「画像を追加」ボタンをクリックします。
3.  WordPress のメディアライブラリが正常に開くことを確認します。
4.  画像を1つまたは複数選択し、「Add to List」をクリックします。
5.  選択した画像の URL がテキストエリアに（カンマ区切りまたは改行後に）挿入されることを確認します。
6.  プレビュー領域に選択した画像が表示されることを確認します。
