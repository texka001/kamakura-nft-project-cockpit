# Implement Annual KSP Calculation

## Goal Description
Calculate and display the total KSP points for the logged-in user based on their held tokens (`kmnft_holdings`), grouped by season (`kmnft_token_ksp`). The display on the dashboard should be arranged by season, with past seasons collapsible.

## Proposed Changes

### 1. `inc/class-kmnft-user-manager.php`

#### [NEW] Method `get_user_ksp_by_season($user_id)`
Add a new method to `KMNFT_User_Manager` class to handle the aggregation logic.
- **Input**: User ID
- **Logic**:
    1. Fetch all `token_id`s held by the user from `kmnft_holdings`.
    2. If no tokens held, return empty array.
    3. Query `kmnft_token_ksp` table to sum `acquisition_point` grouped by `season` for the fetched `token_id`s.
    4. Return an array of objects/arrays: `[{ season: '2026', total: 100 }, { season: '2025', total: 50 }]`, ordered by season DESC.

### 2. `page-dashboard.php`

#### [MODIFY] KSP Display Section (~Line 25, ~Line 183)
- **Logic Update**:
    1. Instead of querying `kmnft_ksp_ledger` (Line 25), instantiate `KMNFT_User_Manager` (via global `$kmnft_user_manager` or new instance) and call `get_user_ksp_by_season($current_user->ID)`.
    2. Calculate `total_all_time` (sum of all seasons) and `current_season_total` (latest season).
- **UI Update**:
    1. **Big Number**: Show `total_all_time` in the "Total KSP Status" box.
    2. **Breakdown**: Below the main number, verify if there are multiple seasons.
        - List the latest season explicitly: "2026: 100 pt"
        - If past seasons exist, display a collapsible section (Accordion).
        - **Accordion**: 
            - Summary: "Past Seasons"
            - Content: List of "2025: 50 pt", etc.

## Verification Plan

### Manual Verification
1. **Prepare Data**:
    - Ensure the logged-in user holds some tokens (e.g., Token ID: `10093502074`).
    - Ensure `kmnft_token_ksp` has records for these tokens for multiple seasons (e.g., 2026 and 2025).
    - *If only 2026 exists, manually insert a 2025 record for testing.*
    ```sql
    INSERT INTO wp_kmnft_token_ksp (token_id, acquisition_date, acquisition_point, season, reason_1) VALUES ('10093502074', '2025-12-01', 50, '2025', 'Test Past Season');
    ```
2. **Check Dashboard**:
    - **Total**: Verify "Total KSP Status" equals (2026 Total + 2025 Total).
    - **Breakdown**: Verify "2026" shows correct sum.
    - **Collapse**: Click "Past Seasons" (or similar) to expand and see "2025" and its correct sum.
