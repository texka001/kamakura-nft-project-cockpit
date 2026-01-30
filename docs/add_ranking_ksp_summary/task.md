# Task: Debugging Aggregation Error

- [/] Switch aggregation logic to PHP-side calculation
    - [ ] Modify `process_token_ksp_aggregation` to fetch raw data first
    - [ ] Calculate Standard Ranking in PHP
    - [ ] Insert data using bulk INSERT
- [ ] Add explicit counting of raw input rows for better error messages
- [ ] Verify if data exists using debug messages if count is 0
