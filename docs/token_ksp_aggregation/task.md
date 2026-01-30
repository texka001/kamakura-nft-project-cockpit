# Token KSP Aggregation Task List

- [x] **Database Schema Update**
    - [x] Define `kmnft_ksp_token_summary` table in `inc/class-kmnft-db-migration.php`
    - [x] Define `kmnft_ksp_user_summary` table in `inc/class-kmnft-db-migration.php`
    - [x] Run migration to create tables (Handled via ensure method in User Manager or Theme Switch)
- [x] **Backend Implementation**
    - [x] Add `admin_post` action for aggregation trigger in `inc/class-kmnft-user-manager.php`
    - [x] Implement `process_token_ksp_aggregation` method
        - [x] Logic to clear existing data for target season
        - [x] Logic to aggregate Token KSP (Token x Season)
        - [x] Logic to aggregate User KSP (User x Season using Current Holdings)
- [x] **UI Implementation**
    - [x] Add "Aggregation" section to `render_token_ksp_page` in `inc/class-kmnft-user-manager.php`
    - [x] Create form with Season input and Submit button
- [x] **Verification**
    - [x] Verify tables are created
    - [x] Verify aggregation logic produces correct sums
    - [ ] Verify "Wash and Replace" behavior
