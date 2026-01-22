# 実装計画 - パスワード変更＆リセット機能

## 目標の概要
ログイン中のユーザーがダッシュボードからパスワードを変更できるようにし、パスワードを忘れたユーザーがログインページからメール経由でリセットできるようにします。

## ユーザーレビューが必要な事項
> [!IMPORTANT]
> この実装では、パスワードリセットメールを送信するためにサーバー上で `wp_mail` が正しく機能する必要があります。

## 提案される変更

### テーマ: `kamakura-cockpit-theme`

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- `kmnft_change_password` POSTリクエストを処理するPHPロジックを冒頭に追加します。
    - `wp_check_password` を使用して現在のパスワードを確認します。
    - `wp_update_user` を使用してユーザーのパスワードを更新します。
    - 成功/エラーメッセージを処理します。
- 「Owner Profile」セクションに「パスワード変更」ボタンを追加します。
- 「パスワード変更」フォーム用の隠しモーダルを追加します。
- モーダルを切り替えるためのJSを追加します。

#### [MODIFY] [page-login.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-login.php)
- 以下を処理するPHPロジックを追加します：
    - `kmnft_forgot_password` POST: トークンを生成し、ユーザーメタに保存して、メールを送信します。
    - `kmnft_reset_password` POST: トークンを検証し、パスワードをリセットします。
- `$_GET['view']` に基づいてビューを切り替える実装を行います：
    - `default`: ログインフォーム。
    - `forgot`: メール入力フォーム。
    - `reset`: 新しいパスワードフォーム（URLに有効な `token` と `email` が必要）。
- 「Forgot Access Code?」リンクを `?view=forgot` に更新します。

## 検証計画

### 手動検証
1.  **パスワード変更 (ダッシュボード)**
    -   ユーザーとしてログインします。
    -   「パスワード変更」をクリックします。
    -   誤った現在のパスワードを入力 -> エラーを確認します。
    -   新しいパスワードの不一致を入力 -> エラーを確認します。
    -   有効なデータを入力 -> 成功メッセージを確認します。
    -   ログアウトし、新しいパスワードでログイン -> 成功を確認します。

2.  **パスワードを忘れた場合 (ログイン)**
    -   ログインページに移動します。
    -   「Forgot Access Code?」をクリックします。
    -   メールアドレスを入力 -> 「メールを確認してください」メッセージを確認します。
    -   リセットリンクにアクセスします（必要に応じてモック化）。
    -   新しいパスワードを入力 -> 成功を確認します。
    -   新しいパスワードでログイン -> 成功を確認します。
