# Implementation Plan - Create Red Button

User requested a red button with text "HELLO Antigravity" in the center.

## Proposed Changes

### [Theme] kamakura-cockpit-theme

#### [MODIFY] [page-dashboard.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/page-dashboard.php)

- Insert a new `div` element at the top of the main content grid (inside `<main>`).
- Set the `div` to span all columns (`col-span-12`) and center its content.
- Add a button with the following styles:
    - Background: Bright Red (`bg-red-500` / `hover:bg-red-400`)
    - Text: White, Bold, "HELLO Antigravity"
    - Style: Rounded full, shadow for visibility.

```html
<!-- Added Button -->
<div class="md:col-span-12 flex justify-center mb-8">
    <a href="#" class="bg-red-500 hover:bg-red-400 text-white font-bold py-4 px-12 rounded-full shadow-[0_0_20px_rgba(255,50,50,0.6)] transition duration-300 transform hover:scale-105 text-xl tracking-widest">
        HELLO Antigravity
    </a>
</div>
```

## Verification Plan

### Manual Verification
- Open the dashboard page (root URL).
- Verify that the button appears at the top center of the content area.
- Verify the text is "HELLO Antigravity".
- Verify the color is a bright red.
- Check hover effects.
