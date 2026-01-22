# Implementation Plan - Allow Multiple KSP Entries per Day

The user wants to be able to import multiple KSP records for the same Token ID on the same day (e.g. distinct point awards).
Current logic checks for `token_id` + `acquisition_date` uniqueness and updates the existing record.
New logic will bypass this check and always `INSERT` new records.

## Proposed Changes

### [Theme] kamakura-cockpit-theme

#### [MODIFY] [inc/class-kmnft-user-manager.php](file:///Users/mukaikazuma/Desktop/AIエージェント開発/kamakura-nft-project202601/wp-content/themes/kamakura-cockpit-theme/inc/class-kmnft-user-manager.php)

1.  **Update `process_token_ksp_import`**:
    - Remove the `SELECT id ...` check for existing records.
    - Remove the `if ($existing_id) { update } else { insert }` logic.
    - Change to always perform `$wpdb->insert()`.
2.  **Update `render_token_ksp_page`**:
    - Add a note in the "Batch Import Token KSP" section explaining that data is always appended (INSERT), not updated, so duplicate uploads will result in duplicate records.

## Verification Plan

- Prepare a CSV with two rows having the same `token_id` and `acquisition_date` but different points/reasons.
- Import the CSV.
- Verify in Database (or Export) that two distinct rows exist.
