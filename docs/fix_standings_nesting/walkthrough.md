# 修正内容の確認: トグルボタンの修正

## 変更内容
`page-dashboard.php` において、`toggleSection` 関数の定義をスクリプトブロックの先頭に移動しました。
これにより、他のスクリプト（特に厳格モードでの変数の再代入など）でエラーが発生した場合でも、`toggleSection` 関数が確実に定義されるようになります。

### 変更前
```javascript
// スクリプトブロックの最後の方で定義されていた
function toggleSection(contentId, iconId) { ... }
```

### 変更後
```javascript
// スクリプトブロックの直後（変数定義の後）に定義
const tokensHistory = ...;

function toggleSection(contentId, iconId) {
    const content = document.getElementById(contentId);
    const icon = document.getElementById(iconId);
    if (content) content.classList.toggle('hidden');
    if (icon) icon.classList.toggle('rotate-180');
}
```

## 検証計画
### 自動テスト
- なし

### 手動検証
- ダッシュボードページで「LEAGUE STANDINGS」および「LEAGUE SCHEDULE / RESULTS」のセクション右上の矢印アイコンをクリックし、セクションの開閉が行われることを確認してください。
