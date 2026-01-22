<?php
/**
 * Kamakura Cockpit Theme functions and definitions
 */

if (!defined('KMNFT_VERSION')) {
	define('KMNFT_VERSION', '1.0.0');
}

// Load Configuration
require_once get_template_directory() . '/inc/kmnft-config.php';

// Include database migration class
require_once get_template_directory() . '/inc/class-kmnft-db-migration.php';
require_once get_template_directory() . '/inc/class-kmnft-user-manager.php';
require_once get_template_directory() . '/inc/class-kmnft-dashboard-ajax.php';

// Initialize User Manager
new KMNFT_User_Manager();
new KMNFT_Dashboard_Ajax();

// Setup support
function kmnft_setup()
{
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'kmnft_setup');

/**
 * DB Migration on Theme Switch
 * Triggers table creation when the theme is activated (switched to).
 */
function kmnft_on_theme_switch()
{
	$migration = new KMNFT_DB_Migration();
	$migration->create_tables();
}
add_action('after_switch_theme', 'kmnft_on_theme_switch');

/**
 * Enqueue scripts and styles.
 */
function kmnft_scripts()
{
	// Tailwind CDN for dev environment convenience (production should use build)
	// wp_enqueue_script('tailwind', 'https://cdn.tailwindcss.com', array(), null, false);
}
add_action('wp_enqueue_scripts', 'kmnft_scripts');

/**
 * Auto-create core pages on theme switch
 */
function kmnft_create_core_pages()
{
	$pages = array(
		'login' => 'KMNFT Login',
		'dashboard' => 'KMNFT Dashboard',
	);

	foreach ($pages as $slug => $template_name) {
		$page_check = get_page_by_path($slug);
		if (!$page_check) {
			$page_id = wp_insert_post(array(
				'post_title' => ucfirst($slug),
				'post_name' => $slug,
				'post_status' => 'publish',
				'post_type' => 'page',
			));

			// Assign Template
			if ($page_id && !is_wp_error($page_id)) {
				update_post_meta($page_id, '_wp_page_template', 'page-' . $slug . '.php');
			}
		}
	}
}
add_action('after_switch_theme', 'kmnft_create_core_pages');
