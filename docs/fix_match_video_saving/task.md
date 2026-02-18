# Task List: Fix Match Results Video URL Saving

- [x] Investigate saving logic
    - [x] Find the file handling match results save.
    - [x] Analyze how `goal_videos` input is processed.
- [x] Create Implementation Plan
- [x] Integrate Goal Images and Videos fix
    - [x] `process_match_save`: Preserve newlines in `goal_videos`.
    - [x] `process_match_save`: Modify `goal_images` logic to keep empty lines.
    - [x] `render_match_results_page`: Prepend `\n` to `goal_videos` textarea.
    - [x] `render_match_results_page`: Prepend `\n` to `goal_images` textarea.
- [x] Verify that empty lines in Goal Images and Videos are preserved
- [x] Verify that `SHOOT ZONE Prize` is saved correctly
- [x] Restore `shoot_prize_memo` assignment
- [x] Prepend newline to `goal_token_ids` textarea
- [ ] Verify `goal_token_ids` with empty first line
- [ ] Deploy and verify all fixes
