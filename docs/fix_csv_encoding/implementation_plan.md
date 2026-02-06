# CSVの文字化け（Excel用）修正の実装計画

Excel で CSV を開いた時に日本語が文字化けする問題を解決するため、出力の先頭に UTF-8 BOM を追加します。

## Proposed Changes

### KMNFT_User_Manager クラスの変更

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **`process_standings_download` メソッド**: `fopen('php://output', 'w')` で出力を開始する直前に、`fwrite($output, "\xEF\xBB\xBF");` を追加して BOM を書き込みます。

## Verification Plan

### Manual Verification
1.  管理画面から順位表の CSV をダウンロードします。
2.  ダウンロードした CSV を Microsoft Excel で直接開きます。
3.  クラブ名などの日本語が正しく表示されることを確認します。
