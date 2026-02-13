<?php
/**
 * Template Name: KMNFT Ranking
 */

global $wpdb;

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

// Define table names
$table_token_summary = $wpdb->prefix . 'kmnft_ksp_token_summary';
$table_user_summary = $wpdb->prefix . 'kmnft_ksp_user_summary';

// Get available seasons from both tables
$token_seasons = $wpdb->get_col("SELECT DISTINCT season FROM $table_token_summary ORDER BY season DESC");
$user_seasons = $wpdb->get_col("SELECT DISTINCT season FROM $table_user_summary ORDER BY season DESC");
$all_seasons = array_unique(array_merge($token_seasons, $user_seasons));
rsort($all_seasons);

// Default to latest season
$selected_season = isset($_GET['season']) ? sanitize_text_field($_GET['season']) : (!empty($all_seasons) ? $all_seasons[0] : '');

// Fetch Token Ranking (Top 30)
$token_ranking = array();
if ($selected_season) {
    $table_holdings = $wpdb->prefix . 'kmnft_holdings';
    $token_ranking = $wpdb->get_results($wpdb->prepare(
        "SELECT s.*, h.zone_x, h.zone_y 
         FROM $table_token_summary s
         LEFT JOIN $table_holdings h ON s.token_id = h.token_id
         WHERE s.season = %s 
         GROUP BY s.token_id
         ORDER BY s.total_points DESC 
         LIMIT 30",
        $selected_season
    ));

    // Fetch history for these tokens
    if (!empty($token_ranking) && class_exists('KMNFT_User_Manager')) {
        $kmnft_manager = new KMNFT_User_Manager();
        $token_ids = array_map(function ($item) {
            return $item->token_id;
        }, $token_ranking);
        $tokens_ksp_history = $kmnft_manager->get_tokens_ksp_history($token_ids);
    }
}

// Fetch User Ranking (Top 30)
$user_ranking = array();
if ($selected_season) {
    $user_ranking = $wpdb->get_results($wpdb->prepare(
        "SELECT s.*, u.display_name, u.user_login 
         FROM $table_user_summary s
         JOIN {$wpdb->users} u ON s.user_id = u.ID
         WHERE s.season = %s 
         ORDER BY s.total_points DESC 
         LIMIT 30",
        $selected_season
    ));
}

