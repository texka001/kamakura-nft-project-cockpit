# Walkthrough - Footer Links Addition

## Update Summary
Added a footer section to the dashboard page (`page-dashboard.php`) containing links to the official website and SNS accounts.

## Changes
### `page-dashboard.php`
- Added a `<footer>` section at the bottom of the page.
- Included links for:
  - Official Website: [https://kamakura-inter.com/](https://kamakura-inter.com/)
  - X (Twitter): [https://twitter.com/kamakura_inter](https://twitter.com/kamakura_inter)
  - Facebook: [https://www.facebook.com/KamakuraInterFC](https://www.facebook.com/KamakuraInterFC)
  - Instagram: [https://www.instagram.com/kamakura_inter_fc/](https://www.instagram.com/kamakura_inter_fc/)
- Styled with Tailwind CSS to match the existing dark/neon theme.
- Used SVG icons for each platform.

## Verification Results
### Deployment
- Successfully ran `./deploy.sh` to sync changes to the local WordPress environment.

### Visual Verification
- Footer appears at the bottom of the page.
- Links open in a new tab (`target="_blank"`).
- Hover effects are working (icons change color, text brightens).
