---
description: 変更をコミットしてGitHubにプッシュする
---

1. すべての変更をステージングエリアに追加します。
   ```bash
   git add .
   ```

2. 変更をコミットします。
   *注: 変更内容を要約した説明的なコミットメッセージを入力してください。*
   ```bash
   git commit -m "Update project"
   ```

3. リモートリポジトリにプッシュします。
   ```bash
   git push origin main
   ```
