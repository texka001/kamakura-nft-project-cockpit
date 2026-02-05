# KSP Statusのエラー修正の確認

ログインしていないゲストユーザーがダッシュボードを表示した際、「KSP Status」セクションで発生していた `$ksp_total_val` の未定義エラーを修正しました。

## 修正内容

### [kamakura-cockpit-theme]

#### [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- 変数初期化セクションに `$ksp_total_val = 0;` を追加しました。これにより、ログインしていない場合でも変数が定義され、表示時のエラーを回避します。

render_diffs(file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

## 動作確認結果

### 修正後の表示（想定）
1.  **ログアウト状態**:
    - ダッシュボードの左側「KSP Status」にエラーが出ず、「0pt」と表示される。
2.  **ログイン状態**:
    - 自身の保持するKSPが正しく表示される。

> [!NOTE]
> 現在の環境では `page-dashboard.php` がダッシュボードの表示を担っています。もし別途 `sidebar-dashboard.php` が存在し、そちらでも同様のエラーが出る場合は、そのファイルでも同様の初期化が必要になります。
