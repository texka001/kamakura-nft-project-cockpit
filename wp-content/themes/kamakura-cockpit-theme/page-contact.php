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

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $subject = sanitize_text_field($_POST['subject']);
        $message_content = sanitize_textarea_field($_POST['message']);

        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
            $error_msg = 'All fields are required.';
        } elseif (!is_email($email)) {
            $error_msg = 'Invalid email address.';
        } else {
            // Send Email
            $admin_email = get_option('admin_email');
            $to = $admin_email;
            $headers = array('Content-Type: text/html; charset=UTF-8');
            // Use Admin Email as 'From' to avoid SPF/DKIM issues on production
            $headers[] = 'From: Kamakura Stadium NFT <' . $admin_email . '>';
            $headers[] = 'Reply-To: ' . $email;

            $email_subject = '[Contact Form] ' . $subject;

            $email_body = "<html><body>";
            $email_body .= "<h2>New Message from " . esc_html($name) . "</h2>";
            $email_body .= "<p><strong>Email:</strong> " . esc_html($email) . "</p>";
            $email_body .= "<p><strong>Subject:</strong> " . esc_html($subject) . "</p>";
            $email_body .= "<hr>";
            $email_body .= "<p>" . nl2br(esc_html($message_content)) . "</p>";
            $email_body .= "</body></html>";

            if (wp_mail($to, $email_subject, $email_body, $headers)) {
                $success_msg = 'Your message has been sent successfully.';
                // Reset fields
                $subject = '';
                $message_content = '';
            } else {
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
    <title>Contact - Kamakura Stadium NFT Cockpit</title>
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

<body class="min-h-screen w-full flex items-center justify-center relative overflow-hidden py-10 px-4">

    <!-- Background Elements -->
    <div class="absolute inset-0 z-0">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login-bg.jpg" alt="Background"
            class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-b from-kmnft-navy/40 to-kmnft-black/90"></div>
    </div>
    <div
        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-kmnft-green to-transparent opacity-70">
    </div>

    <!-- Contact Container -->
    <div class="relative z-10 w-full max-w-lg p-6 md:p-10 glass-panel rounded-xl shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold tracking-wider text-white mb-2">CONTACT SUPPORT</h1>
            <p class="text-gray-400 text-xs md:text-sm tracking-widest uppercase">Kamakura Stadium NFT Project</p>
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
                class="mb-6 p-4 bg-green-900/50 border border-green-500 text-green-200 text-sm rounded flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span>
                    <?php echo $success_msg; ?>
                </span>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('kmnft_contact_action'); ?>
            <input type="hidden" name="kmnft_contact_submit" value="1">

            <div class="mb-5">
                <label for="name"
                    class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Name</label>
                <input type="text" name="name" id="name" required value="<?php echo esc_attr($name); ?>"
                    class="input-field w-full p-3 rounded text-sm placeholder-gray-500" placeholder="Your Name">
            </div>

            <div class="mb-5">
                <label for="email" class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Email
                    Address</label>
                <input type="email" name="email" id="email" required value="<?php echo esc_attr($email); ?>"
                    class="input-field w-full p-3 rounded text-sm placeholder-gray-500" placeholder="name@example.com">
            </div>

            <div class="mb-5">
                <label for="subject"
                    class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Subject</label>
                <input type="text" name="subject" id="subject" required value="<?php echo esc_attr($subject); ?>"
                    class="input-field w-full p-3 rounded text-sm placeholder-gray-500" placeholder="How can we help?">
            </div>

            <div class="mb-8">
                <label for="message"
                    class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Message</label>
                <textarea name="message" id="message" required rows="5"
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
    </div>

</body>

</html>