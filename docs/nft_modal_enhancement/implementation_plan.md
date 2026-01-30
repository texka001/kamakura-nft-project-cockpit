# ダッシュボードへのNFT詳細モーダル実装計画

ダッシュボードでNFTを選択した際に、ランキング画面と同様の「詳細情報（KSPポイント、ランク、座標）」を表示するリッチなモーダルを実装します。

## 変更内容

### 1. ダッシュボード（page-dashboard.php）の更新

#### データ取得・準備
- 保有アセットリスト（`$holdings`）に加え、GROUND MAP用のアセットデータにもKSP情報（ポイント・ランク）を紐付けられるよう準備します。

#### UI（HTML/CSS）
- 既存のシンプルな `image-modal` を置き換えるか、あるいは `page-ranking.php` から `token-modal` のHTML構造を移植します。
- モーダル内には以下の情報を表示します：
    - Token ID
    - そのシーズンのランク（#XX）
    - 獲得KSPポイント
    - 座標（X, Y）

#### インタラクション（JavaScript）
- 既存の `openImageModal` を `openTokenModal(tokenId, rank, points, x, y)` にアップグレードします。
- 以下の箇所からのクリックイベントを更新します：
    - 「OWNED ASSETS」セクションの各カード
    - 「GROUND MAP」内の各アセットアイコン

### 2. KMNFT_User_Manager クラス
- (既に集計データ取得用の `get_tokens_ksp_summary` は実装済みのため、追加の変更はありません)

## 検証計画

### 手動検証
- ダッシュボードの「OWNED ASSETS」のNFTをクリックし、画像だけでなくポイントやランクが表示されることを確認。
- 「GROUND MAP」内のNFTをクリックし、同様に詳細情報が表示されることを確認。
- デザインがランキングページのモーダルと統一されていることを確認。
