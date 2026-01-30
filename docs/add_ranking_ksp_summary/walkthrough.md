# Walkthrough - Adding Ranking

## Changes Implemented
- **Database Schema**: Added `rank` column to KSP summary tables (`kmnft_ksp_token_summary`, `kmnft_ksp_user_summary`).
- **Aggregation Logic**: Implemented Standard Ranking calculation (1, 1, 3) using SQL variables within the `process_token_ksp_aggregation` method.
- **CSV Export**: Updated export functions to include the `rank` column in the generated CSV files.

## Verification
The implementation relies on SQL-level calculation, which ensures consistency between the stored data and the export.
The logic uses the following SQL pattern for Standard Ranking:
```sql
@row_num := @row_num + 1,
@rank := IF(@current_points = total_points, @rank, @row_num)
```
This guarantees that ties receive the same rank, and the next rank corresponds to the row position.

### Next Steps for User
- Deploy the changes to the environment.
- Run the aggregation process (or wait for scheduled run).
- Download the CSVs to verify the output.
