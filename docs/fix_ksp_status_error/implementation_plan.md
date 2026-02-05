# KSP Status表示時の変数未定義エラーの修正

ログインしていないゲストユーザーがダッシュボードを表示した際、「KSP Status」セクションで `$ksp_total_val` が未定義となり、エラーまたは警告が表示される問題を修正します。

## 修正内容

### [kamakura-cockpit-theme]

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- ファイル上部の変数初期化セクション（10行目〜）に `$ksp_total_val = 0;` を追加します。
- これにより、ログインしていない場合でも `$ksp_total_val` が定義された状態になり、`number_format()` などの関数でエラーが発生するのを防ぎます。

## 検証計画

### 画面確認
- ログアウトした状態で `/dashboard/` にアクセスし、「KSP Status」セクションにエラーが表示されず、「0pt」と正しく表示されることを確認します。
- ログインした状態で、自身のKSPが正しく表示されることを確認します。
