# 実装計画 - ゴール画像入力ロジックの改善

「画像を追加」ボタンをクリックして画像を選択した際、不要なカンマが挿入される問題を修正し、改行を優先した追記ロジックに変更します。

## ユーザーレビューが必要な項目
なし

## 変更内容

### 1. 管理画面 JS の修正
`inc/class-kmnft-user-manager.php` 内のメディアアップローダー選択後のロジックを修正します。

#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)
- 現状、既存の値の末尾を `trim()` してからカンマまたは改行を追加していますが、これを変更します。
- 既存の値が空でない場合、末尾に改行がない場合は改行を追加してから、新しい画像URLを追加するようにします。
- 1回の選択アクションで複数の画像を選んだ場合は、それらの間には引き続きカンマを使用します。

```javascript
// 修正イメージ
var currentVal = $('#goal_images_textarea').val();
if (newUrls.length > 0) {
    if (currentVal.length > 0 && !currentVal.match(/\n\s*$/)) {
        currentVal += "\n";
    }
    $('#goal_images_textarea').val(currentVal + newUrls.join(', '));
    updatePreview();
}
```

## 検証計画

### 自動テスト
なし

### 手動確認
1. 管理画面の試合結果編集画面で「画像を追加」をクリック。
2. 1枚選択 -> textarea に追加されることを確認。
3. 再度クリックして別の枚数を選択 -> **改行されて**追加されることを確認（カンマがつかないこと）。
4. textarea の最後に手動で改行を入れてからボタンを押す -> 不要な空行が増えず、正しく追記されることを確認。
