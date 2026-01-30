<?php
/**
 * Template Name: KMNFT Contact
 */

$error_msg = '';
$success_msg = '';

// Default values
$name = '';
$email = '';
$subject = '';
$message_content = '';

// If logged in, pre-fill name and email
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $name = $current_user->display_name;
    $email = $current_user->user_email;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kmnft_contact_submit'])) {

    // Verify Nonce
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'kmnft_contact_action')) {

        $name = sanitize_text_field($_POST['contact_name']);
        $email = sanitize_email($_POST['contact_email']);
        $subject = sanitize_text_field($_POST['contact_subject']);
        $message_content = sanitize_textarea_field($_POST['contact_message']);

        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
            $error_msg = 'All fields are required.';
        } elseif (!is_email($email)) {
            $error_msg = 'Invalid email address.';
        } else {
            // Send Email
            $admin_email = get_option('admin_email');

            // Get Recipients from Settings
            $recipients_option = get_option('kmnft_contact_recipients', '');
            if (!empty($recipients_option)) {
                $to = preg_split('/[\r\n,]+/', $recipients_option);
                $to = array_map('trim', $to);
                $to = array_filter($to, 'is_email');
                if (empty($to)) {
                    $to = $admin_email; // Fallback if parsing failed
                } else {
                    $to = implode(',', $to);
                }
            } else {
                $to = $admin_email;
            }

            $headers = array('Content-Type: text/html; charset=UTF-8');
            // Use Admin Email as 'From' to avoid SPF/DKIM issues on production
            $headers[] = 'From: KAMAKURA STADIUM NFT PORTAL(β) <' . $admin_email . '>';
            $headers[] = 'Reply-To: ' . $email;

            // Get Subject Prefix from Settings
            $subject_prefix = get_option('kmnft_contact_subject_prefix', '[Contact Form]');
            $email_subject = $subject_prefix . ' ' . $subject;

            $email_body = "<html><body>";
            $email_body .= "<h2>New Message from " . esc_html($name) . "</h2>";
            $email_body .= "<p><strong>Email:</strong> " . esc_html($email) . "</p>";
            $email_body .= "<p><strong>Subject:</strong> " . esc_html($subject) . "</p>";
            $email_body .= "<hr>";
            $email_body .= "<p>" . nl2br(esc_html($message_content)) . "</p>";
            $email_body .= "</body></html>";

            $mail_result = wp_mail($to, $email_subject, $email_body, $headers);

            if ($mail_result) {
                $success_msg = 'Your message has been sent successfully.';
                // Reset fields
                $subject = '';
                $message_content = '';
            } else {
                // Log detailed error to server log only
                global $ts_mail_errors;
                global $phpmailer;
                if (isset($ts_mail_errors)) {
                    error_log('[KMNFT Contact] Mail Error: ' . print_r($ts_mail_errors, true));
                }
                if (isset($phpmailer) && isset($phpmailer->ErrorInfo)) {
                    error_log('[KMNFT Contact] PHPMailer Info: ' . $phpmailer->ErrorInfo);
                }
                $error_msg = 'Failed to send message. Please try again later.';
            }
        }
    } else {
        $error_msg = 'Security check failed. Please refresh and try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - KAMAKURA STADIUM NFT PORTAL(β)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'kmnft-black': '#0a0a12',
                        'kmnft-navy': '#1a1f2c',
                        'kmnft-green': '#00ff41',
                        'kmnft-gold': '#ffd700',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0a0a12;
            color: white;
            font-family: 'Inter', sans-serif;
        }

        .glass-panel {
            background: rgba(26, 31, 44, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .neon-glow {
            box-shadow: 0 0 10px rgba(0, 255, 65, 0.3);
        }

        .input-field {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #333;
            color: white;
            transition: all 0.3s ease;
        }

        .input-field:focus {
            border-color: #00ff41;
            outline: none;
            box-shadow: 0 0 8px rgba(0, 255, 65, 0.5);
        }
    </style>
</head>

<body class="min-h-screen w-full flex items-center justify-center relative py-10 px-4">

    <!-- Background Elements -->
    <div class="absolute inset-0 z-0">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login-bg.jpg" alt="Background"
            class="w-full h-full object-cover opacity-30 fixed">
        <div class="absolute inset-0 bg-gradient-to-b from-kmnft-navy/40 to-kmnft-black/90 fixed"></div>
    </div>
    <div
        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-kmnft-green to-transparent opacity-70 fixed">
    </div>

    <!-- Contact Container -->
    <div class="relative z-10 w-full max-w-lg p-6 md:p-10 glass-panel rounded-xl shadow-2xl my-10">
        <div class="text-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold tracking-wider text-white mb-2">CONTACT SUPPORT</h1>
            <p class="text-gray-400 text-xs md:text-sm tracking-widest uppercase">KAMAKURA STADIUM NFT PORTAL(β)</p>
        </div>

        <?php if ($error_msg): ?>
            <div class="mb-6 p-4 bg-red-900/50 border border-red-500 text-red-200 text-sm rounded flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <span>
                    <?php echo $error_msg; ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div
                class="mb-8 p-6 bg-green-900/30 border border-green-500/50 text-white text-center rounded-lg shadow-[0_0_15px_rgba(0,255,65,0.2)]">
                <div class="flex justify-center mb-4">
                    <div class="p-3 bg-green-500/20 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-kmnft-green" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-kmnft-green mb-2">Message Sent!</h3>
                <p class="text-gray-300 mb-6"><?php echo $success_msg; ?></p>

                <a href="<?php echo home_url(is_user_logged_in() ? '/dashboard' : '/'); ?>"
                    class="inline-block w-full py-3 px-6 bg-kmnft-green text-black font-bold text-sm uppercase tracking-widest hover:bg-white transition duration-300 rounded text-center">
                    Back to Dashboard
                </a>
            </div>
        <?php else: ?>

            <form method="post" action="<?php echo esc_url(get_permalink()); ?>">
                <?php wp_nonce_field('kmnft_contact_action'); ?>
                <input type="hidden" name="kmnft_contact_submit" value="1">

                <div class="mb-5">
                    <label for="contact_name"
                        class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Name</label>
                    <input type="text" name="contact_name" id="contact_name" required value="<?php echo esc_attr($name); ?>"
                        class="input-field w-full p-3 rounded text-sm placeholder-gray-500" placeholder="Your Name">
                </div>

                <div class="mb-5">
                    <label for="contact_email"
                        class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Email
                        Address</label>
                    <input type="email" name="contact_email" id="contact_email" required
                        value="<?php echo esc_attr($email); ?>"
                        class="input-field w-full p-3 rounded text-sm placeholder-gray-500" placeholder="name@example.com">
                </div>

                <div class="mb-5">
                    <label for="contact_subject"
                        class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Subject</label>
                    <input type="text" name="contact_subject" id="contact_subject" required
                        value="<?php echo esc_attr($subject); ?>"
                        class="input-field w-full p-3 rounded text-sm placeholder-gray-500" placeholder="How can we help?">
                </div>

                <div class="mb-8">
                    <label for="contact_message"
                        class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Message</label>
                    <textarea name="contact_message" id="contact_message" required rows="5"
                        class="input-field w-full p-3 rounded text-sm placeholder-gray-500"
                        placeholder="Please describe your inquiry..."><?php echo esc_textarea($message_content); ?></textarea>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-kmnft-green text-black font-bold text-lg uppercase tracking-widest hover:bg-white hover:text-black transition duration-300 neon-glow rounded">
                    Send Message
                </button>
            </form>

            <div class="mt-8 text-center border-t border-gray-700/50 pt-6">
                <a href="<?php echo home_url(is_user_logged_in() ? '/dashboard' : '/login'); ?>"
                    class="text-xs text-gray-500 hover:text-kmnft-green transition flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to
                        <?php echo is_user_logged_in() ? 'Dashboard' : 'Login'; ?>
                    </span>
                </a>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>