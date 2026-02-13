# 修正内容の確認 (Walkthrough): 管理画面のアクセス制限

一般ユーザー（購読者）がWordPressの管理画面に入れないようにし、フロントエンドのマイページに完全に誘導する仕組みを実装しました。

## 変更内容

### [Component] テーマ機能 (`functions.php`)

一般ユーザーの管理画面利用を制限するための以下の処理を追加しました。

#### [MODIFY] [functions.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/functions.php)

- **管理画面リダイレクト**: `admin_init` アクション時に `current_user_can('manage_options')` をチェックし、管理者以外の場合は `home_url('/dashboard/')` へリダイレクトします。
- **ツールバー非表示**: `show_admin_bar` フィルタを管理権限がない場合に `false` に設定し、フロントエンドでのWordPressバー表示を消去しました。

## 検証結果

- **管理者ユーザー**: 通常通り `/wp-admin/` にアクセス可能です。
- **購読者ユーザー**: `/wp-admin/` にアクセスしようとすると `/dashboard/` へリダイレクトされます。また、画面上部の管理バーも表示されません。
