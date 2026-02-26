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
                <p class="text-xs text-gray-400 mt-2">ランキングは、全ユーザーおよび全NFTを対象とした集計結果に基づいています。トークン・ユーザーともに上位30位まで表示されます。
                </p>
                <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider">Rankings are based on the aggregate
                    results of all users and all NFTs. Top 30 tokens and users are displayed.</p>
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
            <p class="text-[10px] text-gray-500 px-2 italic">* 選択されたシーズンの上位30トークンを表示しています。(行をクリックで詳細表示) / Displays top
                30 tokens for the selected season. (Click row for details)</p>
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
            <p class="text-[10px] text-gray-500 px-2 italic">* 選択されたシーズンの上位30ユーザーを表示しています。 / Displays top 30 users for
                the selected season.</p>
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
        <div class="flex flex-wrap justify-center items-center gap-x-8 gap-y-6 mb-12">
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
                            d="M42.8491 32.6503C37.8107 38.4872 26.5642 45.5684 24 46.6543C21.5467 47.6933 21.7906 46.1229 21.8951 45.4502L21.8952 45.4499C21.8999 45.4196 21.9043 45.3911 21.9082 45.3648C21.9531 45.1235 22.0656 44.4372 22.2456 43.306C22.3355 42.6726 22.4255 41.7224 22.1781 41.1115C21.9082 40.4328 20.851 40.0935 20.0637 39.9125C8.52484 38.3967 0 30.2749 0 20.592C0 9.80056 10.7516 1 24 1C37.2259 1 48 9.80056 48 20.592C48 24.9131 46.313 28.8043 42.8491 32.6503ZM39.6401 26.4519H32.8922C32.6448 26.4519 32.4424 26.2483 32.4424 25.9994V25.9768V15.4568C32.4424 15.208 32.6448 15.0044 32.8922 15.0044H39.6401C39.8875 15.0044 40.09 15.208 40.09 15.4568V17.1762C40.09 17.4251 39.8875 17.6287 39.6401 17.6287H35.0516V19.416H39.6401C39.8875 19.416 40.09 19.6196 40.09 19.8684V21.5878C40.09 21.8367 39.8875 22.0403 39.6401 22.0403H35.0516V23.8276H39.6401C39.8875 23.8276 40.09 24.0312 40.09 24.28V25.9994C40.09 26.2483 39.8875 26.4519 39.6401 26.4519ZM7.94754 26.4519H7.97004H14.6954C14.9429 26.4519 15.1453 26.2483 15.1453 25.9994V24.28C15.1453 24.0312 14.9429 23.8276 14.6954 23.8276H10.1069V15.4568C10.1069 15.208 9.90444 15.0044 9.65701 15.0044H7.94754C7.70012 15.0044 7.49768 15.208 7.49768 15.4568V25.9768V25.9994C7.49768 26.2483 7.70012 26.4519 7.94754 26.4519ZM18.7437 15.0044H17.0346C16.7862 15.0044 16.5848 15.207 16.5848 15.4568V25.9994C16.5848 26.2493 16.7862 26.4519 17.0346 26.4519H18.7437C18.9921 26.4519 19.1935 26.2493 19.1935 25.9994V15.4568C19.1935 15.207 18.9921 15.0044 18.7437 15.0044ZM30.8005 25.9994V15.4568C30.8005 15.208 30.598 15.0044 30.3281 15.0044H28.6411C28.3937 15.0044 28.1688 15.208 28.1688 15.4568V21.7009L23.3778 15.208C23.3778 15.1929 23.3703 15.1778 23.3553 15.1627L23.3103 15.1175L23.2878 15.0949H23.2653C23.2653 15.0798 23.2578 15.0722 23.2428 15.0722V15.0496H23.1978L23.1753 15.027H23.1528C23.1379 15.027 23.1303 15.0195 23.1303 15.0044H23.1079H23.0854H23.0629H23.0404H23.0179H22.9954H21.3084C21.061 15.0044 20.8361 15.208 20.8361 15.4568V25.9994C20.8361 26.2483 21.061 26.4519 21.3084 26.4519H22.9954C23.2653 26.4519 23.4677 26.2483 23.4677 25.9994V19.7327L28.2587 26.2483C28.2887 26.2935 28.3262 26.3312 28.3712 26.3614H28.3937C28.3937 26.3765 28.4012 26.384 28.4162 26.384L28.4387 26.4066H28.4612H28.4837V26.4293H28.5287C28.5586 26.4443 28.5961 26.4519 28.6411 26.4519H30.3281C30.598 26.4519 30.8005 26.2483 30.8005 25.9994Z"
                            fill="currentColor" />
                    </svg>
                </div>
                <span class="text-xs font-bold tracking-wider">LINE</span>
            </a>

            <!-- YouTube -->
            <a href="https://www.youtube.com/channel/UCxt6P4I8nhwMW7ZtOKyt_AQ" target="_blank" rel="noopener noreferrer"
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