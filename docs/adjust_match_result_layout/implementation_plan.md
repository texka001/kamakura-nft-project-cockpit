# Adjust Match Result Layout

## Goal Description
The user wants to adjust the layout of the "Latest Match Results" section on the dashboard. Specifically, the request is to make the "Ground Image" (Mini Map) smaller and the "Goal Scene Photos" wider.

## Proposed Changes

### Theme Files

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- Locate the Match Results section loop.
- Change the container grid from `grid-cols-1 md:grid-cols-2` to `grid-cols-1 md:grid-cols-12`.
- Change the Map column wrapper to `md:col-span-4` (approx 33%).
- Change the Goal Scenes column wrapper to `md:col-span-8` (approx 67%).

This will reduce the map width from 50% to ~33% and increase the image section width from 50% to ~67%.

## Verification Plan

### Manual Verification
- Review the code changes to ensure the tailwind classes are correctly applied.
- The user will need to verify the visual result on the frontend.
