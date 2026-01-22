# Implementation Plan - Support YYYY/MM/DD Date Format

The user wants the Token KSP system (Import and Delete) to accept dates in `YYYY/MM/DD` format, which is common in Japan/Excel, in addition to the standard `YYYY-MM-DD`.

## Proposed Changes

### [Theme] kamakura-cockpit-theme

#### [MODIFY] [inc/class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

1.  **Update `process_token_ksp_delete_by_date`**:
    - Use `strtotime` or `DateTime` to parse the date string.
    - Format it to `Y-m-d` to ensure `2025/1/1` becomes `2025-01-01`.
    - `date('Y-m-d', strtotime(str_replace('/', '-', $date)))` is a robust way to handle both.
2.  **Update `process_token_ksp_import`**:
    - Apply the same robust parsing to `acquisition_date` from CSV.
    - This ensures `2025/1/1`, `2025-1-1`, `2025/01/01` all transform to standard `2025-01-01` for the DB.
3.  **Update `render_token_ksp_page`**:
    - Update the "CSV Format Specification" and "Delete" instructions to mention that `YYYY/MM/DD` is supported.

## Verification Plan

- Check the "Delete" form with `YYYY/MM/DD` input.
- Check the "Import" form with a CSV containing `YYYY/MM/DD`.
- Ensure data is stored as `YYYY-MM-DD` in the DB (which is handled by MySQL DATE type if input is correct, but PHP normalization ensures it).
