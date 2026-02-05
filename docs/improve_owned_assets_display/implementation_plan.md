# Owned Assetsの一覧表示改善

「OWNED ASSETS」の各アイテムにおいて、Token IDを非表示にし、KSP（ポイント）とRANK（順位）をより視認性の高いデザインに改善します。

## 修正内容

### [kamakura-cockpit-theme]

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- **Token IDおよび座標の削除**: 各アセットカードに表示されていた「Token ID」および「X,Y座標」を削除します。これらを削除することで、アセットの画像と主要なステータス（KSP/RANK）に集中できるデザインにします。
- **KSPとRANKの強調**: 
  - KSPのラベルと値を整理し、より詳細画面を開かなくてもポイントがパッと目に入るように調整します。
  - RANKについても、金色のテキスト（`text-kmnft-gold`）を維持しつつ、視認性を高めます。
  - レイアウトを整理し、全体的にすっきりとしたカードデザインに変更します。

## 検証計画

### 画面確認
- ダッシュボードの「OWNED ASSETS」セクションを確認します。
- 各アセットカードから「Token ID」が消えていることを確認します。
- KSPとRANKの表示が改善され、以前よりも読みやすくなっていることを確認します。
