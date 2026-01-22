# Implementation Plan: Sample CSV Download

## Goal
Add a button to the "League Schedule" admin page that allows users to download a sample CSV file with the correct format for importing schedule data.

## Proposed Changes

### [Backend] `class-kmnft-user-manager.php`

#### [MODIFY] `class-kmnft-user-manager.php`
- Add `add_action('admin_post_kmnft_download_sample_league_schedule_csv', ...)` to the constructor.
- Add hook registration to handle the download request.
- Implement `process_download_sample_league_schedule_csv()` function:
    - Set headers for CSV download.
    - Output sample data: `Section, Date(m/d), Time, Score(H - A), Opponent`.
    - `exit`.
- Update `render_league_schedule_page()`:
    - Add a link/button "Download Sample CSV" near the file input.
    - Link to `admin_url('admin-post.php?action=kmnft_download_sample_league_schedule_csv')`.

## Verification Plan
1. Access the "League Schedule" admin page.
2. Click "Download Sample CSV".
3. Verify the file `sample_league_schedule.csv` is downloaded.
4. Verify the content matches the expected format.
