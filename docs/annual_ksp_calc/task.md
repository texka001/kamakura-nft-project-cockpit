# Implement Annual KSP Calculation

- [x] Investigate `page-dashboard.php` to identify current KSP display logic <!-- id: 0 -->
- [x] Investigate `class-kmnft-user-manager.php` for existing token/user data retrieval methods <!-- id: 1 -->
- [x] Create Implementation Plan <!-- id: 2 -->
- [x] Implement `get_user_annual_ksp($user_id)` method in `KMNFT_User_Manager` <!-- id: 3 -->
    - Retrieve user's held tokens from `kmnft_holdings`
    - Calculate sum of KSP from `kmnft_token_ksp` for those tokens, grouped by `season`
- [x] Update `page-dashboard.php` to display KSP grouped by season with collapsible UI <!-- id: 4 -->
- [x] Verify the display with test data <!-- id: 5 -->
- [x] Deploy and Commit <!-- id: 6 -->
