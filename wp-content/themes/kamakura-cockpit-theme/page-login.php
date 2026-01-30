<?php
/**
 * Template Name: KMNFT Login
 */

// If already logged in, redirect to dashboard.
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard'));
    exit;
}

$error_msg = '';
$success_msg = '';
$view = isset($_GET['view']) ? $_GET['view'] : 'default'; // default, forgot, reset

// --- LOGIC HANDLERS ---

// 1. Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kmnft_login'])) {
    $creds = array(
        'user_login' => sanitize_text_field($_POST['email']),
        'user_password' => $_POST['password'],
        'remember' => isset($_POST['remember']),
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $error_msg = $user->get_error_message();
    } else {
        wp_redirect(home_url('/dashboard'));
        exit;
    }
}

// 2. Forgot Password (Request Link)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kmnft_forgot_password'])) {
    $email = sanitize_email($_POST['email']);
    if (email_exists($email)) {
        $user = get_user_by('email', $email);

        // Generate Token
        $token = wp_generate_password(32, false);
        $expiry = time() + 3600; // 1 hour

        update_user_meta($user->ID, 'kmnft_reset_token', $token);
        update_user_meta($user->ID, 'kmnft_reset_expiry', $expiry);

        // Build Link
        $reset_link = home_url('/login/?view=reset&email=' . urlencode($email) . '&token=' . $token);

        // Send Email
        $subject = '[KAMAKURA STADIUM NFT PORTAL(β)] Password Reset Request';
        $message = "You requested a password reset.\n\n";
        $message .= "Click the link below to reset your password (valid for 1 hour):\n";
        $message .= $reset_link . "\n\n";
        $message .= "If you did not request this, please ignore this email.";

        // Use wp_mail (Requires server setup)
        if (wp_mail($email, $subject, $message)) {
            $success_msg = 'Password reset link has been sent to your email.';
        } else {
            // Fallback for demo/local env where mail might fail
            $error_msg = 'Failed to send email. Please contact admin.';
            // For debugging: $success_msg = 'DEBUG LINK: ' . $reset_link;
        }
    } else {
        $error_msg = 'Email address not found.';
    }
}

