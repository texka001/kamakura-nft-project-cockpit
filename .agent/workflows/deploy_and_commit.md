---
description: Deploy changes to Local WP and commit to git (No Push)
---

1. Execute the deployment script to sync theme files to Local WP.
   ```bash
   ./deploy.sh
   ```

2. Add all changes to the staging area.
   ```bash
   git add .
   ```

3. Commit the changes.
   *Note: Please provide a descriptive commit message summarizing the changes made.*
   ```bash
   git commit -m "Update project and deploy"
   ```
