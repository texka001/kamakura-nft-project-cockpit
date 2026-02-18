# Walkthrough - Fix Match Results Video & Image Saving

Implemented a fix to correct the saving and display logic for empty lines in "Goal Images" and "Goal Videos" fields in the Match Results editor. This ensures that if a user skips a line (e.g., for the 1st goal) and enters data on the next line (e.g., for the 2nd goal), the empty line is preserved.

## Changes

### `inc/class-kmnft-user-manager.php`

#### `process_match_save`
-   **Goal Images**: Modified logic to split input by valid newlines, process each line (filtering empty comma-separated values but keeping the line itself), and join back with newlines. This preserves empty lines intended for goals without images.
-   **Goal Videos**: Updated to split input by newlines, sanitize each line individually, and join back with newlines. This replaces the previous `sanitize_textarea_field` which stripped leading empty lines.

#### `render_match_results_page`
-   **Display**: Prepended a newline character `\n` to the echoed content within `<textarea>` for both "Goal Images" and "Goal Videos". This forces the browser to recognize leading newlines in the content, ensuring empty lines at the start are visible and editable.

### 3. Regression Fixes (Restored Missing Logic)
- **`process_match_save`**: Restored the missing assignment for `$shoot_prize_memo` which was causing the "SHOOT ZONE Prize" to not save.
- **`process_match_save`**: Restored the logic for processing `$goal_token_ids` into `$clean_token_ids`, which was accidentally removed.

## Verification

### 1. Match Results Saving
- **Empty Lines**:
    - Enter multiple video URLs with empty lines in between.
    - Save and reload.
    - Verify that empty lines are preserved in the textarea and the order matches the goals.
-   **Goal Videos**:
    -   [ ] Enter a video URL on the 2nd line only (leave 1st line empty).
    -   [ ] Save.
    -   [ ] Verify in "Existing Matches" table that the video corresponds to the 2nd goal (or check DB).
    -   [ ] Open Edit screen again -> Verify 1st line is empty and URL is on 2nd line.
-   **Goal Images**:
    -   [ ] Enter an image URL on the 2nd line only.
    -   [ ] Save.
    -   [ ] Verify preview/table.
    -   [ ] Open Edit screen again -> Verify 1st line is empty and URL is on 2nd line.
-   **Goal Token IDs**:
    -   [ ] Enter a token ID on the 2nd line only (leave 1st line empty).
    -   [ ] Verify.
    -   [ ] Open Edit screen again -> Verify 1st line is empty.
