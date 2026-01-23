# NFTギャラリー パフォーマンス改善計画

## 現状の課題
ギャラリー上の小さな表示エリア（約96x96px）に対して、外部サーバーの高解像度画像をそのまま読み込んでいるため、以下の問題が発生しています。
- **データ転送量が大きい**: 読み込みに時間がかかり、表示が遅れる。
- **ブラウザ負荷**: 大量の高解像度画像をリサイズして描画するため、パフォーマンスが低下する。

## 解決策
サーバーサイド（WordPress）で外部画像を一度だけ取得し、**サムネイルサイズにリサイズしてサーバー内にキャッシュ**します。
ブラウザには、この軽量化されたキャッシュ画像を配信します。

## 実装詳細

### 1. ユーティリティ関数の追加 (`functions.php`)
新しい関数 `kmnft_get_remote_thumbnail($url, $token_id)` を実装します。

**処理フロー:**
1. キャッシュディレクトリ（`wp-content/uploads/kmnft-cache/`）を確認。
2. 既に変換済みの画像があれば、そのURLを返す（爆速）。
3. なければ、外部URLから画像をダウンロード。
4. WordPressの画像編集機能を使って `150x150` 程度にリサイズ・圧縮。
5. キャッシュディレクトリに保存し、そのURLを返す。
6. エラー時は元のURLをフォールバックとして返す。

### 2. ダッシュボードの修正 (`page-dashboard.php`)
- ギャラリーのループ内で、直接画像URLを表示する代わりに、上記関数を呼び出す。
- `img` タグの `src` にはキャッシュ画像のURLを設定。
- クリック時のモーダル（拡大表示）用には、元の高画質URLを使う（画質維持）。

### 3. ファイル変更対象
#### [MODIFY] [functions.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/functions.php)
- キャッシュ機能の実装。

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- ギャラリー部分の画像出力ロジック変更。

## 検証方法
1. ダッシュボードをリロードし、画像の表示速度が体感で向上しているか確認。
2. ブラウザの開発者ツール（Networkタブ）で、読み込まれている画像のサイズが小さくなっていること（例: 数MB → 数KB）を確認。
3. `wp-content/uploads/kmnft_thumbnails/` フォルダに画像が生成されているか確認。
