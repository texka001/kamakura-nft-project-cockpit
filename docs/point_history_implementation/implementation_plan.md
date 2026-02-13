# 実装計画: 管理画面のアクセス制限 (Admin Access Restriction)

一般ユーザー（購読者）がWordPressの管理画面およびプロフィール編集画面にアクセスできないようにし、フロントエンドのダッシュボードにリダイレクトさせる。

## Proposed Changes

### [Component] テーマ機能 (`functions.php`)

  - 一般ユーザー（購読者）の管理画面アクセス制限（リダイレクト・管理バー非表示）。
  - **NEW: 固定ページ自動生成の拡充**: `kmnft_create_core_pages` 関数にランキングページ (`ranking`) を追加し、テーマ有効化時に自動作成されるようにする。

#### [MODIFY] [functions.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/functions.php)

- **管理画面リダイレクト**: `admin_init` アクションフックを使用して、管理者権限 (`manage_options`) を持たないユーザーが管理画面 (`/wp-admin/`) にアクセスした際、サイトのダッシュボードページ (`/dashboard/`) にリダイレクトさせる。
- **ツールバー非表示**: `show_admin_bar` フィルタフックを使用して、管理者権限を持たないユーザーに対してはページ上部の管理バーを表示しないようにする。

## Verification Plan

### Manual Verification
- **購読者（Subscriber）としてログイン**: 
    - ログイン後に `/wp-admin/` にアクセスし、`/dashboard/` へリダイレクトされることを確認。
    - ページ上部に管理バーが表示されていないことを確認。
- **管理者（Administrator）としてログイン**:
    - 通常通り管理画面にアクセスできることを確認。
    - 管理バーが表示されていることを確認。
