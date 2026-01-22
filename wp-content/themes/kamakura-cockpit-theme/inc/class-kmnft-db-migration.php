<?php
/**
 * Handles Custom Database Tables creation using dbDelta.
 */
class KMNFT_DB_Migration
{
	public function create_tables()
	{
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// 1. User Meta (Extended)
		// Stores additional user info like Wallet Address and Rank
		$table_user_meta = $wpdb->prefix . 'kmnft_user_meta';
		$sql_user_meta = "CREATE TABLE $table_user_meta (
			user_id bigint(20) unsigned NOT NULL,
			wallet_address varchar(255) DEFAULT '',
			rank_current varchar(50) DEFAULT 'BRONZE',
			PRIMARY KEY  (user_id)
		) $charset_collate;";
		dbDelta($sql_user_meta);

		// 2. Holdings (NFT/Zone)
		// Stores which zone/NFT a user owns
		$table_holdings = $wpdb->prefix . 'kmnft_holdings';
		$sql_holdings = "CREATE TABLE $table_holdings (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			token_id varchar(100) NOT NULL,
			zone_code varchar(50) NOT NULL,
			zone_name varchar(255) NOT NULL,
			image_url varchar(255) DEFAULT '',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta($sql_holdings);

		// 3. KSP Ledger
		// Stores history of Kamakura Support Points
		$table_ksp = $wpdb->prefix . 'kmnft_ksp_ledger';
		$sql_ksp = "CREATE TABLE $table_ksp (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			amount int(11) NOT NULL,
			transaction_type varchar(50) NOT NULL,
			season varchar(10) NOT NULL,
			description text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta($sql_ksp);

		// 4. Prediction Games
		// Stores user predictions for matches
		$table_games = $wpdb->prefix . 'kmnft_prediction_games';
		$sql_games = "CREATE TABLE $table_games (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			match_id varchar(50), 
			user_id bigint(20) unsigned NOT NULL,
			prediction_score_home int(3),
			prediction_score_away int(3),
			prediction_mom varchar(255),
			status varchar(20) DEFAULT 'PENDING',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset_collate;";
		dbDelta($sql_games);

		// 5. Token KSP
		// Stores acquisition points for each token
		$table_token_ksp = $wpdb->prefix . 'kmnft_token_ksp';
		$sql_token_ksp = "CREATE TABLE $table_token_ksp (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			token_id varchar(100) NOT NULL,
			acquisition_date date NOT NULL,
			acquisition_point int(11) NOT NULL,
			season varchar(20) DEFAULT '',
			reason_1 text,
			reason_2 text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY token_id (token_id)
		) $charset_collate;";
		dbDelta($sql_token_ksp);
	}
}