$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - KAMAKURA STADIUM NFT PORTAL(β)</title>
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

        .tab-active {
            border-bottom: 2px solid #00ff41;
            color: #00ff41;
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
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">DASHBOARD</a>
            <a href="<?php echo home_url('/points'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">POINTS</a>
            <a href="<?php echo home_url('/ranking'); ?>"
                class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs transition">RANKING</a>
            <a href="<?php echo home_url('/contact'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">CONTACT</a>
            <?php if ($is_logged_in): ?>
                <a href="<?php echo wp_logout_url(home_url('/dashboard')); ?>"
                    class="px-4 py-1 border border-white/50 text-white rounded text-xs hover:bg-white hover:text-black transition">LOGOUT</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login'); ?>"
                    class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs hover:bg-kmnft-green hover:text-black transition">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="flex-grow pt-24 px-6 pb-10 max-w-5xl mx-auto w-full">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold neon-text uppercase tracking-widest">KSP Ranking</h1>
                <p class="text-xs text-gray-400 mt-2">ランキングは、全ユーザーおよび全NFTを対象とした集計結果に基づいています。</p>
            </div>

            <!-- Season Selector -->
            <form method="GET" action="" class="flex items-center gap-2">
                <label for="season" class="text-xs text-gray-400">SEASON:</label>
                <select name="season" id="season" onchange="this.form.submit()"
                    class="bg-kmnft-navy border border-gray-700 text-white text-xs rounded px-2 py-1 outline-none focus:border-kmnft-green transition">
                    <?php if (empty($all_seasons)): ?>
                        <option value="">No Data</option>
                    <?php else: ?>
                        <?php foreach ($all_seasons as $season): ?>
                            <option value="<?php echo esc_attr($season); ?>" <?php selected($selected_season, $season); ?>>
                                <?php echo esc_html($season); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </form>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-8 border-b border-gray-800 mb-6">
            <button onclick="switchTab('token')" id="tab-btn-token"
                class="pb-2 text-sm font-bold uppercase tracking-wider transition hover:text-kmnft-green tab-active">Token
                Ranking</button>
            <button onclick="switchTab('user')" id="tab-btn-user"
                class="pb-2 text-sm font-bold uppercase tracking-wider transition hover:text-kmnft-green text-gray-500">User
                Ranking</button>
        </div>

        <!-- Token Ranking Table -->
        <div id="tab-content-token" class="space-y-4">
            <div class="glass-card rounded-lg overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-gray-400 uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-3 font-medium">Rank</th>
                            <th class="px-6 py-3 font-medium">Token ID</th>
                            <th class="px-6 py-3 font-medium text-right">KSP Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($token_ranking)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">No data available for
                                    this season.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($token_ranking as $index => $item):
                                $rank_num = $index + 1;
                                $row_class = ($rank_num <= 3) ? 'bg-kmnft-green/5' : '';
                                $rank_color = ($rank_num == 1) ? 'text-kmnft-gold' : (($rank_num == 2) ? 'text-gray-300' : (($rank_num == 3) ? 'text-amber-600' : 'text-gray-500'));
                                ?>
                                <tr class="hover:bg-white/5 transition <?php echo $row_class; ?> cursor-pointer group/row"
                                    onclick="openTokenModal('<?php echo esc_js($item->token_id); ?>', '<?php echo esc_js($rank_num); ?>', '<?php echo esc_js(number_format($item->total_points)); ?>', '<?php echo esc_js($item->zone_x); ?>', '<?php echo esc_js($item->zone_y); ?>', '<?php echo esc_js($selected_season); ?>')">
                                    <td class="px-6 py-4">
                                        <span class="font-bold <?php echo $rank_color; ?>">#<?php echo $rank_num; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="w-14 h-14 rounded border border-gray-700 overflow-hidden bg-gray-900 shadow-lg group-hover/row:border-kmnft-green transition duration-300">
                                                <img src="<?php echo KMNFT_IMAGE_BASE_URL . esc_attr($item->token_id) . '.png'; ?>"
                                                    alt="<?php echo esc_attr($item->token_id); ?>"
                                                    class="w-full h-full object-cover group-hover/row:scale-110 transition duration-500"
                                                    onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';this.style.opacity='0.5';">
                                            </div>
                                            <span
                                                class="font-mono text-white text-base">#<?php echo esc_html($item->token_id); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-kmnft-green font-mono text-base">
                                        <?php echo number_format($item->total_points); ?> <span
                                            class="text-[10px] text-gray-500 ml-1 uppercase">pt</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-[10px] text-gray-500 px-2 italic">* Displays top 30 tokens for the selected season. (Click
                row for details)</p>
        </div>

        <!-- User Ranking Table -->
        <div id="tab-content-user" class="space-y-4 hidden">
            <div class="glass-card rounded-lg overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white/5 text-gray-400 uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-6 py-3 font-medium">Rank</th>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium text-right">KSP Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($user_ranking)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">No data available for
                                    this season.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($user_ranking as $index => $item):
                                $rank_num = $index + 1;
                                $is_current = ($is_logged_in && $current_user->ID == $item->user_id);
                                $row_class = $is_current ? 'bg-kmnft-green/10 border-l-2 border-kmnft-green' : (($rank_num <= 3) ? 'bg-kmnft-green/5' : '');
                                $rank_color = ($rank_num == 1) ? 'text-kmnft-gold' : (($rank_num == 2) ? 'text-gray-300' : (($rank_num == 3) ? 'text-amber-600' : 'text-gray-500'));

                                // Fetch Avatar
                                $user_avatar_url = get_user_meta($item->user_id, 'kmnft_user_avatar_url', true);
                                if (!$user_avatar_url) {
                                    $user_avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($item->display_name) . '&background=00ff41&color=0a0a12';
                                }
                                ?>
                                <tr class="hover:bg-white/5 transition <?php echo $row_class; ?>">
                                    <td class="px-6 py-4">
                                        <span class="font-bold <?php echo $rank_color; ?>">#<?php echo $rank_num; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full overflow-hidden border border-white/10 bg-gray-900 shadow-sm flex-shrink-0">
                                                <img src="<?php echo esc_url($user_avatar_url); ?>"
                                                    alt="<?php echo esc_attr($item->display_name); ?>"
                                                    class="w-full h-full object-cover">
                                            </div>
                                            <span class="text-white font-bold text-base">
                                                <?php echo esc_html($item->display_name); ?>
                                                <?php if ($is_current): ?>
                                                    <span
                                                        class="ml-2 text-[8px] bg-kmnft-green text-black px-1 rounded uppercase">YOU</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-kmnft-green font-mono text-base">
                                        <?php echo number_format($item->total_points); ?> <span
                                            class="text-[10px] text-gray-500 ml-1 uppercase">pt</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-[10px] text-gray-500 px-2 italic">* Displays top 30 users for the selected season.</p>
        </div>
    </main>

    <!-- Token Detail Modal -->
    <div id="token-modal"
        class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/90 backdrop-blur-sm">
        <div class="glass-card max-w-2xl w-full rounded-2xl overflow-hidden relative animate-in zoom-in duration-300">
            <button onclick="closeTokenModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-white transition z-10 w-8 h-8 flex items-center justify-center rounded-full bg-black/50 overflow-hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="flex flex-col md:flex-row h-full">
                <!-- Image Section -->
                <div class="w-full md:w-3/5 aspect-square">
                    <img id="modal-token-image" src="" alt="Token NFT"
                        class="w-full h-full object-contain brightness-[1.1]">
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

    <footer class="mt-auto py-10 border-t border-gray-800 text-center">
        <div class="flex flex-wrap justify-center items-center gap-6 mb-8">
            <!-- HP -->
            <a href="https://kamakura-inter.com/" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-kmnft-green transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </a>
            <!-- X -->
            <a href="https://twitter.com/kamakura_inter" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-kmnft-green transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z">
                    </path>
                </svg>
            </a>
            <!-- Facebook -->
            <a href="https://www.facebook.com/KamakuraInterFC" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-kmnft-green transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                        clip-rule="evenodd"></path>
                </svg>
            </a>
            <!-- Instagram -->
            <a href="https://www.instagram.com/kamakura_inter_fc/" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-kmnft-green transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772 4.902 4.902 0 011.772-1.153c.636-.247 1.363-.416 2.427-.465C9.673 2.013 10.03 2 12.48 2h-.165zm-2.386 8.88c0 1.938 1.573 3.512 3.511 3.512 1.938 0 3.512-1.574 3.512-3.512 0-.964-.383-1.888-1.065-2.57-.682-.682-1.606-1.065-2.57-1.065a3.512 3.512 0 00-3.388 3.634zm7.662-3.863a1.284 1.284 0 110-2.568 1.284 1.284 0 010 2.568zM12 4.25c-2.27 0-2.553.01-3.44.051-.885.04-1.36.178-1.678.303-.422.164-.722.358-1.04.675-.317.318-.511.618-.675 1.04-.124.318-.261.793-.302 1.678C4.852 9.096 4.842 9.38 4.842 11.65v.7c0 2.27.01 2.553.051 3.44.04.885.178 1.36.303 1.678.164.422.358.722.675 1.04.318.317.618.511 1.04.675.318.124.793.261 1.678.302.887.04 1.17.05 3.44.05h.7c2.27 0 2.553-.01 3.44-.051.885-.041 1.36-.178 1.678-.303.422-.164.722-.358 1.04-.675.318-.318.511-.618.675-1.04.124-.318.261-.793.302-1.678.041-.887.05-1.17.05-3.44v-.7c0-2.27-.01-2.553-.051-3.44-.041-.885-.178-1.36-.303-1.678-.164-.422-.358-.722-.675-1.04-.318-.317-.618-.511-1.04-.675-.318-.124-.793-.261-1.678-.302-.871-.04-1.157-.05-3.284-.05H12.315V4.25h-.315z"
                        clip-rule="evenodd"></path>
                </svg>
            </a>
            <!-- LINE -->
            <a href="https://page.line.me/346jwclp" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-kmnft-green transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        d="M24 10.3c0-4.646-4.925-8.303-11-8.303S2 5.654 2 10.3c0 4.164 3.904 7.643 9.172 8.213L10.518 21.01c-.134.425.105.787.525.787.199 0 .375-.075.525-.225l3.413-1.95c.15-.113.3-.113.45-.113.15 0 .263.037.412.113l.038.037c5.287-1.013 8.924-4.5 8.924-9.359zM10.151 13.91H7.838c-.375 0-.675-.3-.675-.675V8.164c0-.375.3-.675.675-.675s.675.3.675.675v4.388h1.638c.375 0 .675.3.675.675s-.3.682-.675.682zm3.188-.675c0 .375-.3.675-.675.675s-.675-.3-.675-.675V8.164c0-.375.3-.675.675-.675s.675.3.675.675v5.071zm4.838 0c0 .375-.3.675-.675.675h-2.1c-.375 0-.675-.3-.675-.675V8.164c0-.375.3-.675.675-.675s.675.3.675.675v3.825l1.463-1.5c.262-.262.675-.262.937 0 .262.262.262.675 0 .937L16.489 11.23l1.463 1.5c.206.206.244.506.237.505zm3.187-1.012c0 .375-.3.675-.675.675h-2.1c-.375 0-.675-.3-.675-.675V8.164c0-.375.3-.675.675-.675h2.1c.375 0 .675.3.675.675s-.3.675-.675.675h-1.425v1.2h1.425c.375 0 .675.3.675.675s-.3.675-.675.675h-1.425v1.2h1.425c.375 0 .675.3.675.675s-.3.675-.675.682z" />
                </svg>
            </a>
            <!-- YouTube -->
            <a href="https://www.youtube.com/channel/UCxt6P4I8nhwMW7ZtOKyt_AQ" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-kmnft-green transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                </svg>
            </a>
            <!-- NOTE -->
            <a href="https://note.com/kamakura_inter" target="_blank" rel="noopener noreferrer"
                class="text-gray-500 hover:text-kmnft-green transition-colors">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        d="M19.062 10.438a2.625 2.625 0 1 0 0-5.25c-.237 0-.462.038-.674.094a2.623 2.623 0 0 0-.825 3.931 2.631 2.631 0 0 0 1.499 1.225zM16.488 15.75c0 .544-.225 1.04-.593 1.387l-.956.894-.956-.894c-.368-.344-.593-.84-.593-1.387V6.3a2.625 2.625 0 0 0-5.25 0v11.55c0 2.062 1.688 3.75 3.75 3.75h6.3c2.062 0 3.75-1.688 3.75-3.75V15.75c0-.962-.788-1.75-1.75-1.75s-1.75.788-1.75 1.75h-.002zm-8.4 0c0 .544-.225 1.04-.593 1.387l-.956.894-.956-.894c-.368-.344-.593-.84-.593-1.387V6.3a2.625 2.625 0 0 0-5.25 0v11.55c0 2.062 1.688 3.75 3.75 3.75h2.1c2.062 0 3.75-1.688 3.75-3.75V15.75c0-.962-.788-1.75-1.75-1.75s-1.75.788-1.75 1.75h-.002z" />
                </svg>
            </a>
        </div>
        <div class="text-[10px] text-gray-600 uppercase tracking-widest mb-2">Developed by</div>
        <div class="text-sm font-bold text-gray-400">KAMAKURA STADIUM NFT PORTAL(β)</div>
        <div class="mt-4 flex justify-center space-x-6">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="text-[10px] text-gray-500 hover:text-white transition">Cockpit</a>
            <a href="<?php echo home_url('/contact'); ?>"
                class="text-[10px] text-gray-500 hover:text-white transition">Contact</a>
        </div>
    </footer>

    <script>
        function switchTab(tab) {
            const tokenTab = document.getElementById('tab-content-token');
            const userTab = document.getElementById('tab-content-user');
            const tokenBtn = document.getElementById('tab-btn-token');
            const userBtn = document.getElementById('tab-btn-user');

            if (tab === 'token') {
                tokenTab.classList.remove('hidden');
                userTab.classList.add('hidden');
                tokenBtn.classList.add('tab-active');
                tokenBtn.classList.remove('text-gray-500');
                userBtn.classList.remove('tab-active');
                userBtn.classList.add('text-gray-500');
            } else {
                tokenTab.classList.add('hidden');
                userTab.classList.remove('hidden');
                tokenBtn.classList.remove('tab-active');
                tokenBtn.classList.add('text-gray-500');
                userBtn.classList.add('tab-active');
                userBtn.classList.remove('text-gray-500');
            }
        }

        const imageBaseUrl = '<?php echo KMNFT_IMAGE_BASE_URL; ?>';
        const fallbackImage = '<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';
        const tokensHistory = <?php echo json_encode(!empty($tokens_ksp_history) ? $tokens_ksp_history : new stdClass()); ?>;

        let currentTokenId = null;

        function openTokenModal(tokenId, rank, points, x, y, season) {
            currentTokenId = tokenId;
            document.getElementById('modal-token-id').textContent = '#' + tokenId;

            const seasonSelect = document.getElementById('modal-token-season-select');
            seasonSelect.innerHTML = '';
            const history = tokensHistory[tokenId] || [];

            if (history.length > 0) {
                history.forEach(h => {
                    const opt = document.createElement('option');
                    opt.value = h.season;
                    opt.innerText = h.season;
                    if (h.season === season) opt.selected = true;
                    seasonSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = season || '-';
                opt.innerText = season || '-';
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
        }
    </script>
</body>

</html>