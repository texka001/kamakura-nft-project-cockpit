# User Identity Management Update Plan

## Goal
Separate "Login ID" and "Email Address" in the system to support the client's existing User ID format (e.g., `k779000010`) alongside their Email.

## Current State
- `wp_create_user($email, $password, $email)` uses email for both Login ID and Email.
- Login form accepts 'email' field but processes it as `user_login` (which works for email if login=email).

## Proposed Changes

### 1. Database / Logic (CSV Import)
- **Change**: Update `KMNFT_User_Manager::process_csv_import`.
- **CSV Format Update**: Add `user_login_id` column.
- **Logic**: 
  - Use `user_login_id` for `user_login` (WordPress username).
  - Use `email` for `user_email`.
  - `wp_create_user( $user_login_id, $password, $email )`.

### 2. Login UI (`page-login.php`)
- **Label Update**: Change label from "ID / Email" to "Login ID (k-number)".
- **Logic**: WordPress `wp_signon` supports logging in with either Username or Email by default if configured correctly, but explicitly using the ID is safer if that's the primary key.

### 3. CSV Format
Old: `email, password, display_name, rank, initial_ksp, zone_codes`
New: `user_login_id, email, password, display_name, rank, initial_ksp, zone_codes`

## Verification Plan
1. Create a new CSV with separated ID and Email.
2. Import and verify usage of `k779000010` to login.
