# 実装計画 - SNSアイコンの更新

コクピットフッターのSNSアイコンを、提供された新しいSVGデザインに更新し、視認性を高めるためにサイズを調整します。

## 提案される変更

### テーマファイル (`wp-content/themes/kamakura-cockpit-theme`)

#### [修正] `page-dashboard.php` (file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- フッターセクション（2036行目付近）の各SNSアイコン（X, Facebook, Instagram, LINE, YouTube, NOTE）のSVGを新しいものに置換します。
- `fill="#103D58"` を `fill="currentColor"` に変更し、既存のホバーエフェクト（背景色が緑になりアイコンが黒になる）が機能するようにします。
- アイコンサイズを現在の `h-6 w-6` から `h-8 w-8` に拡大し、視認性を向上させます。

#### [修正] `page-ranking.php` (file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-ranking.php)
- `page-dashboard.php` と同様の修正を行い、デザインの統一性を維持します。

## 検証計画

### 画面確認
- ブラウザでダッシュボードとランキングページを開き、フッターのSNSアイコンが新しくなっていることを確認します。
- ホバー時に色が適切に変わること（背景が緑、アイコンが黒）を確認します。
- サイズが適切（見やすい大きさ）であることを確認します。
