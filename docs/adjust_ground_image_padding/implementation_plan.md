# グラウンドマップの表示領域拡張

グラウンドマップ（120x64）の周囲にパディング（余白）を持たせ、アセットの丸印が白枠の外にはみ出しても切れないように表示を調整します。
現状は `overflow-hidden` が設定されているため、端にあるアセットの円が半分欠けてしまっていますが、これを解消します。

## ユーザーレビューが必要な事項

> [!NOTE]
> マップの外側に「濃い緑色」の背景を追加して、グラウンドが広がっているように見せます。画像自体を加工するわけではないため、テクスチャ（芝目）までは再現されませんが、違和感のない色味（#1a4023 付近）を適用する予定です。

## 変更内容

### テーマファイル

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

以下の3箇所すべてに同様の変更を適用します。
1. **ダッシュボード左側の「My Asset Map」**
2. **クリック時の「Map Modal」**
3. **右側の「LATEST MATCH RESULTS」内のミニマップ**

**変更イメージ:**

```html
<!-- 変更前 -->
<div class="relative w-full bg-green-900/40 rounded overflow-hidden border border-white/10" style="aspect-ratio: 120/64;">
    ...
</div>

<!-- 変更後 -->
<!-- 外枠（パディングと背景色） -->
<div class="w-full bg-[#1a4023] rounded-lg p-3 border border-white/10"> 
    <!-- 内枠（overflow-hiddenを削除） -->
    <div class="relative w-full h-full" style="aspect-ratio: 120/64;">
         <!-- 白枠線（border）はここに残す -->
         <div class="absolute inset-0 border border-white/30 pointer-events-none z-0"></div>

        <!-- 画像 -->
        <img ... class="w-full h-full object-cover rounded opacity-80 relative z-0">

        <!-- アセット（赤や黄色の丸） -->
        ... (変更なし) ...
    </div>
</div>
```

## 検証計画

### 手動検証
1.  **ダッシュボード表示確認**:
    - [ ] マップの周囲に濃い緑の余白が表示されていること。
    - [ ] 白い枠線（120x64の境界）が表示されていること。
    - [ ] 境界線上（例: 座標 0,0 や 120,64）にある丸いアイコンが、欠けずに円全体が表示されていること。
2.  **モーダル表示確認**:
    - [ ] マップをクリックして拡大表示し、同様に余白がありアイコンが欠けていないことを確認。
3.  **試合結果欄の確認**:
    - [ ] 「LATEST MATCH RESULTS」内の小さなマップでも同様に崩れがないか確認。