// 3. Reset Password (Process New Password)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kmnft_reset_password'])) {
    $email = sanitize_email($_POST['email']);
    $token = sanitize_text_field($_POST['token']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass !== $confirm_pass) {
        $error_msg = 'Passwords do not match.';
    } else {
        $user = get_user_by('email', $email);
        if ($user) {
            $sw_token = get_user_meta($user->ID, 'kmnft_reset_token', true);
            $sw_expiry = get_user_meta($user->ID, 'kmnft_reset_expiry', true);

            if ($token === $sw_token && (int) $sw_expiry > time()) {
                wp_set_password($new_pass, $user->ID);
                delete_user_meta($user->ID, 'kmnft_reset_token');
                delete_user_meta($user->ID, 'kmnft_reset_expiry');
                $success_msg = 'Password has been reset. Please login.';
                $view = 'default'; // Switch back to login
            } else {
                $error_msg = 'Invalid or expired token.';
            }
        } else {
            $error_msg = 'Invalid request.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KAMAKURA STADIUM NFT PORTAL(β)</title>
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

<body class="h-screen w-full flex items-center justify-center relative overflow-hidden">

    <!-- Background Elements -->
    <div class="absolute inset-0 z-0">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login-bg.jpg" alt="Background"
            class="w-full h-full object-cover opacity-100">
        <div class="absolute inset-0 bg-gradient-to-b from-kmnft-navy/20 to-kmnft-black/60"></div>
    </div>
    <div
        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-kmnft-green to-transparent opacity-70">
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-md p-8 glass-panel rounded-xl shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-wider text-white mb-2">ACCESS PORTAL</h1>
            <p class="text-gray-400 text-sm tracking-widest uppercase">KAMAKURA STADIUM NFT PORTAL(β)</p>
        </div>

        <?php if ($error_msg): ?>
            <div class="mb-4 p-3 bg-red-900/50 border border-red-500 text-red-200 text-sm rounded">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_msg): ?>
            <div class="mb-4 p-3 bg-green-900/50 border border-green-500 text-green-200 text-sm rounded">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <!-- VIEW: DEFAULT (LOGIN) -->
        <?php if ($view === 'default'): ?>
            <form method="post" action="">
                <div class="mb-6">
                    <label for="email" class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Login
                        ID /
                        Email</label>
                    <input type="text" name="email" id="email" required class="input-field w-full p-3 rounded"
                        placeholder="k77xxxxx or email">
                </div>

                <div class="mb-8">
                    <label for="password"
                        class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" id="password" required class="input-field w-full p-3 rounded"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between mb-8 text-sm text-gray-400">
                    <label class="flex items-center hover:text-white cursor-pointer transition">
                        <input type="checkbox" name="remember"
                            class="mr-2 bg-black border-gray-600 rounded focus:ring-kmnft-green">
                        Remember Me
                    </label>
                </div>

                <button type="submit" name="kmnft_login"
                    class="w-full py-4 bg-kmnft-green text-black font-bold text-lg uppercase tracking-widest hover:bg-white hover:text-black transition duration-300 neon-glow rounded">
                    Connect
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?php echo home_url('/login/?view=forgot'); ?>"
                    class="text-xs text-gray-500 hover:text-kmnft-green transition">Forgot Access Code?</a>
            </div>

            <!-- VIEW: FORGOT PASSWORD -->
        <?php elseif ($view === 'forgot'): ?>
            <form method="post" action="">
                <input type="hidden" name="kmnft_forgot_password" value="1">
                <div class="mb-4 text-sm text-gray-300">
                    Enter your registered email address. We will send you a link to reset your password.
                </div>
                <div class="mb-6">
                    <label for="email" class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">
                        Registered Email</label>
                    <input type="email" name="email" id="email" required class="input-field w-full p-3 rounded"
                        placeholder="email@example.com">
                </div>

                <button type="submit"
                    class="w-full py-4 bg-white text-black font-bold text-lg uppercase tracking-widest hover:bg-kmnft-green hover:text-black transition duration-300 rounded">
                    Send Link
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?php echo home_url('/login'); ?>"
                    class="text-xs text-gray-500 hover:text-kmnft-green transition">Back to Login</a>
            </div>

            <!-- VIEW: RESET PASSWORD -->
        <?php elseif ($view === 'reset'): ?>
            <?php
            $req_email = isset($_GET['email']) ? $_GET['email'] : '';
            $req_token = isset($_GET['token']) ? $_GET['token'] : '';
            ?>
            <form method="post" action="">
                <input type="hidden" name="kmnft_reset_password" value="1">
                <input type="hidden" name="email" value="<?php echo esc_attr($req_email); ?>">
                <input type="hidden" name="token" value="<?php echo esc_attr($req_token); ?>">

                <div class="mb-4 text-sm text-gray-300">
                    Enter your new password below.
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">
                        New Password</label>
                    <input type="password" name="new_password" required class="input-field w-full p-3 rounded"
                        placeholder="••••••••">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-kmnft-green uppercase tracking-wider mb-2">
                        Confirm New Password</label>
                    <input type="password" name="confirm_password" required class="input-field w-full p-3 rounded"
                        placeholder="••••••••">
                </div>

                <button type="submit"
                    class="w-full py-4 bg-kmnft-green text-black font-bold text-lg uppercase tracking-widest hover:bg-white hover:text-black transition duration-300 neon-glow rounded">
                    Reset Password
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?php echo home_url('/login'); ?>"
                    class="text-xs text-gray-500 hover:text-kmnft-green transition">Back to Login</a>
            </div>

        <?php endif; ?>
    </div>

</body>

</html>