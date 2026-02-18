# Implementation Plan - Fix Match Results Video URL Saving

The user reported an issue where assigning a video URL only to the second goal (leaving the first line empty) results in the URL being moved to the first line after saving. This effectively assigns the video to the first goal incorrectly.

## Problem Analysis
1.  **Saving Logic**: `process_match_save` uses `sanitize_textarea_field` for `goal_videos`. This WordPress function transforms the input and *trims whitespace*, including leading newlines. Thus, `\nURL` becomes `URL`.
2.  **Display Logic**: HTML `<textarea>` tags ignore the first newline character immediately following the opening tag. If the data is `\nURL` (intended to be empty line 1, URL line 2), displaying it as `<textarea>\nURL</textarea>` renders as `URL` (Line 1).

## Proposed Changes

### `inc/class-kmnft-user-manager.php`

#### [MODIFY] `process_match_save`
1.  **Goal Videos**:
    -   Change sanitization to preserve newlines.
    -   Split raw POST data by newline, sanitize each line, join with `\n`.
2.  **Goal Images**:
    -   Current logic explicitly strips empty lines.
    -   Update logic to **preserve empty lines**.
    -   Process: Split by newline -> For each line: split by comma, trim, filter empty items -> Join by comma -> Add to result list (even if empty string) -> Join result list by `\n`.

#### [MODIFY] `render_match_results_page`
Update `goal_videos` and `goal_images` textareas.
-   Prepend a newline character `\n` to the echoed content for **both** fields.
-   This ensures leading newlines are displayed correctly in the browser.

## Verification Plan
1.  **Manual Verification**:
    -   **Goal Videos**: Input `\nURL` -> Save -> Verify DB and Edit Screen (Line 1 empty, Line 2 URL).
    -   **Goal Images**: Input `\nImageURL` -> Save -> Verify DB and Edit Screen (Line 1 empty, Line 2 ImageURL).
    -   **Mixed**: Verify that existing data without empty lines still works correctly.
