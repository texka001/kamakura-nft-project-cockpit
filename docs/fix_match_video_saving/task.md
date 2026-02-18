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
- [ ] Verify handling of empty lines for both fields.
