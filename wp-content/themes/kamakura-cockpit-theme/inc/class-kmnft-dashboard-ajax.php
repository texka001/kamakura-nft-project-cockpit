<?php
/**
 * Handles Dashboard AJAX interactions.
 */
class KMNFT_Dashboard_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_kmnft_upload_user_icon', array($this, 'handle_upload_user_icon'));
        add_action('wp_ajax_kmnft_load_more_gallery', array($this, 'handle_load_more_gallery'));
        add_action('wp_ajax_nopriv_kmnft_load_more_gallery', array($this, 'handle_load_more_gallery')); // Allow public access
    }

    /**
     * Handle Load More Gallery Ajax
     */
    public function handle_load_more_gallery()
    {
        // Simple Nonce Check (optional for public read, but good usage)
        // If coming from dashboard/gallery page, nonce should be present
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, 'kmnft_dashboard_nonce')) {
            // For public gallery, maybe we can relax this or ensure nonce is on page
            // Let's enforce strict nonce for consistency
            wp_send_json_error(array('message' => 'Invalid nonce.'));
        }

        global $wpdb;

        // Params
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 24;
        $offset = ($page - 1) * $limit;

        // Query
        $tokens = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT token_id FROM {$wpdb->prefix}kmnft_holdings ORDER BY id DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));

        // Count total for "has_more" check
        $total_tokens = $wpdb->get_var("SELECT COUNT(DISTINCT token_id) FROM {$wpdb->prefix}kmnft_holdings");
        $has_more = ($offset + $limit) < $total_tokens;

        if (empty($tokens)) {
            if ($page === 1 && $total_tokens == 0) {
                // Fallback for demo if absolutely no data exists
                $tokens = range(1, 12); // Demo data
                $has_more = false;
            } else {
                wp_send_json_success(array('html' => '', 'has_more' => false));
                return;
            }
        }

        // Generate HTML
        ob_start();
        foreach ($tokens as $token_id) {
            $original_url = KMNFT_IMAGE_BASE_URL . esc_attr($token_id) . '.png';
            // Use server-side cache if function exists
            $thumb_url = function_exists('kmnft_get_remote_thumbnail')
                ? kmnft_get_remote_thumbnail($original_url, $token_id)
                : $original_url;

            ?>
            <div class="gallery-item aspect-square rounded-lg overflow-hidden border border-gray-800 hover:border-kmnft-green transition cursor-pointer relative group"
                onclick="openImageModal('<?php echo $original_url; ?>')">
                <img src="<?php echo $thumb_url; ?>" alt="Token <?php echo esc_attr($token_id); ?>"
                    class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-300" loading="lazy"
                    onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';this.style.opacity='0.5';">
                <div
                    class="absolute bottom-0 inset-x-0 bg-black/60 p-2 translate-y-full group-hover:translate-y-0 transition duration-300">
                    <span class="text-xs font-mono text-white block text-center">#<?php echo esc_html($token_id); ?></span>
                </div>
            </div>
            <?php
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $has_more
        ));
    }

    public function handle_upload_user_icon()
    {
        // 1. Verify Nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'kmnft_dashboard_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce.'));
        }

        // 2. Check User Login
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Not logged in.'));
        }

        $user_id = get_current_user_id();

        // 3. Check File
        if (empty($_FILES['file']) || $_FILES['file']['error'] !== 0) {
            wp_send_json_error(array('message' => 'File upload error.'));
        }

        $file = $_FILES['file'];

        // 4. Validate File Type (strictly images)
        $allowed_types = array('image/jpeg', 'image/png', 'image/jpg');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(array('message' => 'Only JPG and PNG allowed.'));
        }

        // 5. Handle Upload (Use WordPress Media Handler)
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $file_path = $movefile['file'];
            $file_url = $movefile['url'];
            $file_type = $movefile['type'];

            // 6. Resize / Compress Image
            $image_editor = wp_get_image_editor($file_path);
            if (!is_wp_error($image_editor)) {
                // Resize to max 400x400
                $image_editor->resize(400, 400, false); // false = no crop (maintain aspect ratio)

                // Set quality
                $image_editor->set_quality(80);

                // Save back to the same path
                $saved_image = $image_editor->save($file_path);

                if (!is_wp_error($saved_image)) {
                    // Update meta with NEW URL (might have changed ext if converted, though rare here)
                    // Usually save retains the name unless specified otherwise.
                } else {
                    // Log error but proceed with original if save failed? 
                    // Better to fail or warn? We'll assume best effort.
                    error_log('KMNFT Image Resize Error: ' . $saved_image->get_error_message());
                }
            } else {
                error_log('KMNFT Image Editor Error: ' . $image_editor->get_error_message());
            }

            // 7. Save to User Meta
            // We store the full URL. 
            // NOTE: For better management, one might use attachment ID, but direct URL is simple and requested.
            // Delete old avatar if needed? For now, we just overwrite the meta.
            update_user_meta($user_id, 'kmnft_user_avatar_url', $file_url);

            wp_send_json_success(array(
                'message' => 'Upload successful',
                'url' => $file_url
            ));

        } else {
            wp_send_json_error(array('message' => $movefile['error']));
        }
    }
}
