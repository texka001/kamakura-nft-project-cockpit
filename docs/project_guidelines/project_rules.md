# プロジェクトルール (Project Rules)

## 1. 基本方針
- **言語**: 日本語 (Japanese) を基本とする。
- **ドキュメント**: 実装や修正を行う際は、以下のドキュメントを `docs/<Topic_Name>` フォルダに作成・更新する。
  - `task.md`: タスクリスト
  - `implementation_plan.md`: 実装計画
  - `walkthrough.md`: 実施結果確認 (修正後)

## 2. 技術スタック (Technology Stack)
- **Platform**: WordPress (Custom Theme/Plugin)
- **Language**: PHP, TypeScript/JavaScript
  - Frontend Logic: Vanilla JS, partial use of Alpine.js/Vue.js
- **Styling**: Tailwind CSS (Recommended)
- **UI/UX**: 
  - リッチなデザイン (Future Tech & Organic)、モダンなタイポグラフィ
  - マイクロインタラクション、ダークモードベース (Deep Space Black/Midnight Navy)
  - "Wow" を感じさせるプレミアムなデザイン
- **Database**: MySQL (WordPress default tables + Custom tables)

## 3. コーディング規約 (Coding Standards)
- **命名規則**: わかりやすい英語の変数名・関数名を使用。
- **コメント**: 複雑なロジックには日本語でコメントを付与。
- **絶対パス**: ファイルパスの参照は原則として絶対パスを使用 (ツール使用時)。
- **安全なファイル編集 (Safe Editing)**:
  - コード修正ツール (`replace_file_content`) の適用エラー (Context Not Found) が発生しやすい場合や、大幅な変更を加える場合は、**ファイル全体を読み込んだ上で `write_to_file` (Overwrite=true) を使用して確実な更新を行うこと**。
  - 連続して編集に失敗した場合、必ずこのルールに従う。

## 4. ワークフロー (Workflow)
1. **Planning**: `task.md`, `implementation_plan.md` を作成し、方針を決定。
2. **Review**: ユーザーによる計画の承認。
3. **Execution**: 実装・コーディング。
    - **デプロイ**: コード修正後は必ずプロジェクトルートの `./deploy.sh` を実行し、Local WP環境へ反映すること。
4. **Verification**: 動作確認、`walkthrough.md` の作成。
