<?php
require_once(dirname(__FILE__) . '/wp-load.php');
global $wpdb;

$table_token_ksp = $wpdb->prefix . 'kmnft_token_ksp';

echo "Checking table: $table_token_ksp\n";

// Count rows by season
$counts = $wpdb->get_results("SELECT season, COUNT(*) as count, SUM(acquisition_point) as total_points FROM $table_token_ksp GROUP BY season");

if ($counts) {
    echo "Data found by season:\n";
    foreach ($counts as $c) {
        echo "Season: {$c->season}, Count: {$c->count}, Total Points: {$c->total_points}\n";
    }
} else {
    echo "No data found in $table_token_ksp\n";
}

// Check holdings table for user aggregation
$table_holdings = $wpdb->prefix . 'kmnft_holdings';
echo "\nChecking table: $table_holdings\n";
$holdings_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_holdings");
echo "Total holdings: $holdings_count\n";

// Test the aggregation query specifically for a season if it exists
if (!empty($counts)) {
    $test_season = $counts[0]->season;
    echo "\nTesting aggregation query for season: $test_season\n";

    // We try to run the inner SELECT of the aggregation logic to see what it returns
    $sql = $wpdb->prepare(
        "SELECT token_id, %s as season, SUM(acquisition_point) as total_points
         FROM {$table_token_ksp}
         WHERE season = %s
         GROUP BY token_id
         ORDER BY total_points DESC
         LIMIT 5",
        $test_season,
        $test_season
    );
    $results = $wpdb->get_results($sql);
    echo "Raw Aggregation sample (top 5):\n";
    print_r($results);
}
