# プロジェクトメモリ (Project Memory)

## プロジェクト概要
**プロジェクト名**: Kamakura NFT Project 202601
**目的**: (NFTプロジェクトの概要を記述 - 詳細はユーザー入力待ち)

## 現在のステータス
- **フェーズ**: Phase 1 (MVP) 完了 / Phase 2 (Interaction) 準備中
- **完了済みタスク**:
  - ドキュメントフォルダ構成の作成 (`docs/`)
  - プロジェクトルールの策定
  - 要件定義・基本設計書の作成 ([requirements.md](requirements.md))
  - Phase 1 実装 (カスタムテーマ・DB構築・ユーザー管理・ログイン/ダッシュボードUI)
  - デプロイ環境の整備 (`deploy.sh`)

## 技術的決定事項 (Key Decisions)
- [2026-01-08] プロジェクトのドキュメント管理を `docs/` 配下で行うことを決定。
- [2026-01-08] 要件定義書 (v1.4) により、Wordpressカスタムテーマでの開発を決定。

## 開発環境
- **OS**: Mac
- **Editor**: Cursor / VS Code
- **Platform**: WordPress (Latest ver)
- **Language**: PHP, JavaScript (Vanilla/Alpine.js/Vue.js)
- **Styling**: Tailwind CSS (推奨)
- **Database**: MySQL (WP standard + Custom Tables)

## 参照リンク/リソース
- (デザインファイル、要件定義書などがあればここに追記)
