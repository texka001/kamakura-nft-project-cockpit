# 実装計画 - ダッシュボードレイアウト調整

## 目標
ダッシュボードのマッチ結果セクションにおいて、「GOAL SCENES」のタイトルを「VS [対戦相手]」のテキストと揃え、ゴールシーン画像をグラウンドマップ画像と揃える。

## 変更案

### `page-dashboard.php`

#### [変更] マッチ結果セクションのループ内 (408-490行目付近)

**現在の構造:**
```html
<div class="text-sm text-white mb-2 font-bold">VS Opponent</div>
<div class="grid ...">
    <div class="col-span-4">Map...</div>
    <div class="col-span-8">
        <h4>GOAL SCENES</h4>
        Images...
    </div>
</div>
```

**新しい構造:**
```html
<!-- ヘッダー行 -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-2">
    <!-- VSタイトルをマップの列と揃える -->
    <div class="md:col-span-4">
        <div class="text-sm text-white font-bold">VS <?php echo esc_html($match->opponent); ?></div>
    </div>
    <!-- Goal Scenesタイトルを画像の列と揃える -->
    <div class="md:col-span-8">
        <h4 class="text-xs font-bold text-gray-400">GOAL SCENES</h4>
    </div>
</div>

<!-- コンテンツ行 -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <div class="md:col-span-4">Map...</div>
    <div class="md:col-span-8">
        <!-- 画像グリッド (タイトルはここから削除) -->
        <div class="grid grid-cols-2 gap-2">...</div>
    </div>
</div>
```

## 検証計画

### 手動検証
1.  **目視確認**:
    - 「VS [対戦相手]」と「GOAL SCENES」が同じ水平レベルにあるか確認。
    - グラウンドマップの上部とゴールシーン画像の最初の行の上部が揃っているか確認。
    - レスポンシブ動作（モバイル対デスクトップ）をテスト。

### 自動テスト
- なし（視覚的なレイアウト調整のみ）。
