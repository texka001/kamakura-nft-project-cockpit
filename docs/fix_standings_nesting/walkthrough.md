# 修正内容の確認: トグルボタンの修正

## 変更内容
### 1. `toggleSection` 関数の定義修正と移動
`page-dashboard.php` において、`toggleSection` 関数を `window` オブジェクトに明示的にアタッチし、スクリプトブロックを分割して他のデータの読み込みエラーの影響を受けないように変更しました。また、デバッグ用のログは削除しました。

```html
<script>
    // Toggle function defined early and in separate block to avoid ReferenceError
    window.toggleSection = function (contentId, iconId) {
        const content = document.getElementById(contentId);
        const icon = document.getElementById(iconId);
        if (content) {
            content.classList.toggle('hidden');
        }
        if (icon) icon.classList.toggle('rotate-180');
    };
</script>

<script>
    // ... Data logic ...
</script>
```

### 2. 変数の初期化とJSON出力の安全性向上
PHP側で `$tokens_ksp_history` などの変数が未定義の場合に発生するエラーを防ぐため、初期化処理を追加しました。また、`json_encode` の出力をより堅牢にし、万が一エンコードに失敗しても空のオブジェクト `{}` を返すように変更しました。

```php
const tokensHistory = <?php 
    $json = json_encode(!empty($tokens_ksp_history) ? $tokens_ksp_history : new stdClass(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo $json ?: '{}'; 
?>;
```

### 3. トグルボタンへの `type="button"` 追加
「LEAGUE STANDINGS」および「LEAGUE SCHEDULE / RESULTS」セクションのトグルボタンに `type="button"` 属性を追加しました。

## 検証計画
### 手動検証
- ダッシュボードページで「LEAGUE STANDINGS」および「LEAGUE SCHEDULE / RESULTS」のセクション右上の矢印アイコンをクリックし、セクションの開閉が行われることを確認してください。
- コンソールにデバッグログが表示されなくなったことを確認してください。
