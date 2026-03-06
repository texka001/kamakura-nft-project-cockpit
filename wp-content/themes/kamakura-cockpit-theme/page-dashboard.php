<?php
/**
 * Template Name: KMNFT Dashboard
 */

$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;
global $wpdb;

// Initialize variables
$rank = 'GUEST';
$holdings = array();
$ksp_balance = '-';
$ksp_total_val = 0; // Initialize to avoid undefined variable error for guests
$ksp_by_season = array();
$tokens_ksp_summary = array();
$latest_rank = 0;
$avatar_url = ''; // Will handle default below
$tokens_ksp_history = array(); // Initialize to avoid undefined variable error
$latest_season_label = ''; // Initialize to avoid undefined variable error

if ($is_logged_in) {
    // Fetch Extended Data
    $user_meta = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}kmnft_user_meta WHERE user_id = %d", $current_user->ID));
    $rank = $user_meta ? $user_meta->rank_current : 'STARTER';

    // Fetch Holdings
    $holdings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}kmnft_holdings WHERE user_id = %d", $current_user->ID));

    // Fetch Annual KSP Summary (from aggregate table)
    if (class_exists('KMNFT_User_Manager')) {
        $kmnft_manager = new KMNFT_User_Manager();
        $ksp_by_season = $kmnft_manager->get_user_ksp_summary($current_user->ID);

        if (!empty($ksp_by_season)) {
            // Latest season is always first due to ORDER BY season DESC
            $ksp_total_val = intval($ksp_by_season[0]->total_points);
            $latest_rank = intval($ksp_by_season[0]->rank);
        }
    }

    $ksp_balance = number_format($ksp_total_val);

    // Fetch Token Level KSP Summary and History for Holdings
    if (!empty($holdings) && !empty($ksp_by_season)) {
        $latest_season_label = $ksp_by_season[0]->season;
        $token_ids = array_map(function ($h) {
            return $h->token_id;
        }, $holdings);
        $tokens_ksp_summary = $kmnft_manager->get_tokens_ksp_summary($token_ids, $latest_season_label);
        $tokens_ksp_history = $kmnft_manager->get_tokens_ksp_history($token_ids);
    }
}

// Handle Password Change
$msg_password_change = '';
$msg_type = '';

if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kmnft_change_password'])) {
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'kmnft_change_password_nonce')) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
            $msg_password_change = 'All fields are required.';
            $msg_type = 'error';
        } elseif ($new_pass !== $confirm_pass) {
            $msg_password_change = 'New passwords do not match.';
            $msg_type = 'error';
        } elseif (!wp_check_password($current_pass, $current_user->data->user_pass, $current_user->ID)) {
            $msg_password_change = 'Current password is incorrect.';
            $msg_type = 'error';
        } else {
            wp_set_password($new_pass, $current_user->ID);
            wp_set_auth_cookie($current_user->ID); // Re-login user after password change
            $msg_password_change = 'Password changed successfully.';
            $msg_type = 'success';
            // Re-fetch user to be safe, though ID implies same object mostly
            $current_user = wp_get_current_user();
        }
    } else {
        $msg_password_change = 'Security check failed.';
        $msg_type = 'error';
    }
}

// Fetch Match Results (Public)
$match_results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}kmnft_match_results ORDER BY match_date DESC");

