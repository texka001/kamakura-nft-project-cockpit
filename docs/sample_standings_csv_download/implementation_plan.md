# 順位表サンプルCSVダウンロード機能の実装計画

順位表の登録を容易にするため、League Schedule Manager と同様にサンプル CSV をダウンロードできるボタンを追加します。

## Proposed Changes

### KMNFT_User_Manager クラスの変更

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

- **アクションの登録**:
  `__construct` で `admin_post_kmnft_download_sample_standings_csv` を登録します。

- **UI の追加**:
  `render_standings_page` の CSV ファイル選択エリアに、「Download Sample CSV」ボタンを追加します。

- **ダウンロード処理の実装**:
  `process_download_sample_standings_csv` メソッドを追加し、BOM 付きの UTF-8 CSV を出力するようにします。出力内容は以下の通りです：
  - ヘッダー: `rank`, `clubname`, `PL`, `W`, `D`, `L`, `GD`, `PT`
  - サンプルデータ: 鎌倉インターナショナルFCを含む数行のデータ。

## Verification Plan

### Manual Verification
1.  順位表管理画面（KMNFT Console > Standings）を開きます。
2.  CSVファイル選択エリアに「Download Sample CSV」ボタンが表示されていることを確認します。
3.  ボタンをクリックし、`sample_standings.csv` がダウンロードされることを確認します。
4.  ダウンロードしたファイルを Excel 等で開き、文字化けがないこと、およびヘッダー行が正しいことを確認します。
