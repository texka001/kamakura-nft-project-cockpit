# 修正内容の確認: トグルボタンの修正

## 変更内容
### 1. `toggleSection` 関数の定義修正
`page-dashboard.php` において、`toggleSection` 関数を `window` オブジェクトに明示的にアタッチするように変更しました。これにより、関数のスコープに関連する問題を回避し、確実に呼び出せるようにしました。また、デバッグ用の console.log を追加しました。

```javascript
// 変更前
function toggleSection(contentId, iconId) { ... }

// 変更後
window.toggleSection = function(contentId, iconId) {
    console.log('toggleSection called', contentId, iconId);
    // ...
};
```

### 2. トグルボタンへの `type="button"` 追加
「LEAGUE STANDINGS」および「LEAGUE SCHEDULE / RESULTS」セクションのトグルボタンに `type="button"` 属性を追加しました。これにより、意図しないフォーム送信動作（もしある場合）を防止し、純粋なJavaScriptトリガーとして機能するようにしました。

```html
<!-- 変更前 -->
<button onclick="toggleSection(...)">

<!-- 変更後 -->
<button type="button" onclick="toggleSection(...)">
```

### 3. HTML構造の確認
前回の修正で「LEAGUE STANDINGS」内の入れ子構造の問題は解消されていることをコード上で確認しました。

## 検証計画
### 手動検証
- ダッシュボードページで「LEAGUE STANDINGS」および「LEAGUE SCHEDULE / RESULTS」のセクション右上の矢印アイコンをクリックし、セクションの開閉が行われることを確認してください。
- ブラウザの開発者ツール（Console）を開き、クリック時に `toggleSection called` というログが表示されるか確認することで、関数が呼び出されているかを検証できます。