// Fetch Gallery Tokens (Random selection for display)
// Fetch Gallery Tokens (Random selection for display) only if not logged in
$gallery_loop = array();
if (!$is_logged_in) {
    $gallery_tokens = $wpdb->get_col("SELECT DISTINCT token_id FROM {$wpdb->prefix}kmnft_holdings ORDER BY RAND() LIMIT 15");
    if (empty($gallery_tokens)) {
        // Fallback if no holdings found
        $gallery_tokens = range(1, 15);
    }
    // Duplicate for infinite scroll loop
    $gallery_loop = array_merge($gallery_tokens, $gallery_tokens);
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kamakura Stadium NFT PORTAL(β)</title>
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
            color: #e2e8f0;
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

        .cyber-border {
            border-left: 2px solid #00ff41;
        }

        /* Gallery Marquee */
        .marquee-mask {
            mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        }

        .marquee-track {
            display: flex;
            gap: 1rem;
            width: max-content;
            animation: marquee-scroll 40s linear infinite;
        }

        .marquee-track:hover {
            animation-play-state: paused;
        }

        @keyframes marquee-scroll {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        /* Custom Scrollbar for Sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 255, 65, 0.3);
            border-radius: 2px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 255, 65, 0.5);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <!-- Navbar -->
    <header class="w-full h-16 glass-card flex items-center justify-between px-6 fixed top-0 z-50">
        <div class="flex items-center space-x-4">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="text-kmnft-green font-bold tracking-widest text-lg hover:opacity-80 transition">KAMAKURA STADIUM
                NFT PORTAL(β)</a>
        </div>
        <div class="flex items-center space-x-4 ml-auto">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs transition">DASHBOARD</a>
            <?php if ($is_logged_in): ?>
                <a href="<?php echo home_url('/points'); ?>"
                    class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">POINTS</a>
                <a href="<?php echo home_url('/ranking'); ?>"
                    class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">RANKING</a>
            <?php endif; ?>
            <a href="<?php echo home_url('/contact'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">CONTACT</a>
            <span class="text-xs text-gray-400 hidden sm:inline">Welcome,
                <?php echo $is_logged_in ? esc_html($current_user->user_login) : 'Guest'; ?></span>
            <?php if ($is_logged_in): ?>
                <a href="<?php echo wp_logout_url(home_url('/dashboard')); ?>"
                    class="px-4 py-1 border border-white/50 text-white rounded text-xs hover:bg-white hover:text-black transition">LOGOUT</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login'); ?>"
                    class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs hover:bg-kmnft-green hover:text-black transition">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Board -->
    <main class="flex-grow pt-24 px-6 pb-10 max-w-7xl mx-auto w-full grid grid-cols-1 md:grid-cols-12 gap-6">


        <!-- Left Col: Stats (4 cols) -->
        <div id="dashboard-sidebar" class="md:col-span-4 space-y-6 md:sticky md:self-start">
            <!-- Project Logo Module -->
            <!-- Project Logo Module -->
            <div class="glass-card p-0 rounded-lg flex items-center justify-center relative overflow-hidden h-28">
                <div class="absolute inset-0 bg-kmnft-green/5 blur-xl"></div>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg"
                    alt="Project Creative Logo"
                    class="w-full h-full object-cover object-center relative z-10 opacity-90 hover:opacity-100 transition duration-500">
            </div>

            <!-- Game Guide Module -->
            <a href="https://kamakura-stadium-nft.com/shootzone/" target="_blank" class="block group">
                <div
                    class="glass-card p-4 rounded-lg relative overflow-hidden flex items-center justify-between border border-kmnft-green/30 hover:bg-kmnft-green/10 transition duration-300">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-kmnft-green/20 flex items-center justify-center text-kmnft-green group-hover:scale-110 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white group-hover:text-kmnft-green transition">GAME GUIDE
                            </h3>
                            <p class="text-[10px] text-gray-400">詳しい遊び方はこちら</p>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-gray-500 group-hover:text-white group-hover:translate-x-1 transition"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- KSP Module -->
            <div class="glass-card p-6 rounded-lg relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-kmnft-green opacity-10 rounded-full blur-xl group-hover:opacity-20 transition">
                </div>
                <h3 class="text-xs text-gray-500 uppercase tracking-widest mb-1">KSP Status</h3>

                <?php
                $latest_season = (!empty($ksp_by_season) && count($ksp_by_season) > 0) ? $ksp_by_season[0] : null;
                ?>

                <?php if ($latest_season): ?>
                    <div class="text-[10px] text-kmnft-green font-bold uppercase tracking-wider mb-1 mt-4">
                        <?php echo esc_html($latest_season->season); ?> Season
                    </div>
                <?php endif; ?>

                <div class="flex justify-between items-end <?php echo $latest_season ? 'mb-0' : ''; ?>">
                    <a href="<?php echo home_url('/points'); ?>" class="group/ksp block hover:opacity-80 transition">
                        <div
                            class="text-4xl font-bold text-white neon-text leading-none group-hover/ksp:text-kmnft-green transition-colors">
                            <?php echo number_format($ksp_total_val); ?><span
                                class="text-xs ml-1 text-gray-400 font-normal">pt</span>
                        </div>
                    </a>
                    <?php if ($latest_season): ?>
                        <div class="text-right">
                            <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">
                                Rank
                            </div>
                            <div class="text-xl font-mono text-kmnft-gold leading-none">
                                <?php echo $latest_season->rank > 0 ? esc_html($latest_season->rank) : '-'; ?><span
                                    class="text-[10px] ml-0.5">位</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($latest_season): ?>
                    <?php if (count($ksp_by_season) > 1): ?>
                        <div class="mt-4 pt-2 border-t border-white/10">
                            <details class="group/details">
                                <summary
                                    class="cursor-pointer text-xs text-gray-500 hover:text-white transition flex items-center gap-1 list-none outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-3 w-3 transform group-open/details:rotate-90 transition" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    Past Seasons
                                </summary>
                                <div class="mt-2 pl-4 space-y-1 border-l border-white/10 ml-1.5">
                                    <?php for ($i = 1; $i < count($ksp_by_season); $i++):
                                        $season_data = $ksp_by_season[$i];
                                        ?>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-400"><?php echo esc_html($season_data->season); ?></span>
                                            <span
                                                class="text-gray-300 font-mono"><?php echo number_format($season_data->total_points); ?>
                                                pt</span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </details>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-xs text-kmnft-green mt-2 flex items-center">
                        <span class="w-2 h-2 bg-kmnft-green rounded-full mr-2 animate-pulse"></span>
                        ACTIVE
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile / Rank -->
            <div class="glass-card p-4 rounded-lg relative">
                <h3 class="text-[10px] text-gray-500 uppercase tracking-widest mb-3">Owner Profile</h3>

                <!-- Avatar Upload Section -->
                <?php
                // Display Password Change Message if any
                if (!empty($msg_password_change)) {
                    $color_class = $msg_type === 'success' ? 'text-green-400' : 'text-red-400';
                    echo '<div class="mb-4 text-xs ' . $color_class . ' border border-white/20 p-2 rounded">' .
                        esc_html($msg_password_change) . '</div>';
                }

                if ($is_logged_in) {
                    $avatar_url = get_user_meta($current_user->ID, 'kmnft_user_avatar_url', true);
                    if (!$avatar_url) {
                        $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($current_user->display_name) .
                            '&background=00ff41&color=0a0a12';
                    }
                } else {
                    $avatar_url = 'https://ui-avatars.com/api/?name=Guest&background=333&color=fff';
                }
                ?>
                <div class="absolute top-4 right-4 group">
                    <?php if ($is_logged_in): ?>
                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-kmnft-gold cursor-pointer relative"
                            onclick="openIconSelectionModal()">
                            <img id="user-avatar-img" src="<?php echo esc_url($avatar_url); ?>" alt="User Avatar"
                                class="w-full h-full object-cover">
                            <div
                                class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center text-[8px] text-white text-center">
                                CHANGE
                            </div>
                        </div>
                        <!-- Spinner -->
                        <div id="avatar-spinner"
                            class="hidden absolute top-0 left-0 w-12 h-12 rounded-full bg-black/60 flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-kmnft-green" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                        <input type="file" id="user-icon-input" class="hidden" accept="image/png, image/jpeg"
                            onchange="uploadUserIcon(this)">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-600 relative">
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="Guest Avatar"
                                class="w-full h-full object-cover opacity-50">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-3">

                    <div>
                        <div class="text-[10px] text-gray-400">User ID</div>
                        <div class="text-base font-bold text-white font-mono">
                            <?php echo $is_logged_in ? esc_html($current_user->user_login) : ' - '; ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400">Nickname</div>
                        <div class="text-base font-bold text-kmnft-gold">
                            <?php echo $is_logged_in ? esc_html($current_user->display_name) : 'Guest User'; ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400">Registered ID</div>
                        <?php if ($is_logged_in): ?>
                            <div class="text-xs font-mono">
                                <?php echo esc_html($current_user->user_email); ?>
                            </div>
                            <button onclick="openPasswordModal()"
                                class="mt-1 text-[9px] text-gray-500 hover:text-white underline transition">
                                Change Password
                            </button>
                        <?php else: ?>
                            <a href="<?php echo home_url('/login'); ?>"
                                class="inline-block mt-1 px-3 py-1 bg-kmnft-green text-black text-xs font-bold rounded hover:bg-white transition">LOGIN</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ground Map -->
            <!-- Ground Map -->
            <div class="glass-card p-6 rounded-lg cursor-zoom-in hover:border-kmnft-green transition"
                onclick="openMapModal()">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs text-gray-500 uppercase tracking-widest">My Asset Map <span
                            class="text-[10px] ml-1 text-kmnft-green opacity-70">(Click)</span></h3>
                    <!-- Legend -->
                    <div
                        class="flex items-center gap-2 px-2 py-1 bg-black/40 border border-gray-700 rounded text-[10px] text-gray-300 shadow-sm">
                        <span class="w-2 h-2 bg-kmnft-gold rounded-full shadow-[0_0_4px_#ffd700]"></span>
                        <span>Your Asset</span>
                    </div>
                </div>
                <!-- 102 x 64 Aspect Ratio with Padding -->
                <div class="w-full bg-[#1a4023] rounded-lg p-2 sm:p-3 border border-white/10">
                    <div class="relative w-full h-full" style="aspect-ratio: 102/64;">
                        <!-- Field Boundary Border -->
                        <div class="absolute inset-0 border border-white/30 pointer-events-none z-0 rounded"></div>

                        <!-- Ground Image -->
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/whiteLine.svg"
                            alt="Ground Map" class="w-full h-full object-cover opacity-100 relative z-0 rounded">

                        <!-- Plots -->
                        <?php foreach ($holdings as $holding): ?>
                            <?php if (is_numeric($holding->zone_x) && is_numeric($holding->zone_y)): ?>
                                <?php
                                // Clamp values (0-102, 0-64)
                                $x = max(0, min(102, floatval($holding->zone_x)));
                                $y = max(0, min(64, floatval($holding->zone_y)));

                                // Map directly to percentage (with -0.5m offset to center in 1m cell)
                                $left = (($x - 0.5) / 102) * 100;
                                $bottom = (($y - 0.5) / 64) * 100;

                                $token_ksp_data = isset($tokens_ksp_summary[$holding->token_id]) ? $tokens_ksp_summary[$holding->token_id] : null;
                                $pts = $token_ksp_data ? number_format($token_ksp_data->total_points) : '0';
                                $rnk = ($token_ksp_data && $token_ksp_data->rank > 0) ? $token_ksp_data->rank : '';
                                ?>
                                <div class="absolute w-1.5 h-1.5 bg-kmnft-gold rounded-full shadow-[0_0_4px_#ffd700] hover:scale-150 transition cursor-help z-10 -translate-x-1/2 translate-y-1/2"
                                    style="left: <?php echo $left; ?>%; bottom: <?php echo $bottom; ?>%;"
                                    onclick="openTokenModal('<?php echo esc_js($holding->token_id); ?>', '<?php echo esc_js($rnk); ?>', '<?php echo esc_js($pts); ?>', '<?php echo esc_js($x); ?>', '<?php echo esc_js($y); ?>', '<?php echo esc_js($latest_season_label); ?>')"
                                    title="ID: <?php echo esc_attr($holding->token_id); ?> (X:<?php echo $x; ?>, Y:<?php echo $y; ?>)">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="flex justify-between text-[10px] text-gray-500 mt-2 font-mono opacity-60">
                    <span>(0,0)</span>
                    <span>(102,64)</span>
                </div>
            </div>
        </div>

        <!-- Right Col: Assets & Content (8 cols) -->
        <div class="md:col-span-8 space-y-6">

            <!-- NFT Gallery Section -->
            <?php if (!$is_logged_in): ?>
                <div class="glass-card p-4 rounded-lg mb-6 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-4 px-2">
                        <h3 class="text-sm font-bold text-gray-300 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-kmnft-green" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            NFT GALLERY
                        </h3>
                        <div class="flex items-center gap-2">
                            <a href="<?php echo home_url('/nft-gallery'); ?>"
                                class="text-[10px] text-gray-400 hover:text-white transition flex items-center gap-1 group/link">
                                VIEW ALL
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-3 w-3 transform group-hover/link:translate-x-0.5 transition" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                            <span
                                class="text-[10px] text-gray-500 bg-gray-900 px-2 py-0.5 rounded border border-gray-700">LIVE</span>
                        </div>
                    </div>

                    <div class="marquee-mask w-full overflow-hidden relative">
                        <div class="marquee-track py-2">
                            <?php foreach ($gallery_loop as $g_token):
                                $g_original_url = KMNFT_IMAGE_BASE_URL . esc_attr($g_token) . '.png';
                                // Use cached thumbnail for display
                                $g_thumb_url = function_exists('kmnft_get_remote_thumbnail') ? kmnft_get_remote_thumbnail($g_original_url, $g_token) : $g_original_url;
                                ?>
                                <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden border border-gray-800 hover:border-kmnft-green transition cursor-pointer relative group/item"
                                    onclick="openImageModal('<?php echo $g_original_url; ?>')">
                                    <img src="<?php echo $g_thumb_url; ?>" alt="Token <?php echo esc_attr($g_token); ?>"
                                        class="w-full h-full object-cover opacity-80 group-hover/item:opacity-100 transition duration-300"
                                        loading="lazy"
                                        onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';this.style.opacity='0.5';">
                                    <div
                                        class="absolute bottom-0 inset-x-0 bg-black/60 p-1 text-center translate-y-full group-hover/item:translate-y-0 transition duration-300">
                                        <span class="text-[8px] font-mono text-white">#<?php echo esc_html($g_token); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- My Seat / Holdings -->
            <?php if ($is_logged_in): ?>
                <div class="glass-card p-6 rounded-lg">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <h2
                                class="text-lg font-bold text-white tracking-wide border-b border-kmnft-green pb-1 inline-block">
                                OWNED ASSETS</h2>
                            <div class="flex items-center gap-2">
                                <a href="https://kamakura-stadium-nft.com/" target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 border border-kmnft-green/50 text-kmnft-green text-[10px] font-bold rounded hover:bg-kmnft-green hover:text-black transition duration-200 group uppercase tracking-wider">
                                    <span>アセットを追加購入</span>
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-2.5 w-2.5 transform group-hover:translate-x-0.5 transition" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                                <?php if (count($holdings) > 5): ?>
                                    <button id="show-more-assets" onclick="showAllAssets()"
                                        class="px-3 py-1 border border-gray-600 text-gray-400 text-[10px] font-bold rounded hover:border-kmnft-green hover:text-kmnft-green transition uppercase tracking-wider">
                                        Show More
                                    </button>
                                    <button id="show-less-assets" onclick="showLessAssets()" style="display:none;"
                                        class="px-3 py-1 border border-red-900/50 text-red-500 text-[10px] font-bold rounded hover:bg-red-500 hover:text-white transition uppercase tracking-wider">
                                        Show Less
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">
                            <?php echo count($holdings); ?> ASSETS
                        </span>
                    </div>

                    <?php if ($holdings): ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="asset-grid">
                            <?php foreach ($holdings as $index => $holding): ?>
                                <?php
                                $is_hidden = $index >= 5 ? 'hidden hidden-asset' : '';
                                $image_url = KMNFT_IMAGE_BASE_URL . esc_attr($holding->token_id) . '.png';
                                $token_ksp_data = isset($tokens_ksp_summary[$holding->token_id]) ? $tokens_ksp_summary[$holding->token_id] : null;
                                $pts = $token_ksp_data ? number_format($token_ksp_data->total_points) : '0';
                                $rnk = ($token_ksp_data && $token_ksp_data->rank > 0) ? $token_ksp_data->rank : '';
                                $zx = isset($holding->zone_x) ? $holding->zone_x : '';
                                $zy = isset($holding->zone_y) ? $holding->zone_y : '';
                                ?>
                                <div class="asset-item group relative glass-card rounded-lg overflow-hidden border border-gray-800 hover:border-kmnft-green transition cursor-pointer <?php echo $is_hidden; ?>"
                                    onclick="openTokenModal('<?php echo esc_js($holding->token_id); ?>', '<?php echo esc_js($rnk); ?>', '<?php echo esc_js($pts); ?>', '<?php echo esc_js($zx); ?>', '<?php echo esc_js($zy); ?>', '<?php echo esc_js($latest_season_label); ?>')">
                                    <!-- Image -->
                                    <div class="aspect-square w-full bg-gray-900 relative">
                                        <img src="<?php echo $image_url; ?>" alt="Asset <?php echo esc_attr($holding->token_id); ?>"
                                            class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-500"
                                            loading="lazy">
                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent opacity-100">
                                        </div>
                                    </div>

                                    <!-- Overlay Info -->
                                    <div class="absolute bottom-0 left-0 w-full p-3">


                                        <?php
                                        $token_ksp_data = isset($tokens_ksp_summary[$holding->token_id]) ? $tokens_ksp_summary[$holding->token_id] : null;
                                        if ($token_ksp_data):
                                            ?>
                                            <div class="flex flex-col gap-1.5 mt-1 border-t border-white/10 pt-2">
                                                <div class="flex items-baseline justify-between">
                                                    <span class="text-[8px] text-gray-500 font-bold uppercase tracking-wider">KSP</span>
                                                    <div class="text-base font-mono font-bold text-white leading-none">
                                                        <?php echo number_format($token_ksp_data->total_points); ?><span
                                                            class="text-[9px] ml-0.5 text-gray-400 font-normal">pt</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-baseline justify-between">
                                                    <span
                                                        class="text-[8px] text-gray-500 font-bold uppercase tracking-wider">RANK</span>
                                                    <div class="text-base font-mono font-bold text-kmnft-gold leading-none">
                                                        <?php echo $token_ksp_data->rank > 0 ? '#' . esc_html($token_ksp_data->rank) : '-'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>



                    <?php else: ?>
                        <div class="text-center py-10 text-gray-500 text-sm">
                            No Assets Found. Please contact admin.
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>



            <!-- Match Highlights -->
            <?php if ($match_results): ?>
                <div class="glass-card p-6 rounded-lg mb-6">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-700 pb-2">
                        <h3 class="text-sm font-bold text-gray-300">LATEST MATCH RESULTS</h3>
                        <button onclick="toggleSection('latest-matches-content', 'matches-toggle-icon')"
                            class="text-gray-400 hover:text-white transition">
                            <svg id="matches-toggle-icon" xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transform transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="latest-matches-content">
                        <div class="space-y-6">
                            <?php foreach ($match_results as $index => $match): ?>
                                <?php
                                $is_hidden_match = $index >= 3 ? 'hidden hidden-match' : '';

                                $win_status = intval($match->is_win);
                                if ($win_status === 1) {
                                    // WIN
                                    $bgColor = 'bg-green-900/20';
                                    $borderColor = 'border-kmnft-green/30';
                                    $resultText = 'WIN';
                                    $resultTextColor = 'text-kmnft-green';
                                } elseif ($win_status === 2) {
                                    // DRAW
                                    $bgColor = 'bg-gray-800/40';
                                    $borderColor = 'border-gray-600';
                                    $resultText = 'DRAW';
                                    $resultTextColor = 'text-gray-300';
                                } else {
                                    // LOSE
                                    $bgColor = 'bg-gray-800/40';
                                    $borderColor = 'border-gray-700';
                                    $resultText = 'LOSE';
                                    $resultTextColor = 'text-red-500';
                                }

                                // Parse Token IDs
                                $goal_tokens = explode(',', $match->goal_token_ids);
                                $goal_tokens = array_map('trim', $goal_tokens);
                                $goal_tokens = array_filter($goal_tokens);

                                // Parse Video URLs
                                $goal_videos = !empty($match->goal_videos) ? explode("\n", $match->goal_videos) : array();
                                $goal_videos = array_map('trim', $goal_videos);
                                ?>
                                <div
                                    class="match-item <?php echo $is_hidden_match; ?> <?php echo $bgColor; ?> border <?php echo $borderColor; ?> rounded p-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <div class="text-xs text-gray-400 font-mono">
                                            <?php if (!empty($match->section_label)): ?>
                                                <span
                                                    class="mr-2 text-kmnft-gold"><?php echo esc_html($match->section_label); ?></span>
                                            <?php endif; ?>
                                            <?php echo esc_html($match->match_date); ?>
                                        </div>
                                        <div class="text-sm font-bold <?php echo $resultTextColor; ?>">
                                            <?php echo $resultText; ?>
                                            <?php echo esc_html($match->result_score); ?>
                                        </div>
                                    </div>
                                    <!-- Header Row for Alignment -->
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-2">
                                        <div class="md:col-span-4">
                                            <div class="text-sm text-white font-bold">VS
                                                <?php echo esc_html($match->opponent); ?>
                                            </div>
                                        </div>
                                        <div class="md:col-span-8 hidden md:block">
                                            <h4 class="text-xs font-bold text-gray-400">GOAL SCENES</h4>
                                        </div>
                                        <!-- Mobile only: Goal scenes title usually appears above images, but here we want to enforce structure using grid. 
                                             If on mobile we want standard flow: VS -> Map -> Goal Scenes Title -> Images.
                                             The grid above puts VS and Goal Scenes Title in one row on desktop.
                                             On mobile (grid-cols-1), it would be VS -> Goal Scenes Title -> Map -> Images.
                                             Wait, if I use grid-cols-1 on mobile for the header row:
                                             1. VS
                                             2. Goal Scenes Title
                                             Then next grid:
                                             3. Map
                                             4. Images
                                             This order: VS -> Goal Scenes Title -> Map -> Images seems wrong for mobile.
                                             Usually mobile: VS -> Map -> Goal Scenes Title -> Images.
                                             
                                             To preserve mobile order VS -> Map -> Goal Scenes Title -> Images:
                                             We might need to duplicate the title or use flex/order, but simply hiding it on mobile in the header and showing it in the content column might be easier?
                                             Or better: Use `order` classes if they were in one big grid?
                                             The current structure separates them into chunks.
                                             
                                             Let's stick to the user request. "GOAL SCENESをVS テスト青山と同じ高さにいれて" (Put GOAL SCENES at the same height as VS Test Aoyama).
                                             "写真をグラウンドと同じ高さにして" (Make photos same height as ground).
                                             This implies side-by-side desktop view.
                                             
                                             For mobile, let's keep it simple.
                                             If I put them in a grid-cols-1 on match-item for header row:
                                             Col 1: VS
                                             Col 2: Goal Scenes
                                             It matches the "Same height" request for desktop.
                                             
                                             For mobile, the user probably doesn't mind the standard stacking.
                                             However, if Goal Scenes title is above Map on mobile, it might be confusing.
                                             
                                             Let's hide the "GOAL SCENES" in the header on mobile (`hidden md:block`) and keep a mobile-only title inside the image column?
                                             Or just accept the order. 
                                             If I do `hidden md:block` for the header title:
                                             Desktop: VS ... GOAL SCENES
                                             Mobile: VS
                                             
                                             Then in the content part:
                                             Desktop: Map ... Images
                                             Mobile: Map ... Images
                                             
                                             Where does the "GOAL SCENES" title go on mobile?
                                             I should add it back in the Images column but `md:hidden`.
                                        -->
                                    </div>

                                    <!-- Layout: Map + Images -->
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                        <!-- Mini Map Column (approx 33%) -->
                                        <div class="md:col-span-4">
                                            <div class="w-full bg-[#1a4023] rounded p-1.5 border border-white/10">
                                                <div class="relative w-full h-full" style="aspect-ratio: 102/64;">
                                                    <!-- Field Boundary -->
                                                    <div
                                                        class="absolute inset-0 border border-white/30 pointer-events-none z-0 rounded-sm">
                                                    </div>

                                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/whiteLine.svg"
                                                        alt="Ground Map"
                                                        class="w-full h-full object-cover opacity-100 relative z-0 rounded-sm">

                                                    <!-- Goal Plots -->
                                                    <!-- Goal Plots -->
                                                    <?php
                                                    // Parse Token IDs (Multiline format: one line per goal, multiple tokens per line comma-separated)
                                                    $goal_lines = array_filter(preg_split('/\r\n|\r|\n/', $match->goal_token_ids));
                                                    $all_goal_tokens_structured = array();

                                                    foreach ($goal_lines as $idx => $line) {
                                                        $tokens = explode(',', $line);
                                                        $tokens = array_map('trim', $tokens);
                                                        $tokens = array_filter($tokens);
                                                        if (!empty($tokens)) {
                                                            foreach ($tokens as $t) {
                                                                $all_goal_tokens_structured[] = array(
                                                                    'token_id' => $t,
                                                                    'goal_num' => $idx + 1
                                                                );
                                                            }
                                                        }
                                                    }

                                                    foreach ($all_goal_tokens_structured as $goal_data):
                                                        $token_id = $goal_data['token_id'];
                                                        $seq = $goal_data['goal_num'];

                                                        // Lookup coordinates
                                                        $coord_query = $wpdb->prepare("SELECT zone_x, zone_y FROM {$wpdb->prefix}kmnft_holdings WHERE token_id = %s", $token_id);
                                                        $coord = $wpdb->get_row($coord_query);

                                                        if ($coord && is_numeric($coord->zone_x) && is_numeric($coord->zone_y)) {
                                                            $x = max(0, min(102, floatval($coord->zone_x)));
                                                            $y = max(0, min(64, floatval($coord->zone_y)));

                                                            // Map directly (with -0.5m offset to center in 1m cell)
                                                            $left = (($x - 0.5) / 102) * 100;
                                                            $bottom = (($y - 0.5) / 64) * 100;
                                                            ?>
                                                            <?php
                                                            $video_url = isset($goal_videos[$seq - 1]) ? $goal_videos[$seq - 1] : '';
                                                            $clickable_class = ($is_logged_in && !empty($video_url)) ? 'cursor-pointer' : '';
                                                            $video_onclick = ($is_logged_in && !empty($video_url)) ? 'onclick="window.open(\'' . esc_js($video_url) . '\', \'_blank\'); return false;"' : '';
                                                            ?>
                                                            <div class="absolute w-4 h-4 bg-red-500 rounded-full border border-white flex items-center justify-center text-[8px] text-white font-bold z-10 -translate-x-1/2 translate-y-1/2 shadow-lg hover:scale-125 transition <?php echo $clickable_class; ?>"
                                                                style="left: <?php echo $left; ?>%; bottom: <?php echo $bottom; ?>%;"
                                                                <?php echo $video_onclick; ?>
                                                                title="<?php echo !empty($video_url) ? 'Watch Video' : 'Goal ' . $seq; ?> (ID: <?php echo esc_attr($token_id); ?>)">
                                                                <?php echo $seq; ?>
                                                            </div>
                                                        <?php } ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Goal Images Column (approx 67%) -->
                                        <div class="md:col-span-8">
                                            <h4 class="text-xs font-bold text-gray-400 mb-2 md:hidden">GOAL SCENES</h4>
                                            <div class="grid grid-cols-2 gap-2">
                                                <?php
                                                // Parse Goal Images (Multiline format: one line per goal, multiple images per line comma-separated)
                                                $goal_img_lines = array_filter(preg_split('/\r\n|\r|\n/', $match->goal_images));
                                                $all_goal_images_structured = array();

                                                foreach ($goal_img_lines as $idx => $line) {
                                                    $imgs = explode(',', $line);
                                                    $imgs = array_map('trim', $imgs);
                                                    $imgs = array_filter($imgs);
                                                    if (!empty($imgs)) {
                                                        foreach ($imgs as $img_url) {
                                                            $all_goal_images_structured[] = array(
                                                                'url' => $img_url,
                                                                'goal_num' => $idx + 1
                                                            );
                                                        }
                                                    }
                                                }

                                                $total_imgs = count($all_goal_images_structured);
                                                $display_limit = 4;
                                                ?>
                                                <?php foreach ($all_goal_images_structured as $idx => $img_data): ?>
                                                    <?php
                                                    $is_hidden = $idx >= $display_limit;
                                                    $url = $img_data['url'];
                                                    $goal_num_for_img = $img_data['goal_num'];

                                                    // Associate video by goal index if possible (1st goal line -> 1st video line)
                                                    // idx here is image index, we want goal index
                                                    $goal_idx = $img_data['goal_num'] - 1;
                                                    $video_url = isset($goal_videos[$goal_idx]) ? $goal_videos[$goal_idx] : '';
                                                    $clickable_class = ($is_logged_in && !empty($video_url)) ? 'cursor-pointer' : 'pointer-events-none';
                                                    $video_onclick = ($is_logged_in && !empty($video_url)) ? 'onclick="window.open(\'' . esc_js($video_url) . '\', \'_blank\');"' : '';
                                                    ?>
                                                    <div
                                                        class="relative group aspect-video rounded overflow-hidden border border-white/10 <?php echo $is_hidden ? 'hidden extra-images-' . $match->id : ''; ?>">
                                                        <!-- Sequence Number Badge -->
                                                        <div class="absolute top-1 left-1 w-4 h-4 bg-red-500 rounded-full border border-white flex items-center justify-center text-[9px] text-white font-bold z-10 shadow-md <?php echo $clickable_class; ?>"
                                                            <?php echo $video_onclick; ?>
                                                            title="<?php echo !empty($video_url) ? 'Watch Video' : ''; ?>">
                                                            <?php echo $goal_num_for_img; ?>
                                                        </div>
                                                        <?php
                                                        $video_url_param = ($is_logged_in && !empty($video_url)) ? esc_js($video_url) : '';
                                                        $image_onclick = "openGoalScene(this.src, '$video_url_param')";
                                                        ?>
                                                        <img src="<?php echo esc_url(trim($url)); ?>"
                                                            class="w-full h-full object-cover cursor-pointer hover:scale-110 transition duration-500"
                                                            onclick="<?php echo $image_onclick; ?>" alt="Goal Scene">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if ($total_imgs > $display_limit): ?>
                                                <button
                                                    onclick="document.querySelectorAll('.extra-images-<?php echo $match->id; ?>').forEach(el => el.classList.remove('hidden')); this.style.display='none';"
                                                    class="text-[10px] text-kmnft-green hover:underline mt-2">
                                                    + Show <?php echo $total_imgs - $display_limit; ?> More
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($total_imgs === 0): ?>
                                                <p class="text-[10px] text-gray-500 italic">None</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Shoot Zone Prize Toggle & Content -->
                                    <?php if (!empty($match->shoot_prize_memo)): ?>
                                        <div class="flex justify-end mt-2 relative z-20">
                                            <button onclick="togglePrize('<?php echo $match->id; ?>')"
                                                class="text-[10px] text-kmnft-green hover:text-white border border-kmnft-green/50 hover:bg-kmnft-green/10 px-3 py-1 rounded transition flex items-center gap-1">
                                                <span>SHOOT ZONE PRIZE</span>
                                                <svg id="prize-icon-<?php echo $match->id; ?>" xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3 w-3 transform transition-transform duration-300" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div id="prize-memo-<?php echo $match->id; ?>"
                                            class="hidden mt-2 pt-2 border-t border-gray-700/50">
                                            <div class="text-xs text-gray-300 font-mono leading-relaxed bg-black/20 p-3 rounded">
                                                <?php echo nl2br(esc_html($match->shoot_prize_memo)); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($match_results) > 3): ?>
                            <div class="text-center mt-4">
                                <button id="show-more-matches" onclick="showAllMatches()"
                                    class="text-xs text-kmnft-green hover:text-white underline transition">
                                    Show All Matches
                                </button>
                                <button id="show-less-matches" onclick="showLessMatches()" style="display:none;"
                                    class="text-xs text-red-400 hover:text-white underline transition">
                                    Show Less Matches
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- League Standings Section -->
            <?php
            $standings_history = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}kmnft_standings ORDER BY announcement_date DESC");
            if ($standings_history):
                ?>
                <div class="glass-card p-6 rounded-lg mb-6">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-700 pb-2">
                        <h3 class="text-sm font-bold text-gray-300">LEAGUE STANDINGS</h3>
                        <button type="button" onclick="toggleSection('standings-content', 'standings-toggle-icon')"
                            class="text-gray-400 hover:text-white transition">
                            <svg id="standings-toggle-icon" xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transform transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="standings-content">
                        <div class="space-y-12">
                            <?php foreach ($standings_history as $index => $standing):
                                $is_hidden_history = $index >= 1 ? 'hidden extra-standings' : '';
                                ?>
                                <div class="<?php echo $is_hidden_history; ?>">
                                    <?php
                                    $standings_data = !empty($standing->data) ? json_decode($standing->data, true) : null;
                                    ?>
                                    <div class="space-y-6">
                                        <!-- Table or Image Section -->
                                        <div class="space-y-4">
                                            <div
                                                class="text-sm font-bold text-kmnft-green tracking-wider flex items-center gap-2 mb-1">
                                                <?php if (!empty($standing->display_title)): ?>
                                                    <span class="w-1 h-3 bg-kmnft-green rounded-full"></span>
                                                    <?php echo esc_html($standing->display_title); ?>
                                                <?php endif; ?>
                                                <span
                                                    class="text-[10px] text-gray-500 font-normal <?php echo !empty($standing->display_title) ? 'ml-1' : ''; ?>">
                                                    Updated: <?php echo esc_html($standing->announcement_date); ?>
                                                </span>
                                            </div>

                                            <div
                                                class="glass-card p-3 rounded bg-kmnft-navy/50 border border-kmnft-green/30 shadow-[0_0_15px_rgba(57,255,20,0.05)] flex items-center justify-between gap-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-2 h-2 rounded-full bg-kmnft-green"></div>
                                                    <div class="text-[10px] text-gray-400 uppercase tracking-widest">My Club
                                                        Status</div>
                                                    <div class="text-[10px] text-gray-500 font-mono">Kamakura Intl. FC</div>
                                                </div>
                                                <div class="flex items-center gap-6">
                                                    <div class="flex items-baseline gap-1">
                                                        <span class="text-[10px] text-gray-500">RANK</span>
                                                        <span
                                                            class="text-xl font-bold text-white neon-text"><?php echo esc_html($standing->our_rank); ?></span>
                                                        <span class="text-[10px] text-gray-400 font-normal">th</span>
                                                    </div>
                                                    <div class="w-[1px] h-4 bg-gray-700"></div>
                                                    <div class="flex items-baseline gap-1">
                                                        <span class="text-[10px] text-gray-500">POINTS</span>
                                                        <span
                                                            class="text-xl font-bold text-kmnft-gold"><?php echo esc_html($standing->our_points); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="relative group rounded overflow-hidden border border-white/10 bg-black/20">
                                                <?php if ($standings_data): ?>
                                                    <div
                                                        class="overflow-x-auto max-h-[400px] scrollbar-thin scrollbar-thumb-kmnft-green/20 scrollbar-track-transparent">
                                                        <table class="w-full text-xs text-center border-collapse">
                                                            <thead
                                                                class="bg-black/60 text-gray-400 font-bold uppercase sticky top-0 backdrop-blur-md z-10 shadow-lg">
                                                                <tr>
                                                                    <th class="py-3 px-2">Rank</th>
                                                                    <th class="py-3 px-4 text-left">Club</th>
                                                                    <th class="py-3 px-2">PL</th>
                                                                    <th class="py-3 px-2">W</th>
                                                                    <th class="py-3 px-2">D</th>
                                                                    <th class="py-3 px-2">L</th>
                                                                    <th class="py-3 px-2">GD</th>
                                                                    <th class="py-3 px-2">Pts</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-gray-300">
                                                                <?php foreach ($standings_data as $row):
                                                                    // Logic for highlighting
                                                                    $is_kamakura = (strpos($row['clubname'], '鎌倉') !== false || stripos($row['clubname'], 'Kamakura') !== false);
                                                                    $row_bg = $is_kamakura ? 'bg-kmnft-green/10 border-l-2 border-kmnft-green' : 'border-b border-gray-800 hover:bg-white/5';
                                                                    $text_cls = $is_kamakura ? 'text-white font-bold' : '';
                                                                    ?>
                                                                    <tr
                                                                        class="<?php echo $row_bg . ' ' . $text_cls; ?> transition-colors duration-200">
                                                                        <td class="py-3 px-2"><?php echo esc_html($row['rank']); ?></td>
                                                                        <td
                                                                            class="py-3 px-4 text-left whitespace-nowrap flex items-center gap-2">
                                                                            <?php if ($is_kamakura): ?>
                                                                                <span
                                                                                    class="w-1.5 h-1.5 rounded-full bg-kmnft-green animate-pulse shadow-[0_0_8px_#39ff14]"></span>
                                                                            <?php endif; ?>
                                                                            <?php echo esc_html($row['clubname']); ?>
                                                                        </td>
                                                                        <td class="py-3 px-2 text-gray-500">
                                                                            <?php echo esc_html($row['pl']); ?>
                                                                        </td>
                                                                        <td class="py-3 px-2"><?php echo esc_html($row['w']); ?></td>
                                                                        <td class="py-3 px-2"><?php echo esc_html($row['d']); ?></td>
                                                                        <td class="py-3 px-2"><?php echo esc_html($row['l']); ?></td>
                                                                        <td class="py-3 px-2 font-mono">
                                                                            <?php echo esc_html($row['gd']); ?>
                                                                        </td>
                                                                        <td
                                                                            class="py-3 px-2 font-bold text-kmnft-gold text-sm shadow-black drop-shadow-md">
                                                                            <?php echo esc_html($row['pt']); ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php elseif (!empty($standing->image_url)): ?>
                                                    <img src="<?php echo esc_url($standing->image_url); ?>" alt="League Standings"
                                                        class="w-full h-auto object-contain cursor-pointer hover:scale-105 transition duration-500"
                                                        onclick="openImageModal(this.src)">
                                                <?php else: ?>
                                                    <div class="p-8 text-center text-gray-500 italic">No standings data available.
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($standing->memo)): ?>
                                                <div class="bg-black/20 p-4 rounded border border-gray-700/50">
                                                    <h4 class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Memo /
                                                        Analysis</h4>
                                                    <div class="text-xs text-gray-300 font-sans leading-relaxed">
                                                        <?php echo nl2br(esc_html($standing->memo)); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($standings_history) > 1): ?>
                            <div class="text-center mt-4">
                                <button id="show-more-standing-history"
                                    onclick="document.querySelectorAll('.extra-standings').forEach(el => el.classList.remove('hidden')); this.style.display='none';"
                                    class="text-xs text-kmnft-green hover:text-white underline transition">
                                    Show All History
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- League Schedule Section -->
            <?php
            $schedule_history = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}kmnft_league_schedule ORDER BY season_year DESC");
            if ($schedule_history):
                ?>
                <div class="glass-card p-6 rounded-lg mb-6">
                    <div class="flex justify-between items-center mb-4 border-b border-gray-700 pb-2">
                        <h3 class="text-sm font-bold text-gray-300">LEAGUE SCHEDULE / RESULTS</h3>
                        <button type="button" onclick="toggleSection('schedule-content', 'schedule-toggle-icon')"
                            class="text-gray-400 hover:text-white transition">
                            <svg id="schedule-toggle-icon" xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 transform transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                    <div id="schedule-content">
                        <div class="space-y-10">
                            <?php
                            $has_past_seasons = false;
                            foreach ($schedule_history as $index => $schedule):
                                $is_latest = ($index === 0);
                                if (!$is_latest)
                                    $has_past_seasons = true;

                                $schedule_data = !empty($schedule->data) ? json_decode($schedule->data, true) : null;
                                $stats = !empty($schedule->summary_stats) ? json_decode($schedule->summary_stats, true) : null;
                                $summary_text = ($stats && isset($stats['win'])) ? "{$stats['win']}W {$stats['lose']}L {$stats['draw']}D" : '';

                                // Process sorting and identify next match
                                $has_next_match = false;
                                if ($schedule_data) {
                                    $current_ts = current_time('timestamp');

                                    // Add timestamp to each row
                                    foreach ($schedule_data as &$row) {
                                        $date_str = trim($row['date']);
                                        $time_str = trim($row['time']);

                                        // Check if it has a 4-digit year or two separators (indicating full date)
                                        $has_year = (substr_count($date_str, '/') === 2 || substr_count($date_str, '-') === 2 || preg_match('/^\d{4}/', $date_str));

                                        if ($has_year) {
                                            $full_date_ts = strtotime("{$date_str} {$time_str}");
                                            $row['_ts'] = ($full_date_ts !== false) ? $full_date_ts : 0;
                                        } else {
                                            // Fallback to legacy format parsing (MM/DD or M/D)
                                            $dt_parts = explode('/', $date_str);
                                            if (count($dt_parts) === 2) {
                                                $month = str_pad($dt_parts[0], 2, '0', STR_PAD_LEFT);
                                                $day = str_pad($dt_parts[1], 2, '0', STR_PAD_LEFT);
                                                $year = $schedule->season_year;
                                                $full_date_str = "{$year}-{$month}-{$day} {$time_str}";
                                                $row['_ts'] = strtotime($full_date_str);
                                            } else {
                                                $row['_ts'] = 0;
                                            }
                                        }
                                    }
                                    unset($row);

                                    // Identify Next Match and Last Match
                                    $next_match_idx = -1;
                                    $last_match_idx = -1;
                                    $closest_next_diff = null;
                                    $closest_last_diff = null;

                                    foreach ($schedule_data as $idx => $row) {
                                        $diff = $row['_ts'] - $current_ts;
                                        if ($diff >= 0) {
                                            // Future match (including right now)
                                            if ($closest_next_diff === null || $diff < $closest_next_diff) {
                                                $closest_next_diff = $diff;
                                                $next_match_idx = $idx;
                                            }
                                        } else {
                                            // Past match
                                            $abs_diff = abs($diff);
                                            if ($closest_last_diff === null || $abs_diff < $closest_last_diff) {
                                                $closest_last_diff = $abs_diff;
                                                $last_match_idx = $idx;
                                            }
                                        }
                                    }

                                    // Mark matches
                                    if ($next_match_idx !== -1) {
                                        $schedule_data[$next_match_idx]['_is_next'] = true;
                                    }
                                    if ($last_match_idx !== -1) {
                                        $schedule_data[$last_match_idx]['_is_recent'] = true;
                                    }

                                    // Sort Ascending by Date (Timestamp)
                                    usort($schedule_data, function ($a, $b) {
                                        return $a['_ts'] - $b['_ts'];
                                    });
                                }

                                // Container classes
                                $container_class = '';
                                if (!$is_latest) {
                                    $container_class = 'past-season hidden';
                                }

                                // Accordion logic for past seasons
                                $header_class = 'flex items-center justify-between mb-3 px-1';
                                $content_class = '';
                                $content_style = '';
                                if (!$is_latest) {
                                    $header_class .= ' cursor-pointer hover:bg-white/5 p-2 rounded transition-colors';
                                    $content_class = 'hidden';
                                }
                                ?>
                                <div class="<?php echo $container_class; ?>">
                                    <div class="<?php echo $header_class; ?>" <?php if (!$is_latest): ?>onclick="toggleSeason(this)" <?php endif; ?>>
                                        <div class="flex items-center">
                                            <h4
                                                class="text-kmnft-green font-bold text-sm tracking-widest border-l-4 border-kmnft-green pl-2">
                                                SEASON <?php echo esc_html($schedule->season_year); ?>
                                            </h4>
                                            <?php if (!$is_latest): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4 ml-2 text-gray-400 transform transition-transform duration-200"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs font-mono text-gray-400">
                                            <?php echo esc_html($summary_text); ?>
                                        </div>
                                    </div>

                                    <?php if ($schedule_data): ?>
                                        <div
                                            class="<?php echo $content_class; ?> overflow-x-auto scrollbar-thin scrollbar-thumb-kmnft-green/20 scrollbar-track-transparent rounded bg-black/20 border border-white/5">
                                            <table class="w-full text-xs text-center border-collapse">
                                                <thead class="bg-black/40 text-gray-500 uppercase tracking-wider">
                                                    <tr>
                                                        <th class="py-2 px-3 text-left">Section</th>
                                                        <th class="py-2 px-2">Date</th>
                                                        <th class="py-2 px-2">Time</th>
                                                        <th class="py-2 px-3">Score</th>
                                                        <th class="py-2 px-4 text-left">Opponent</th>
                                                        <th class="py-2 px-4 text-left">STADIUM</th>
                                                        <th class="py-2 px-2">Result</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-gray-300 divide-y divide-gray-800/50">
                                                    <?php foreach ($schedule_data as $row):
                                                        $is_win = isset($row['is_win']) ? intval($row['is_win']) : -1;
                                                        $is_next = !empty($row['_is_next']);

                                                        $res_class = '';
                                                        $res_label = '-';
                                                        if ($is_win === 1) {
                                                            $res_class = 'text-kmnft-green font-bold';
                                                            $res_label = 'WIN';
                                                        } elseif ($is_win === 0) {
                                                            $res_class = 'text-red-500 font-bold';
                                                            $res_label = 'LOSE';
                                                        } elseif ($is_win === 2) {
                                                            $res_class = 'text-gray-400 font-bold';
                                                            $res_label = 'DRAW';
                                                        }

                                                        // Row Visibility for Latest Season
                                                        // Use hidden class instead of inline style
                                        
                                                        // Highlight style for next match
                                                        $row_classes = 'hover:bg-white/5 transition-colors';
                                                        $is_recent = !empty($row['_is_recent']);

                                                        if ($is_next) {
                                                            $row_classes = 'bg-kmnft-green/10 border-l-4 border-kmnft-green transition-colors';
                                                        } elseif ($is_recent) {
                                                            $row_classes = 'bg-white/5 border-l-4 border-gray-400 transition-colors';
                                                        }

                                                        // Apply hidden class for non-next/recent matches in latest season
                                                        $tr_class = $row_classes;
                                                        if ($is_latest && !$is_next && !$is_recent) {
                                                            $tr_class .= ' latest-season-hidden hidden';
                                                        }
                                                        ?>
                                                        <tr class="<?php echo $tr_class; ?>">
                                                            <td
                                                                class="py-3 px-3 text-left <?php echo ($is_next || $is_recent) ? '' : 'border-r border-white/5'; ?> text-kmnft-gold/80 font-mono">
                                                                <?php echo esc_html($row['section']); ?>
                                                                <?php if ($is_next): ?>
                                                                    <span
                                                                        class="text-kmnft-green text-[10px] font-bold ml-2 animate-pulse">NEXT</span>
                                                                <?php elseif ($is_recent): ?>
                                                                    <span class="text-gray-400 text-[10px] font-bold ml-2">LAST</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="py-3 px-2"><?php echo esc_html($row['date']); ?></td>
                                                            <td class="py-3 px-2 text-gray-500">
                                                                <?php echo esc_html($row['time']); ?>
                                                            </td>
                                                            <td class="py-3 px-3 font-mono font-bold tracking-wider">
                                                                <?php echo esc_html($row['score']); ?>
                                                            </td>
                                                            <td class="py-3 px-4 text-left font-medium">
                                                                <?php echo esc_html($row['opponent']); ?>
                                                            </td>
                                                            <td class="py-3 px-4 text-left text-gray-400">
                                                                <?php echo esc_html($row['location'] ?? '-'); ?>
                                                            </td>
                                                            <td class="py-3 px-2 <?php echo $res_class; ?>">
                                                                <?php echo $res_label; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if ($is_latest): ?>
                                            <div class="text-center mt-2">
                                                <button onclick="toggleLatestSeason()" id="btn-show-latest"
                                                    class="text-xs text-kmnft-green hover:text-white transition-colors border border-kmnft-green/50 px-3 py-1 rounded bg-black/20 hover:bg-black/40">
                                                    Show Full Schedule
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4 text-gray-500 italic text-xs">No schedule data available.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($has_past_seasons): ?>
                            <div class="text-center mt-8 pt-4 border-t border-white/10">
                                <button onclick="toggleHistory()" id="btn-show-history"
                                    class="text-xs text-gray-400 hover:text-white transition-colors flex items-center justify-center mx-auto space-x-1">
                                    <span>Previous Seasons</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <script>                function toggleSeason(header) { const content = header.nextElementSibling; const icon = header.querySelector('svg'); if (content && content.classList.contains('hidden')) { content.classList.remove('hidden'); if (icon) icon.style.transform = 'rotate(180deg)'; } else if (content) { content.classList.add('hidden'); if (icon) icon.style.transform = 'rotate(0deg)'; } }
                    function toggleLatestSeason() {
                        const hiddenRows = document.querySelectorAll('.latest-season-hidden'); const btn = document.getElementById('btn-show-latest');
                        hiddenRows.forEach(row => { row.classList.toggle('hidden'); });
                        if (btn) { if (btn.innerText.includes('Show Full Schedule')) { btn.innerText = 'Show Recent/Next Only'; } else { btn.innerText = 'Show Full Schedule'; } }
                    }
                    function toggleHistory() {
                        const pastSeasons = document.querySelectorAll('.past-season'); const btn = document.getElementById('btn-show-history');
                        pastSeasons.forEach(season => { season.classList.toggle('hidden'); });
                        if (btn) { const span = btn.querySelector('span'); const svg = btn.querySelector('svg'); if (span.innerText === 'Previous Seasons') { span.innerText = 'Hide Previous Seasons'; svg.style.transform = 'rotate(180deg)'; } else { span.innerText = 'Previous Seasons'; svg.style.transform = 'rotate(0deg)'; } }
                    }
                </script>
            <?php endif; ?>

            <!-- Quick Actions / News Placeholder -->


        </div>

    </main>

    <!-- Asset Detail Modal (Ported from Ranking) -->
    <div id="token-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/90 backdrop-blur-sm">
        <div
            class="glass-card max-w-2xl w-full rounded-2xl overflow-hidden relative animate-in zoom-in duration-300 shadow-2xl shadow-kmnft-green/20">
            <button onclick="closeTokenModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition z-10 w-8 h-8 flex items-center justify-center rounded-full bg-black/50 overflow-hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <!-- Navigation Arrows -->
            <button onclick="prevToken()"
                class="modal-nav-btn absolute left-2 top-1/2 -translate-y-1/2 z-20 p-1 text-white/30 hover:text-kmnft-green transition-all duration-300 hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 md:h-14 md:w-14" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button onclick="nextToken()"
                class="modal-nav-btn absolute right-2 top-1/2 -translate-y-1/2 z-20 p-1 text-white/30 hover:text-kmnft-green transition-all duration-300 hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 md:h-14 md:w-14" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <div class="flex flex-col md:flex-row h-full">
                <!-- Image Section -->
                <!-- Image Section -->
                <div class="w-full md:w-3/5 aspect-square relative group">
                    <img id="modal-token-image" src="" alt="Token NFT"
                        class="w-full h-full object-contain brightness-[1.1]">

                    <!-- Watch Video Button -->
                    <a id="modal-watch-video-btn" href="#" target="_blank"
                        class="hidden absolute bottom-8 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-2 rounded-full font-bold shadow-lg hover:bg-white hover:text-red-500 transition duration-300 flex items-center gap-2 z-20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>WATCH VIDEO</span>
                    </a>
                </div>
                <!-- Info Section -->
                <div
                    class="w-full md:w-2/5 p-6 flex flex-col justify-center border-t md:border-t-0 md:border-l border-white/10 bg-gray-900/60 overflow-hidden">
                    <div class="space-y-6">
                        <!-- Row 1: Token ID -->
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Token ID</div>
                            <div id="modal-token-id"
                                class="text-xl md:text-2xl font-bold font-mono text-white break-all">#000000</div>
                        </div>

                        <!-- Row 2: Coordinates (2 column grid) -->
                        <div id="modal-token-coord-container" class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">X-Coordinate</div>
                                <div id="modal-token-x" class="text-xl font-bold text-gray-300 font-mono">0</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Y-Coordinate</div>
                                <div id="modal-token-y" class="text-xl font-bold text-gray-300 font-mono">0</div>
                            </div>
                        </div>

                        <!-- Row 3: Total KSP -->
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Total KSP</div>
                            <div id="modal-token-points" class="text-3xl font-bold text-kmnft-gold font-mono">
                                0<span class="text-xs text-gray-500 ml-1">PT</span></div>
                        </div>

                        <!-- Row 4: Season Rank -->
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Season Rank</div>
                            <div id="modal-token-rank" class="text-3xl font-bold text-kmnft-green neon-text">#1</div>
                        </div>

                        <!-- Row 5: Season (Year) -->
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Season</div>
                            <select id="modal-token-season-select"
                                class="w-full bg-gray-800 border border-white/10 rounded px-2 py-1 text-sm font-bold text-gray-300 font-mono outline-none focus:border-kmnft-green transition"
                                onchange="updateModalSeasonData()">
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tap background to close -->
        <div class="absolute inset-0 -z-10" onclick="closeTokenModal()"></div>
    </div>

    <!-- Map Modal -->
    <div id="map-modal"
        class="fixed inset-0 z-[100] bg-black/90 hidden flex items-center justify-center p-4 backdrop-blur-sm"
        onclick="closeMapModal()">
        <div class="relative w-full max-w-6xl flex flex-col items-center" onclick="event.stopPropagation()">
            <button onclick="closeMapModal()"
                class="absolute -top-10 right-0 text-gray-400 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Large Map -->
            <div class="w-full bg-[#1a4023] rounded-lg p-3 sm:p-5 shadow-2xl shadow-kmnft-green/20">
                <div class="relative w-full h-full" style="aspect-ratio: 102/64;">
                    <!-- Field Boundary -->
                    <div class="absolute inset-0 border border-white/30 pointer-events-none z-0 rounded"></div>

                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/whiteLine.svg" alt="Ground Map"
                        class="w-full h-full object-cover relative z-0 rounded">

                    <!-- Plots (Large) -->
                    <?php foreach ($holdings as $holding): ?>
                        <?php if (is_numeric($holding->zone_x) && is_numeric($holding->zone_y)): ?>
                            <?php
                            $x = max(0, min(102, floatval($holding->zone_x)));
                            $y = max(0, min(64, floatval($holding->zone_y)));

                            // Map directly (with -0.5m offset to center in 1m cell)
                            $left = (($x - 0.5) / 102) * 100;
                            $bottom = (($y - 0.5) / 64) * 100;

                            $last4 = substr($holding->token_id, -4);
                            $image_url_large = KMNFT_IMAGE_BASE_URL . esc_attr($holding->token_id) . '.png';

                            $token_ksp_data = isset($tokens_ksp_summary[$holding->token_id]) ? $tokens_ksp_summary[$holding->token_id] : null;
                            $pts = $token_ksp_data ? number_format($token_ksp_data->total_points) : '0';
                            $rnk = ($token_ksp_data && $token_ksp_data->rank > 0) ? $token_ksp_data->rank : '';

                            // Tooltip Y Position
                            $tooltip_y_class = ($y > 32) ? 'top-full mt-2' : 'bottom-full mb-2';

                            // Tooltip X Position
                            if ($x > 100) {
                                // Right edge -> Align Right
                                $tooltip_x_class = 'right-0 translate-x-0';
                            } elseif ($x < 20) {
                                // Left edge -> Align Left
                                $tooltip_x_class = 'left-0 translate-x-0';
                            } else {
                                // Center
                                $tooltip_x_class = 'left-1/2 -translate-x-1/2';
                            }
                            ?>
                            <div class="absolute w-3 h-3 md:w-4 md:h-4 bg-kmnft-gold rounded-full shadow-[0_0_10px_#ffd700] hover:scale-150 transition cursor-help z-10 -translate-x-1/2 translate-y-1/2 group"
                                style="left: <?php echo $left; ?>%; bottom: <?php echo $bottom; ?>%;"
                                onclick="openTokenModal('<?php echo esc_js($holding->token_id); ?>', '<?php echo esc_js($rnk); ?>', '<?php echo esc_js($pts); ?>', '<?php echo esc_js($x); ?>', '<?php echo esc_js($y); ?>', '<?php echo esc_js($latest_season_label); ?>')">

                                <!-- Label (Last 4 Digits) -->
                                <span
                                    class="absolute top-full left-1/2 -translate-x-1/2 mt-1 text-[8px] md:text-[10px] text-white font-mono bg-black/50 px-1 rounded whitespace-nowrap pointer-events-none">
                                    <?php echo esc_html($last4); ?>
                                </span>

                                <!-- Tooltip -->
                                <div
                                    class="absolute <?php echo $tooltip_y_class . ' ' . $tooltip_x_class; ?> bg-black/90 border border-gray-700 text-white text-xs rounded p-2 opacity-0 group-hover:opacity-100 transition pointer-events-none z-20 flex flex-col items-center shadow-xl">
                                    <img src="<?php echo $image_url_large; ?>" alt="Asset"
                                        class="w-16 h-16 object-cover rounded mb-1 bg-gray-800">
                                    <div class="font-mono text-[10px] text-gray-300">ID:
                                        <?php echo esc_html($holding->token_id); ?>
                                    </div>
                                    <div class="font-mono text-[10px] text-kmnft-green whitespace-nowrap">(X:<?php echo $x; ?>,
                                        Y:<?php echo $y; ?>)
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="flex justify-between w-full text-xs text-gray-500 mt-2 font-mono">
                <span>(0,0)</span>
                <span>(102,64)</span>
            </div>
        </div>
    </div>

    <script>
        // Toggle function defined early and in separate block to avoid ReferenceError
        window.toggleSection = function (contentId, iconId) {
            const content = document.getElementById(contentId);
            const icon = document.getElementById(iconId);
            if (content) {
                content.classList.toggle('hidden');
            }
            if (icon) icon.classList.toggle('rotate-180');
        };
    </script>

    <?php
    $owned_assets_json = array();
    foreach ($holdings as $holding) {
        $token_id = $holding->token_id;
        $token_ksp_data = isset($tokens_ksp_summary[$token_id]) ? $tokens_ksp_summary[$token_id] : null;
        $owned_assets_json[] = array(
            'token_id' => $token_id,
            'rank' => ($token_ksp_data && $token_ksp_data->rank > 0) ? $token_ksp_data->rank : '',
            'points' => $token_ksp_data ? number_format($token_ksp_data->total_points) : '0',
            'x' => isset($holding->zone_x) ? $holding->zone_x : '',
            'y' => isset($holding->zone_y) ? $holding->zone_y : ''
        );
    }
    ?>

    <script>
        const imageBaseUrl = '<?php echo KMNFT_IMAGE_BASE_URL; ?>';
        const fallbackImage = '<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';
        const latestSeasonLabel = '<?php echo esc_js($latest_season_label); ?>';
        // Use safer JSON encoding options and ensure fallback
        const tokensHistory = <?php
        $json = json_encode(!empty($tokens_ksp_history) ? $tokens_ksp_history : new stdClass(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        echo $json ?: '{}';
        ?>;
        const ownedAssets = <?php echo json_encode($owned_assets_json); ?>;

        let currentTokenId = null;
        let currentTokenIndex = -1;

        function openTokenModal(tokenId, rank, points, x, y, season) {
            currentTokenId = tokenId;
            currentTokenIndex = ownedAssets.findIndex(a => a.token_id === tokenId);

            // Toggle navigation visibility
            const navButtons = document.querySelectorAll('.modal-nav-btn');
            if (currentTokenIndex !== -1 && ownedAssets.length > 1) {
                navButtons.forEach(btn => btn.classList.remove('hidden'));
            } else {
                navButtons.forEach(btn => btn.classList.add('hidden'));
            }

            document.getElementById('modal-token-id').textContent = '#' + tokenId;

            const seasonSelect = document.getElementById('modal-token-season-select');
            seasonSelect.innerHTML = '';
            const history = tokensHistory[tokenId] || [];

            if (history.length > 0) {
                history.forEach(h => {
                    const opt = document.createElement('option');
                    opt.value = h.season;
                    opt.innerText = h.season;
                    if (h.season === (season || latestSeasonLabel)) opt.selected = true;
                    seasonSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = (season || latestSeasonLabel) || '-';
                opt.innerText = (season || latestSeasonLabel) || '-';
                seasonSelect.appendChild(opt);
            }

            document.getElementById('modal-token-points').innerHTML = (points || '0') + '<span class="text-xs text-gray-500 ml-1">PT</span>';
            document.getElementById('modal-token-rank').textContent = rank ? '#' + rank : '-';

            const coordContainer = document.getElementById('modal-token-coord-container');
            if (x !== undefined && y !== undefined && x !== '' && y !== '') {
                document.getElementById('modal-token-x').textContent = x;
                document.getElementById('modal-token-y').textContent = y;
                coordContainer.classList.remove('hidden');
                coordContainer.classList.add('grid');
            } else {
                coordContainer.classList.add('hidden');
                coordContainer.classList.remove('grid');
            }

            const img = document.getElementById('modal-token-image');
            img.style.opacity = '1'; // Reset opacity
            img.src = imageBaseUrl + tokenId + '.png';
            img.onerror = function () {
                this.src = fallbackImage;
                this.style.opacity = '0.5';
            };

            const modal = document.getElementById('token-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function navigateToIndex(index) {
            if (index < 0) index = ownedAssets.length - 1;
            if (index >= ownedAssets.length) index = 0;

            const asset = ownedAssets[index];
            if (asset) {
                openTokenModal(asset.token_id, asset.rank, asset.points, asset.x, asset.y, latestSeasonLabel);
            }
        }

        function prevToken() {
            if (currentTokenIndex !== -1) {
                navigateToIndex(currentTokenIndex - 1);
            }
        }

        function nextToken() {
            if (currentTokenIndex !== -1) {
                navigateToIndex(currentTokenIndex + 1);
            }
        }

        // Keyboard Navigation
        document.addEventListener('keydown', (e) => {
            const modal = document.getElementById('token-modal');
            if (modal && !modal.classList.contains('hidden')) {
                if (e.key === 'ArrowLeft') prevToken();
                if (e.key === 'ArrowRight') nextToken();
                if (e.key === 'Escape') closeTokenModal();
            }
        });

        function updateModalSeasonData() {
            if (!currentTokenId) return;
            const selectedSeason = document.getElementById('modal-token-season-select').value;
            const history = tokensHistory[currentTokenId] || [];
            const data = history.find(h => h.season === selectedSeason);

            if (data) {
                document.getElementById('modal-token-points').innerHTML = data.points + '<span class="text-xs text-gray-500 ml-1">PT</span>';
                document.getElementById('modal-token-rank').textContent = (data.rank !== '-') ? '#' + data.rank : '-';
            }
        }

        function closeTokenModal() {
            const modal = document.getElementById('token-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            // Clear src after delay
            setTimeout(() => {
                document.getElementById('modal-token-image').src = '';
            }, 200);
        }

        // Keep openImageModal for backward compatibility and general images
        function openImageModal(url) {
            // If it's a token image URL, try to extract ID and show as token
            if (url.includes(imageBaseUrl)) {
                const tokenId = url.replace(imageBaseUrl, '').replace('.png', '');
                // We don't have rank/points here easily, but we can at least show the ID
                openTokenModal(tokenId, '-', '0', '', '', latestSeasonLabel);
                return;
            }

            // Otherwise, show as a simple image in the token modal but hide the info panel
            const modal = document.getElementById('token-modal');
            const img = document.getElementById('modal-token-image');
            img.src = url;

            // Hide the info section for non-token images
            const infoSection = document.querySelector('#token-modal .md\\:w-2\\/5');
            const imgSection = document.querySelector('#token-modal .md\\:w-3\\/5');
            if (infoSection && imgSection) {
                infoSection.classList.add('hidden');
                imgSection.classList.remove('md:w-3/5');
                imgSection.classList.add('w-full');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        // Modify closeTokenModal to restore layout
        const originalCloseTokenModal = closeTokenModal;
        closeTokenModal = function () {
            originalCloseTokenModal();
            const infoSection = document.querySelector('#token-modal .md\\:w-2\\/5');
            const imgSection = document.querySelector('#token-modal .md\\:w-3\\/5');
            const videoBtn = document.getElementById('modal-watch-video-btn');

            if (infoSection && imgSection) {
                infoSection.classList.remove('hidden');
                imgSection.classList.add('md:w-3/5');
                imgSection.classList.remove('w-full');
            }
            if (videoBtn) {
                videoBtn.classList.add('hidden');
                videoBtn.href = '#';
            }
        };

        function openGoalScene(imageUrl, videoUrl) {
            openImageModal(imageUrl);
            const btn = document.getElementById('modal-watch-video-btn');
            if (btn) {
                if (videoUrl) {
                    btn.href = videoUrl;
                    btn.classList.remove('hidden');
                } else {
                    btn.classList.add('hidden');
                }
            }
        }

        function openMapModal() {
            const modal = document.getElementById('map-modal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMapModal() {
            const modal = document.getElementById('map-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close on Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeTokenModal();
                closeMapModal();
                closePasswordModal();
            }
        });



        function showAllAssets() {
            const hiddenAssets = document.querySelectorAll('.asset-item.hidden-asset');
            hiddenAssets.forEach(el => {
                el.classList.remove('hidden');
            });
            const btnMore = document.getElementById('show-more-assets');
            const btnLess = document.getElementById('show-less-assets');
            if (btnMore) btnMore.style.display = 'none';
            if (btnLess) btnLess.style.display = 'inline-block';
        }

        function showLessAssets() {
            const allAssets = document.querySelectorAll('.asset-item');
            allAssets.forEach((el, index) => {
                if (index >= 5) {
                    el.classList.add('hidden');
                    el.classList.add('hidden-asset'); // Ensure marker class is kept
                }
            });
            const btnMore = document.getElementById('show-more-assets');
            const btnLess = document.getElementById('show-less-assets');
            if (btnMore) btnMore.style.display = 'inline-block';
            if (btnLess) btnLess.style.display = 'none';
        }

        function showAllMatches() {
            const hiddenMatches = document.querySelectorAll('.match-item.hidden-match');
            hiddenMatches.forEach(el => {
                el.classList.remove('hidden');
            });
            const btnMore = document.getElementById('show-more-matches');
            const btnLess = document.getElementById('show-less-matches');
            if (btnMore) btnMore.style.display = 'none';
            if (btnLess) btnLess.style.display = 'inline-block';
        }

        function showLessMatches() {
            const allMatches = document.querySelectorAll('.match-item');
            allMatches.forEach((el, index) => {
                if (index >= 3) {
                    el.classList.add('hidden');
                    el.classList.add('hidden-match');
                }
            });
            const btnMore = document.getElementById('show-more-matches');
            const btnLess = document.getElementById('show-less-matches');
            if (btnMore) btnMore.style.display = 'inline-block';
            if (btnLess) btnLess.style.display = 'none';
        }

        function togglePrize(id) {
            const el = document.getElementById('prize-memo-' + id);
            const icon = document.getElementById('prize-icon-' + id);
            if (el) el.classList.toggle('hidden');
            if (icon) icon.classList.toggle('rotate-180');
        }

        function openPasswordModal() {
            document.getElementById('password-modal').classList.remove('hidden');
        }

        function closePasswordModal() {
            document.getElementById('password-modal').classList.add('hidden');
        }

        // --- User Icon Upload ---
        const dashboardNonce = "<?php echo wp_create_nonce('kmnft_dashboard_nonce'); ?>";

        function uploadUserIcon(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const spinner = document.getElementById('avatar-spinner');

                // Show Spinner
                spinner.classList.remove('hidden');

                const formData = new FormData();
                formData.append('action', 'kmnft_upload_user_icon');
                formData.append('file', file);
                formData.append('nonce', dashboardNonce);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        spinner.classList.add('hidden');
                        if (data.success) {
                            // Update Image
                            document.getElementById('user-avatar-img').src = data.data.url;
                            // alert('Icon updated!');
                        } else {
                            alert('Error: ' + (data.data.message || 'Upload failed'));
                        }
                    })
                    .catch(error => {
                        spinner.classList.add('hidden');
                        console.error('Error:', error);
                        alert('Upload error occurred.');
                    });
            }
        }

        // --- Icon Selection Modal ---
        function openIconSelectionModal() {
            const modal = document.getElementById('icon-selection-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeIconSelectionModal() {
            const modal = document.getElementById('icon-selection-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function selectDefaultIcon(iconFilename) {
            const spinner = document.getElementById('avatar-spinner');
            spinner.classList.remove('hidden');
            closeIconSelectionModal();

            const formData = new FormData();
            formData.append('action', 'kmnft_select_default_icon');
            formData.append('icon', iconFilename);
            formData.append('nonce', dashboardNonce);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    spinner.classList.add('hidden');
                    if (data.success) {
                        // Update Image
                        document.getElementById('user-avatar-img').src = data.data.url;
                    } else {
                        alert('Error: ' + (data.data.message || 'Selection failed'));
                    }
                })
                .catch(error => {
                    spinner.classList.add('hidden');
                    console.error('Error:', error);
                    alert('Selection error occurred.');
                });
        }

        // Close icon selection modal on Escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeIconSelectionModal();
            }
        });
    </script>

    <!-- Password Change Modal -->
    <div id="password-modal"
        class="fixed inset-0 z-[100] bg-black/90 hidden flex items-center justify-center p-4 backdrop-blur-sm"
        onclick="closePasswordModal()">
        <div class="relative w-full max-w-md bg-gray-900 border border-gray-700 rounded-lg p-6 shadow-2xl"
            onclick="event.stopPropagation()">
            <button onclick="closePasswordModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h3 class="text-xl font-bold text-white mb-6">Change Password</h3>
            <form method="post" action="">
                <?php wp_nonce_field('kmnft_change_password_nonce'); ?>
                <input type="hidden" name="kmnft_change_password" value="1">

                <div class="mb-4">
                    <label class="block text-xs text-gray-400 mb-1">Current Password</label>
                    <input type="password" name="current_password" required
                        class="w-full bg-black/50 border border-gray-600 rounded p-2 text-white focus:border-kmnft-green outline-none">
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-gray-400 mb-1">New Password</label>
                    <input type="password" name="new_password" required
                        class="w-full bg-black/50 border border-gray-600 rounded p-2 text-white focus:border-kmnft-green outline-none">
                </div>
                <div class="mb-6">
                    <label class="block text-xs text-gray-400 mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required
                        class="w-full bg-black/50 border border-gray-600 rounded p-2 text-white focus:border-kmnft-green outline-none">
                </div>
                <button type="submit"
                    class="w-full py-2 bg-kmnft-green text-black font-bold uppercase rounded hover:bg-white transition">
                    Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- Icon Selection Modal -->
    <div id="icon-selection-modal"
        class="fixed inset-0 z-[100] bg-black/90 hidden flex items-center justify-center p-4 backdrop-blur-sm"
        onclick="closeIconSelectionModal()">
        <div class="relative w-full max-w-2xl bg-gray-900 border border-gray-700 rounded-lg p-6 shadow-2xl"
            onclick="event.stopPropagation()">
            <button onclick="closeIconSelectionModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h3 class="text-xl font-bold text-white mb-6">プロファイル画像を選択</h3>

            <!-- Default Icons Grid -->
            <div class="mb-6">
                <h4 class="text-sm text-gray-400 mb-3">デフォルトアイコン</h4>
                <div class="grid grid-cols-5 gap-3">
                    <?php
                    $default_icons = kmnft_get_default_icons();
                    foreach ($default_icons as $icon_file):
                        $icon_url = get_template_directory_uri() . '/assets/images/default-icons/' . $icon_file;
                        ?>
                        <div class="aspect-square rounded-lg overflow-hidden border-2 border-gray-700 hover:border-kmnft-green transition cursor-pointer group"
                            onclick="selectDefaultIcon('<?php echo esc_js($icon_file); ?>')">
                            <img src="<?php echo esc_url($icon_url); ?>" alt="Default Icon"
                                class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Local Upload Button -->
            <div class="border-t border-gray-700 pt-6">
                <button onclick="document.getElementById('user-icon-input').click(); closeIconSelectionModal();"
                    class="w-full py-3 bg-kmnft-green/20 border border-kmnft-green text-kmnft-green font-bold uppercase rounded hover:bg-kmnft-green hover:text-black transition flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    ローカルから選択
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="w-full bg-black/80 backdrop-blur-md border-t border-gray-800 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-6 flex flex-col items-center justify-center space-y-6">

            <!-- Links Container -->
            <div class="flex flex-wrap justify-center items-center gap-x-8 gap-y-6">
                <!-- HP Link -->
                <a href="https://kamakura-inter.com/" target="_blank" rel="noopener noreferrer"
                    class="group flex flex-col items-center space-y-2 text-gray-400 hover:text-white transition">
                    <div
                        class="p-3 rounded-full bg-gray-800 group-hover:bg-kmnft-green group-hover:text-black transition duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold tracking-wider">OFFICIAL</span>
                </a>

                <!-- X (Twitter) -->
                <a href="https://twitter.com/kamakura_inter" target="_blank" rel="noopener noreferrer"
                    class="group flex flex-col items-center space-y-2 text-gray-400 hover:text-white transition">
                    <div
                        class="p-3 rounded-full bg-gray-800 group-hover:bg-kmnft-green group-hover:text-black transition duration-300">
                        <!-- X Logo SVG -->
                        <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M18.0558 26.103L0.912215 45.6713H8.50661L21.6288 30.6534L33.1916 45.6744L48 45.5936L29.2251 20.7665L45.2472 2.41364L37.7747 2.33008L25.6722 16.1228L15.3177 2.3526L0 2.33598L18.0558 26.103ZM39.0315 41.1669L35.1992 41.155L8.8696 6.68504H12.9919L39.0315 41.1669Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold tracking-wider">X</span>
                </a>

                <!-- Facebook -->
                <a href="https://www.facebook.com/KamakuraInterFC" target="_blank" rel="noopener noreferrer"
                    class="group flex flex-col items-center space-y-2 text-gray-400 hover:text-white transition">
                    <div
                        class="p-3 rounded-full bg-gray-800 group-hover:bg-kmnft-green group-hover:text-black transition duration-300">
                        <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8">
                            <g clip-path="url(#clip0_332_7)">
                                <path
                                    d="M48 24C48 10.7452 37.2548 0 24 0C10.7452 0 0 10.7452 0 24C0 35.9789 8.77641 45.908 20.25 47.7084V30.9375H14.1562V24H20.25V18.7125C20.25 12.6975 23.8331 9.375 29.3152 9.375C31.9402 9.375 34.6875 9.84375 34.6875 9.84375V15.75H31.6613C28.68 15.75 27.75 17.6002 27.75 19.5V24H34.4062L33.3422 30.9375H27.75V47.7084C39.2236 45.908 48 35.9789 48 24Z"
                                    fill="currentColor" />
                            </g>
                            <defs>
                                <clipPath id="clip0_332_7">
                                    <rect width="48" height="48" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <span class="text-xs font-bold tracking-wider">FACEBOOK</span>
                </a>

                <!-- Instagram -->
                <a href="https://www.instagram.com/kamakura_inter_fc/" target="_blank" rel="noopener noreferrer"
                    class="group flex flex-col items-center space-y-2 text-gray-400 hover:text-white transition">
                    <div
                        class="p-3 rounded-full bg-gray-800 group-hover:bg-kmnft-green group-hover:text-black transition duration-300">
                        <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8">
                            <g clip-path="url(#clip0_332_22)">
                                <path
                                    d="M24 4.32187C30.4125 4.32187 31.1719 4.35 33.6938 4.4625C36.0375 4.56562 37.3031 4.95938 38.1469 5.2875C39.2625 5.71875 40.0688 6.24375 40.9031 7.07812C41.7469 7.92188 42.2625 8.71875 42.6938 9.83438C43.0219 10.6781 43.4156 11.9531 43.5188 14.2875C43.6313 16.8187 43.6594 17.5781 43.6594 23.9813C43.6594 30.3938 43.6313 31.1531 43.5188 33.675C43.4156 36.0188 43.0219 37.2844 42.6938 38.1281C42.2625 39.2438 41.7375 40.05 40.9031 40.8844C40.0594 41.7281 39.2625 42.2438 38.1469 42.675C37.3031 43.0031 36.0281 43.3969 33.6938 43.5C31.1625 43.6125 30.4031 43.6406 24 43.6406C17.5875 43.6406 16.8281 43.6125 14.3063 43.5C11.9625 43.3969 10.6969 43.0031 9.85313 42.675C8.7375 42.2438 7.93125 41.7188 7.09688 40.8844C6.25313 40.0406 5.7375 39.2438 5.30625 38.1281C4.97813 37.2844 4.58438 36.0094 4.48125 33.675C4.36875 31.1438 4.34063 30.3844 4.34063 23.9813C4.34063 17.5688 4.36875 16.8094 4.48125 14.2875C4.58438 11.9437 4.97813 10.6781 5.30625 9.83438C5.7375 8.71875 6.2625 7.9125 7.09688 7.07812C7.94063 6.23438 8.7375 5.71875 9.85313 5.2875C10.6969 4.95938 11.9719 4.56562 14.3063 4.4625C16.8281 4.35 17.5875 4.32187 24 4.32187ZM24 0C17.4844 0 16.6688 0.028125 14.1094 0.140625C11.5594 0.253125 9.80625 0.665625 8.2875 1.25625C6.70313 1.875 5.3625 2.69062 4.03125 4.03125C2.69063 5.3625 1.875 6.70313 1.25625 8.27813C0.665625 9.80625 0.253125 11.55 0.140625 14.1C0.028125 16.6687 0 17.4844 0 24C0 30.5156 0.028125 31.3313 0.140625 33.8906C0.253125 36.4406 0.665625 38.1938 1.25625 39.7125C1.875 41.2969 2.69063 42.6375 4.03125 43.9688C5.3625 45.3 6.70313 46.125 8.27813 46.7344C9.80625 47.325 11.55 47.7375 14.1 47.85C16.6594 47.9625 17.475 47.9906 23.9906 47.9906C30.5063 47.9906 31.3219 47.9625 33.8813 47.85C36.4313 47.7375 38.1844 47.325 39.7031 46.7344C41.2781 46.125 42.6188 45.3 43.95 43.9688C45.2813 42.6375 46.1063 41.2969 46.7156 39.7219C47.3063 38.1938 47.7188 36.45 47.8313 33.9C47.9438 31.3406 47.9719 30.525 47.9719 24.0094C47.9719 17.4938 47.9438 16.6781 47.8313 14.1188C47.7188 11.5688 47.3063 9.81563 46.7156 8.29688C46.125 6.70312 45.3094 5.3625 43.9688 4.03125C42.6375 2.7 41.2969 1.875 39.7219 1.26562C38.1938 0.675 36.45 0.2625 33.9 0.15C31.3313 0.028125 30.5156 0 24 0Z"
                                    fill="currentColor" />
                                <path
                                    d="M24 11.6719C17.1938 11.6719 11.6719 17.1938 11.6719 24C11.6719 30.8062 17.1938 36.3281 24 36.3281C30.8062 36.3281 36.3281 30.8062 36.3281 24C36.3281 17.1938 30.8062 11.6719 24 11.6719ZM24 31.9969C19.5844 31.9969 16.0031 28.4156 16.0031 24C16.0031 19.5844 19.5844 16.0031 24 16.0031C28.4156 16.0031 31.9969 19.5844 31.9969 24C31.9969 28.4156 28.4156 31.9969 24 31.9969Z"
                                    fill="currentColor" />
                                <path
                                    d="M39.6937 11.1848C39.6937 12.7785 38.4 14.0629 36.8156 14.0629C35.2219 14.0629 33.9375 12.7691 33.9375 11.1848C33.9375 9.59102 35.2313 8.30664 36.8156 8.30664C38.4 8.30664 39.6937 9.60039 39.6937 11.1848Z"
                                    fill="currentColor" />
                            </g>
                            <defs>
                                <clipPath id="clip0_332_22">
                                    <rect width="48" height="48" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                    <span class="text-xs font-bold tracking-wider">INSTAGRAM</span>
                </a>

                <!-- LINE -->
                <a href="https://page.line.me/346jwclp" target="_blank" rel="noopener noreferrer"
                    class="group flex flex-col items-center space-y-2 text-gray-400 hover:text-white transition">
                    <div
                        class="p-3 rounded-full bg-gray-800 group-hover:bg-kmnft-green group-hover:text-black transition duration-300">
                        <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M42.8491 32.6503C37.8107 38.4872 26.5642 45.5684 24 46.6543C21.5467 47.6933 21.7906 46.1229 21.8951 45.4502L21.8952 45.4499C21.8999 45.4196 21.9043 45.3911 21.9082 45.3648C21.9531 45.1235 22.0656 44.4372 22.2456 43.306C22.3355 42.6726 22.4255 41.7224 22.1781 41.1115C21.9082 40.4328 20.851 40.0935 20.0637 39.9125C8.52484 38.3967 0 30.2749 0 20.592C0 9.80056 10.7516 1 24 1C37.2259 1 48 9.80056 48 20.592C48 24.9131 46.313 28.8043 42.8491 32.6503ZM39.6401 26.4519H32.8922C32.6448 26.4519 32.4424 26.2483 32.4424 25.9994V25.9768V15.4568C32.4424 15.208 32.6448 15.0044 32.8922 15.0044H39.6401C39.8875 15.0044 40.09 15.208 40.09 15.4568V17.1762C40.09 17.4251 39.8875 17.6287 39.6401 17.6287H35.0516V19.416H39.6401C39.8875 19.416 40.09 19.6196 40.09 19.8684V21.5878C40.09 21.8367 39.8875 22.0403 39.6401 22.0403H35.0516V23.8276H39.6401C39.8875 23.8276 40.09 24.0312 40.09 24.28V25.9994C40.09 26.2483 39.8875 26.4519 39.6401 26.4519ZM7.94754 26.4519H7.97004H14.6954C14.9429 26.4519 15.1453 26.2483 15.1453 25.9994V24.28C15.1453 24.0312 14.9429 23.8276 14.6954 23.8276H10.1069V15.4568C10.1069 15.208 9.90444 15.0044 9.65701 15.0044H7.94754C7.70012 15.0044 7.49768 15.208 7.49768 15.4568V25.9768V25.9994C7.49768 26.2483 7.70012 26.4519 7.94754 26.4519ZM18.7437 15.0044H17.0346C16.7862 15.0044 16.5848 15.207 16.5848 15.4568V25.9994C16.5848 26.2493 16.7862 26.4519 17.0346 26.4519H18.7437C18.9921 26.4519 19.1935 26.2493 19.1935 25.9994V15.4568C19.1935 15.207 18.9921 15.0044 18.7437 15.0044ZM30.8005 25.9994V15.4568C30.8005 15.208 30.598 15.0044 30.3281 15.0044H28.6411C28.3937 15.0044 28.1688 15.208 28.1688 15.4568V21.7009L23.3778 15.208C23.3778 15.1929 23.3703 15.1778 23.3553 15.1627L23.3103 15.1175L23.2878 15.0949H23.2653C23.2653 15.0798 23.2578 15.0722 23.2428 15.0722V15.0496H23.1978L23.1753 15.027H23.1528C23.1379 15.027 23.1303 15.0195 23.1303 15.0044H23.1079H23.0854H23.0629H23.0404H23.0179H22.9954H21.3084C21.061 15.0044 20.8361 15.208 20.8361 15.4568V25.9994C20.8361 26.2483 21.061 26.4519 21.3084 26.4519H22.9954C23.2653 26.4519 23.4677 26.2483 23.4677 25.9994V19.7327L28.2587 26.2483C28.2887 26.2935 28.3262 26.3312 28.3712 26.3614H28.3937C28.3937 26.3765 28.4012 26.384 28.4162 26.384L28.4387 26.4066H28.4612H28.4612H28.4837V26.4293H28.5287C28.5586 26.4443 28.5961 26.4519 28.6411 26.4519H30.3281C30.598 26.4519 30.8005 26.2483 30.8005 25.9994Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold tracking-wider">LINE</span>
                </a>

                <!-- YouTube -->
                <a href="https://www.youtube.com/channel/UCxt6P4I8nhwMW7ZtOKyt_AQ" target="_blank"
                    rel="noopener noreferrer"
                    class="group flex flex-col items-center space-y-2 text-gray-400 hover:text-white transition">
                    <div
                        class="p-3 rounded-full bg-gray-800 group-hover:bg-kmnft-green group-hover:text-black transition duration-300">
                        <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8">
                            <path
                                d="M47.5219 14.3996C47.5219 14.3996 47.0531 11.0902 45.6094 9.63711C43.7812 7.72461 41.7375 7.71523 40.8 7.60273C34.0875 7.11523 24.0094 7.11523 24.0094 7.11523H23.9906C23.9906 7.11523 13.9125 7.11523 7.2 7.60273C6.2625 7.71523 4.21875 7.72461 2.39062 9.63711C0.946875 11.0902 0.4875 14.3996 0.4875 14.3996C0.4875 14.3996 0 18.2902 0 22.1715V25.809C0 29.6902 0.478125 33.5809 0.478125 33.5809C0.478125 33.5809 0.946875 36.8902 2.38125 38.3434C4.20937 40.2559 6.60938 40.1902 7.67813 40.3965C11.5219 40.7621 24 40.8746 24 40.8746C24 40.8746 34.0875 40.8559 40.8 40.3777C41.7375 40.2652 43.7812 40.2559 45.6094 38.3434C47.0531 36.8902 47.5219 33.5809 47.5219 33.5809C47.5219 33.5809 48 29.6996 48 25.809V22.1715C48 18.2902 47.5219 14.3996 47.5219 14.3996ZM19.0406 30.2246V16.734L32.0062 23.5027L19.0406 30.2246Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold tracking-wider">YOUTUBE</span>
                </a>

                <!-- NOTE -->
                <a href="https://note.com/kamakura_inter" target="_blank" rel="noopener noreferrer"
                    class="group flex flex-col items-center space-y-2 text-gray-400 hover:text-white transition">
                    <div
                        class="p-3 rounded-full bg-gray-800 group-hover:bg-kmnft-green group-hover:text-black transition duration-300">
                        <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M18.9175 16.263V12.5919C18.9175 11.9229 18.9515 11.6998 19.0524 11.3566C19.322 10.4131 20.2325 9.7099 21.3111 9.7099C22.3897 9.7099 23.2999 10.4303 23.5695 11.3566C23.6707 11.6998 23.7047 11.9229 23.7047 12.5919V18.2529C23.7047 18.5962 23.7047 18.9391 23.6371 19.248C23.4518 20.1056 22.6933 20.8779 21.8504 21.0665C21.5471 21.1349 21.2099 21.135 20.8727 21.135H15.3105C14.6532 21.135 14.434 21.1007 14.0968 20.9977C13.1866 20.7233 12.4788 19.7969 12.4788 18.6991C12.4788 17.601 13.1866 16.6747 14.0968 16.4003C14.434 16.2973 14.6532 16.263 15.3105 16.263H18.9175ZM38.7396 41.9273H8.90544V15.1824C8.90544 14.8391 9.02345 14.5476 9.25947 14.3074L16.996 6.43303C17.232 6.19312 17.5185 6.073 17.8557 6.073H38.7396V41.9273ZM42.1278 0.034229C41.9761 0.0171142 41.7737 0 41.3861 0H17.0633C16.7939 0 16.5243 0.0171142 16.3387 0.034229C15.2261 0.137224 14.2316 0.669007 13.4394 1.47524L4.38795 10.6876C3.5961 11.4941 3.07331 12.506 2.97241 13.6384C2.95529 13.8269 2.93848 14.1014 2.93848 14.3758V44.6204C2.93848 45.015 2.95529 45.221 2.97241 45.3753C3.10694 46.662 4.25312 47.8282 5.51732 47.9655C5.66927 47.9829 5.87136 48 6.25903 48H41.3861C41.7737 48 41.9761 47.9829 42.1278 47.9655C43.392 47.8282 44.5381 46.662 44.673 45.3753C44.6895 45.221 44.7066 45.015 44.7066 44.6204V3.37956C44.7066 2.985 44.6895 2.77902 44.673 2.62468C44.5381 1.33801 43.392 0.171453 42.1278 0.034229Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold tracking-wider">NOTE</span>
                </a>
            </div>

            <!-- Copyright -->
            <div class="text-gray-500 text-xs mt-4">
                &copy; <?php echo date('Y'); ?> KAMAKURA INTERNATIONAL FC. All Rights Reserved.
            </div>
        </div>
    </footer>
    <!-- <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script> -->
    <!-- Smart Sticky Sidebar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('dashboard-sidebar');
            if (!sidebar) return;

            const handleResize = () => {
                // Only active on desktop (md breakpoint approx 768px)
                if (window.innerWidth < 768) {
                    sidebar.style.top = '';
                    sidebar.style.bottom = '';
                    return;
                }

                const windowHeight = window.innerHeight;
                const sidebarHeight = sidebar.scrollHeight;
                const headerOffset = 96; // 6rem (top-24)
                const bottomOffset = 24; // Margin from bottom

                // Always reset bottom to auto as we only control top
                sidebar.style.bottom = 'auto';

                if (sidebarHeight > (windowHeight - headerOffset)) {
                    // Sidebar is taller than viewport
                    // Calculate negative top so bottom sticks when scrolling down
                    // Formula: ViewportHeight - SidebarHeight - BottomMargin
                    const topValue = windowHeight - sidebarHeight - bottomOffset;
                    sidebar.style.top = `${topValue}px`;
                } else {
                    // Sidebar is shorter than viewport
                    // Stick to top
                    sidebar.style.top = `${headerOffset}px`;
                }
            };

            // Initial check
            handleResize();

            // Listen for resize
            window.addEventListener('resize', handleResize);

            // Listen for content changes (if any dynamic content loads)
            const observer = new ResizeObserver(handleResize);
            observer.observe(sidebar);
            observer.observe(document.body); // In case main content changes affect layout context
        });
    </script>
</body>

</html>