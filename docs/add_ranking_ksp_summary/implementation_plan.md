# Adding Ranking to KSP Summary

## Goal
Implement ranking calculation for Token and User KSP summaries and include it in CSV exports. The ranking is based on `total_points` using Standard Ranking (1, 1, 3).

## Proposed Changes
### PHP
#### [MODIFY] [class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)
- Updated `ensure_ksp_summary_tables` to add `rank` integer column to `kmnft_ksp_token_summary` and `kmnft_ksp_user_summary` tables.
- Updated `process_token_ksp_aggregation` to calculate ranking using SQL variables (`@row_num`, `@rank`) during insertion. Implemented Standard Ranking (ties share rank, gaps follow).
- Updated `process_token_summary_export` and `process_user_summary_export` functions to output the `rank` column as the first column in the CSV file.

## Verification Plan
### Manual Verification
1.  **Trigger Aggregation**: Access the admin page or trigger the aggregation process (e.g., via the "Aggregate" button if available, or reloading the KSP page if it triggers it).
2.  **Export CSV**: Use the "Export Token Summary" and "Export User Summary" buttons.
3.  **Inspect CSV**: Open the downloaded CSV files.
    - Confirm the first column is `rank`.
    - Confirm rows are sorted by `total_points` descending (or `rank` ascending).
    - Verify ranking logic (e.g., if two items have same points, they have same rank; the next item has rank = row number).
