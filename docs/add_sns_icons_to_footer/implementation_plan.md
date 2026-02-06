# 実装計画 - フッターへのSNSアイコン追加

フッターのSNSリンク集に、LINE、YouTube、NOTEのアイコンとリンクを追加し、デザインを調整します。

## 変更内容

### [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

#### 1. フッターのリンク追加
以下のリンクを既存の Facebook と Instagram の後に追加します。

- **LINE**: `https://page.line.me/346jwclp`
- **YouTube**: `https://www.youtube.com/channel/UCxt6P4I8nhwMW7ZtOKyt_AQ`
- **NOTE**: `https://note.com/kamakura_inter`

#### 2. アイコンのデザイン
既存のアイコン（背景：gray-800、ホバー時：kmnft-green）と同じスタイルを適用します。
インラインSVGを使用して、各サービスの公式に近いアイコンを表示します。

#### 3. レイアウト調整
現在、アイコンは `flex items-center space-x-8` で横一列に並んでいますが、数が増えるため `flex-wrap justify-center` と `gap-x-8 gap-y-6` に変更し、モバイル端末でも適切に表示されるようにします。

## 検証方法

1. **リンクの動作確認**:
   - LINE、YouTube、NOTEの各アイコンをクリックし、正しいページが別タブで開くことを確認します。
2. **デザインの確認**:
   - 他のアイコンとサイズや色が統一されているか確認します。
3. **レスポンシブ動作の確認**:
   - 画面幅を縮小し、アイコンが自動的に折り返されてレイアウトが崩れないことを確認します。
