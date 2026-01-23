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
		'contact' => 'KMNFT Contact',
		'nft-gallery' => 'KMNFT Full Gallery',
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
add_action('init', 'kmnft_create_core_pages');
/**
 * Get Remote Thumbnail
 * Checks if a thumbnail exists in local cache. If not, fetches from remote, resizes, saves, and returns the local URL.
 *
 * @param string $remote_url The URL of the external image.
 * @param string|int $token_id Unique identifier for the file naming.
 * @return string Local thumbnail URL or original remote URL on failure.
 */
function kmnft_get_remote_thumbnail($remote_url, $token_id)
{
	// Define cache directory inside uploads
	$upload_dir = wp_upload_dir();
	$cache_dir_name = 'kmnft-cache';
	$cache_path = $upload_dir['basedir'] . '/' . $cache_dir_name;
	$cache_url = $upload_dir['baseurl'] . '/' . $cache_dir_name;

	// Create directory if not exists
	if (!file_exists($cache_path)) {
		wp_mkdir_p($cache_path);
	}

	// Define filenames
	$filename = 'thumb-' . $token_id . '.png';
	$file_path = $cache_path . '/' . $filename;
	$file_url = $cache_url . '/' . $filename;

	// 1. Check if cache exists
	if (file_exists($file_path)) {
		return $file_url;
	}

	// 2. Fetch remote image
	$response = wp_remote_get($remote_url, array('timeout' => 10));
	if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
		return $remote_url; // Fallback to remote
	}

	$image_data = wp_remote_retrieve_body($response);
	if (empty($image_data)) {
		return $remote_url;
	}

	// 3. Load image string into GD resource
	$image = @imagecreatefromstring($image_data);
	if (!$image) {
		return $remote_url;
	}

	// 4. Resize
	$width = imagesx($image);
	$height = imagesy($image);
	$new_width = 150;
	$new_height = 150;

	// Maintain aspect ratio (crop to square or fit? Let's fit for now, or just resize exactly 150x150)
// Actually, object-cover is used in CSS, so maybe just resizing to 150 width is enough?
// Let's do a simple resize to 150x150 for uniformity.

	$thumb = imagecreatetruecolor($new_width, $new_height);

	// Handle transparency for PNG
	imagealphablending($thumb, false);
	imagesavealpha($thumb, true);
	$transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
	imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, $transparent);

	imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	// 5. Save to cache
	imagepng($thumb, $file_path, 9); // Compression level 9 (smaller file)

	// Cleanup
	imagedestroy($image);
	imagedestroy($thumb);

	return $file_url;
}