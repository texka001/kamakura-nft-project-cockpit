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
$is_logged_in = is_user_logged_in();
if ($is_logged_in) {
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

            // Get CC Recipients from Settings
            $cc_recipients_option = get_option('kmnft_contact_cc_recipients', '');
            if (!empty($cc_recipients_option)) {
                $cc = preg_split('/[\r\n,]+/', $cc_recipients_option);
                $cc = array_map('trim', $cc);
                $cc = array_filter($cc, 'is_email');
                if (!empty($cc)) {
                    $headers[] = 'Cc: ' . implode(',', $cc);
                }
            }
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
                // --- Start Auto-reply to Sender ---
                $auto_reply_subject = '【KAMAKURA STADIUM NFT PORTAL】お問い合わせを受け付けました / Thank you for your inquiry';

                $auto_reply_body = "<html><body>";
                $auto_reply_body .= "<p>" . esc_html($name) . " 様</p>";
                $auto_reply_body .= "<p>KAMAKURA STADIUM NFT PORTAL(β)へのお問い合わせありがとうございます。<br>";
                $auto_reply_body .= "以下の内容でお問い合わせを受け付けました。返信まで今しばらくお待ちください。</p>";

                $auto_reply_body .= "<p>---</p>";

                $auto_reply_body .= "<p>Dear " . esc_html($name) . ",</p>";
                $auto_reply_body .= "<p>Thank you for contacting KAMAKURA STADIUM NFT PORTAL(β).<br>";
                $auto_reply_body .= "We have received your inquiry as follows. Please wait for our response.</p>";

                $auto_reply_body .= "<hr>";
                $auto_reply_body .= "<h3>[ お問い合わせ内容 / Inquiry Details ]</h3>";
                $auto_reply_body .= "<p><strong>件名 / Subject:</strong> " . esc_html($subject) . "</p>";
                $auto_reply_body .= "<p><strong>本文 / Message:</strong><br>" . nl2br(esc_html($message_content)) . "</p>";
                $auto_reply_body .= "<hr>";

                $auto_reply_body .= "<p>KAMAKURA STADIUM NFT PORTAL(β) 運営チーム</p>";
                $auto_reply_body .= "</body></html>";

                $auto_reply_headers = array('Content-Type: text/html; charset=UTF-8');
                $auto_reply_headers[] = 'From: KAMAKURA STADIUM NFT PORTAL(β) <' . $admin_email . '>';
                $auto_reply_headers[] = 'Reply-To: ' . $to;

                wp_mail($email, $auto_reply_subject, $auto_reply_body, $auto_reply_headers);
                // --- End Auto-reply to Sender ---

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

        .glass-card {
            background: rgba(26, 31, 44, 0.4);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .neon-text {
            text-shadow: 0 0 5px rgba(0, 255, 65, 0.5);
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

<body class="min-h-screen flex flex-col">

    <!-- Navbar -->
    <header class="w-full h-16 glass-card flex items-center justify-between px-6 fixed top-0 z-50">
        <div class="flex items-center space-x-4">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="text-kmnft-green font-bold tracking-widest text-sm sm:text-lg hover:opacity-80 transition leading-tight">KAMAKURA STADIUM
                NFT PORTAL(β)</a>
        </div>

        <!-- PC ナビ -->
        <div class="hidden md:flex items-center space-x-4 ml-auto">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">DASHBOARD</a>
            <a href="<?php echo home_url('/points'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">POINTS</a>
            <a href="<?php echo home_url('/ranking'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">RANKING</a>
            <a href="<?php echo home_url('/contact'); ?>"
                class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs transition">CONTACT</a>
            <span class="text-xs text-gray-400">Welcome,
                <?php echo $is_logged_in ? esc_html($current_user->user_login) : 'Guest'; ?></span>
            <?php if ($is_logged_in): ?>
                <a href="<?php echo wp_logout_url(home_url('/dashboard')); ?>"
                    class="px-4 py-1 border border-white/50 text-white rounded text-xs hover:bg-white hover:text-black transition">LOGOUT</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login'); ?>"
                    class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs hover:bg-kmnft-green hover:text-black transition">LOGIN</a>
            <?php endif; ?>
        </div>

        <!-- ハンバーガーボタン（スマホのみ） -->
        <button id="kmnft-hamburger" class="md:hidden ml-auto flex flex-col justify-center items-center gap-[5px] w-10 h-10 border border-white/20 rounded-lg bg-transparent cursor-pointer transition" aria-label="メニュー" aria-expanded="false">
            <span class="kmnft-bar block w-5 h-[2px] bg-white rounded transition-all duration-300 origin-center"></span>
            <span class="kmnft-bar block w-5 h-[2px] bg-white rounded transition-all duration-300 origin-center"></span>
            <span class="kmnft-bar block w-5 h-[2px] bg-white rounded transition-all duration-300 origin-center"></span>
        </button>
    </header>

    <!-- モバイルドロワー -->
    <div id="kmnft-drawer" class="fixed top-16 right-0 h-[calc(100dvh-4rem)] w-72 bg-[#0d0d1a] border-l border-white/10 z-40 translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
        <nav class="flex flex-col p-6 gap-5">
            <span class="text-[10px] text-gray-500 uppercase tracking-widest mb-2">
                Welcome, <?php echo $is_logged_in ? esc_html($current_user->user_login) : 'Guest'; ?>
            </span>
            <a href="<?php echo home_url('/dashboard'); ?>" class="kmnft-drawer-link border border-gray-600 text-gray-300 text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:border-kmnft-green hover:text-kmnft-green transition">DASHBOARD</a>
            <a href="<?php echo home_url('/points'); ?>" class="kmnft-drawer-link border border-gray-600 text-gray-300 text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:border-kmnft-green hover:text-kmnft-green transition">POINTS</a>
            <a href="<?php echo home_url('/ranking'); ?>" class="kmnft-drawer-link border border-gray-600 text-gray-300 text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:border-kmnft-green hover:text-kmnft-green transition">RANKING</a>
            <a href="<?php echo home_url('/contact'); ?>" class="kmnft-drawer-link border border-kmnft-green text-kmnft-green text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:bg-kmnft-green hover:text-black transition">CONTACT</a>
            <?php if ($is_logged_in): ?>
                <a href="<?php echo wp_logout_url(home_url('/dashboard')); ?>" class="kmnft-drawer-link border border-white/40 text-white text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:bg-white hover:text-black transition mt-auto">LOGOUT</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login'); ?>" class="kmnft-drawer-link bg-kmnft-green text-black text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:opacity-80 transition">LOGIN</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- オーバーレイ -->
    <div id="kmnft-overlay" class="fixed inset-0 bg-black/60 z-30 hidden opacity-0 transition-opacity duration-300 md:hidden"></div>

    <script>
    (function() {
        const btn    = document.getElementById('kmnft-hamburger');
        const drawer = document.getElementById('kmnft-drawer');
        const overlay= document.getElementById('kmnft-overlay');
        const bars   = btn.querySelectorAll('.kmnft-bar');
        function openMenu() {
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            requestAnimationFrame(() => overlay.classList.add('opacity-100'));
            btn.setAttribute('aria-expanded', 'true');
            bars[0].style.transform = 'translateY(7px) rotate(45deg)';
            bars[1].style.opacity = '0';
            bars[2].style.transform = 'translateY(-7px) rotate(-45deg)';
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            drawer.classList.add('translate-x-full');
            overlay.classList.remove('opacity-100');
            setTimeout(() => overlay.classList.add('hidden'), 300);
            btn.setAttribute('aria-expanded', 'false');
            bars[0].style.transform = '';
            bars[1].style.opacity = '';
            bars[2].style.transform = '';
            document.body.style.overflow = '';
        }
        btn.addEventListener('click', () => btn.getAttribute('aria-expanded') === 'true' ? closeMenu() : openMenu());
        overlay.addEventListener('click', closeMenu);
        document.querySelectorAll('.kmnft-drawer-link').forEach(l => l.addEventListener('click', closeMenu));
    })();
    </script>



    <div class="flex-grow flex items-center justify-center relative py-10 px-4 pt-24">

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
        <div class="relative z-10 w-full max-w-lg p-6 md:p-10 glass-card rounded-xl shadow-2xl my-10">
            <div class="text-center mb-8">
                <h1 class="text-2xl md:text-3xl font-bold tracking-wider text-white mb-2 neon-text uppercase">CONTACT
                    SUPPORT</h1>
                <p class="text-gray-400 text-xs md:text-sm tracking-widest uppercase">KAMAKURA STADIUM NFT PORTAL(β)</p>
            </div>

            <?php if ($error_msg): ?>
                <div
                    class="mb-6 p-4 bg-red-900/50 border border-red-500 text-red-200 text-sm rounded flex items-start gap-2">
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
                        <input type="text" name="contact_name" id="contact_name" required
                            value="<?php echo esc_attr($name); ?>"
                            class="input-field w-full p-3 rounded text-sm placeholder-gray-500" placeholder="Your Name">
                    </div>

                    <div class="mb-5">
                        <label for="contact_email"
                            class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Email
                            Address</label>
                        <input type="email" name="contact_email" id="contact_email" required
                            value="<?php echo esc_attr($email); ?>"
                            class="input-field w-full p-3 rounded text-sm placeholder-gray-500"
                            placeholder="name@example.com">
                    </div>

                    <div class="mb-5">
                        <label for="contact_subject"
                            class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Subject</label>
                        <input type="text" name="contact_subject" id="contact_subject" required
                            value="<?php echo esc_attr($subject); ?>"
                            class="input-field w-full p-3 rounded text-sm placeholder-gray-500"
                            placeholder="How can we help?">
                    </div>

                    <div class="mb-8">
                        <label for="contact_message"
                            class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Message</label>
                        <textarea name="contact_message" id="contact_message" required rows="5"
                            class="input-field w-full p-3 rounded text-sm placeholder-gray-500"
                            placeholder="Please describe your inquiry..."><?php echo esc_textarea($message_content); ?></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-kmnft-green text-black font-bold text-lg uppercase tracking-widest hover:bg-white hover:text-black transition duration-300 rounded shadow-[0_0_15px_rgba(0,255,65,0.4)]">
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
<?php endif; ?>
</div>

</body>

</html>