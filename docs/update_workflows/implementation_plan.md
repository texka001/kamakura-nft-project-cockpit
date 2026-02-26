# ワークフローの再編計画

既存のワークフローを整理し、ユーザーの運用フローに合わせて変更します。

## 概要
- `deploy_and_commit.md` を削除し、デプロイのみを行う `deploy.md` を作成。
- `push_to_github.md` を削除し、コミットとプッシュをセットで行う `commit_and_push.md` を作成。

## 変更内容

### [.agent/workflows]

#### [NEW] [deploy.md](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/.agent/workflows/deploy.md)
- `deploy.sh` の実行のみを行う。

#### [NEW] [commit_and_push.md](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/.agent/workflows/commit_and_push.md)
- `git add .`, `git commit`, `git push` を順に実行する。

#### [DELETE] [deploy_and_commit.md](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/.agent/workflows/deploy_and_commit.md)
#### [DELETE] [push_to_github.md](file:///Users/mukaikazuma/Desktop/AI%E3%82%A8%E3%83%BC%E3%82%B8%E3%82%A7%E3%83%B3%E3%83%88%E9%96%8B%E7%99%BA/kamakura-nft-project202601/.agent/workflows/push_to_github.md)

## 検証計画
- `deploy` コマンドが `deploy.sh` を実行することを確認。
- `commit_and_push` コマンドがコミットからプッシュまで行うことを確認。
