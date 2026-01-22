# Implementation Plan - Improve X Timeline Fallback

The X (Twitter) timeline widget is currently failing to display content due to a `429 Too Many Requests` error from Twitter's API. This is an environment-specific issue. To improve the user experience during this downtime, we will style the fallback link to look like a proper button and ensure the widget script is loaded efficiently.

## User Review Required
> [!NOTE]
> The `429` error is an external API limitation and cannot be fixed by code changes. This plan focuses on improving the UI when the error occurs.

## Proposed Changes

### Theme Files

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)
- **CSS**: Add `.twitter-fallback-btn` class to the `<style>` section.
    - Style as a "Twitter Blue" button with rounded corners and hover effects.
- **HTML**: Apply `.twitter-fallback-btn` class to the Twitter `<a>` tag.
- **Script**: Move the `<script src="...widgets.js">` tag from the `LATEST NEWS` div to the footer area (before `</body>`) to prevent potential blocking and duplicate initialization attempts.

## Verification Plan

### Automated Tests
- Use browser tool to verify:
    - The `<a>` tag has the new class.
    - The computed style of the link matches the button styling (background color, padding).
    - The `widgets.js` script tag is present in the footer and not in the main content.
    - Console logs to ensure no new errors are introduced.

### Manual Verification
- User to check visually that the "Tweets by stadiumNFT" text now looks like a button.
