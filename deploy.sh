#!/bin/bash

# Configuration
SOURCE_DIR="/Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme"
DEST_DIR="/Users/mukaikazuma/Local Sites/kamakura-nft-project/app/public/wp-content/themes/kamakura-cockpit-theme"

# Sync Files
echo "Deploying theme to Local WP..."
rsync -av --delete "$SOURCE_DIR/" "$DEST_DIR/"

echo "Deployment complete."
