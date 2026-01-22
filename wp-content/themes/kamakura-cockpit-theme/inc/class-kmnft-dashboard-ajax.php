<?php
/**
 * Handles Dashboard AJAX interactions.
 */
class KMNFT_Dashboard_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_kmnft_upload_user_icon', array($this, 'handle_upload_user_icon'));
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
