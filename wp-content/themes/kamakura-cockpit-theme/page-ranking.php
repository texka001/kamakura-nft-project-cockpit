<?php
/**
 * Template Name: KMNFT Ranking
 */

global $wpdb;

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
    $token_ranking = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_token_summary WHERE season = %s ORDER BY total_points DESC LIMIT 30",
        $selected_season
    ));
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
    <title>Ranking - Kamakura Stadium NFT</title>
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
                NFT</a>
        </div>
        <div class="flex items-center space-x-4 ml-auto">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">DASHBOARD</a>
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
            <h1 class="text-2xl font-bold neon-text uppercase tracking-widest">KSP Ranking</h1>

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
                                    onclick="openTokenModal('<?php echo esc_js($item->token_id); ?>', '<?php echo esc_js($rank_num); ?>', '<?php echo esc_js(number_format($item->total_points)); ?>')">
                                    <td class="px-6 py-4">
                                        <span class="font-bold <?php echo $rank_color; ?>">#<?php echo $rank_num; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-14 h-14 rounded border border-gray-700 overflow-hidden bg-gray-900 shadow-lg group-hover/row:border-kmnft-green transition duration-300">
                                                <img src="<?php echo KMNFT_IMAGE_BASE_URL . esc_attr($item->token_id) . '.png'; ?>" 
                                                     alt="<?php echo esc_attr($item->token_id); ?>" 
                                                     class="w-full h-full object-cover group-hover/row:scale-110 transition duration-500"
                                                     onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';this.style.opacity='0.5';">
                                            </div>
                                            <span class="font-mono text-white text-base">#<?php echo esc_html($item->token_id); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-kmnft-green font-mono text-base">
                                        <?php echo number_format($item->total_points); ?> <span class="text-[10px] text-gray-500 ml-1 uppercase">pt</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-[10px] text-gray-500 px-2 italic">* Displays top 30 tokens for the selected season. (Click row for details)</p>
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
                                <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">No data available for this season.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($user_ranking as $index => $item): 
                                $rank_num = $index + 1;
                                $is_current = ($is_logged_in && $current_user->ID == $item->user_id);
                                $row_class = $is_current ? 'bg-kmnft-green/10 border-l-2 border-kmnft-green' : (($rank_num <= 3) ? 'bg-kmnft-green/5' : '');
                                $rank_color = ($rank_num == 1) ? 'text-kmnft-gold' : (($rank_num == 2) ? 'text-gray-300' : (($rank_num == 3) ? 'text-amber-600' : 'text-gray-500'));
                            ?>
                                <tr class="hover:bg-white/5 transition <?php echo $row_class; ?>">
                                    <td class="px-6 py-4">
                                        <span class="font-bold <?php echo $rank_color; ?>">#<?php echo $rank_num; ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-white font-bold text-base">
                                                <?php echo esc_html($item->display_name); ?>
                                                <?php if ($is_current): ?>
                                                    <span class="ml-2 text-[8px] bg-kmnft-green text-black px-1 rounded uppercase">YOU</span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="text-[10px] text-gray-500 font-mono">ID: <?php echo esc_html($item->user_login); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-kmnft-green font-mono text-base">
                                        <?php echo number_format($item->total_points); ?> <span class="text-[10px] text-gray-500 ml-1 uppercase">pt</span>
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
    <div id="token-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/90 backdrop-blur-sm">
        <div class="glass-card max-w-xl w-full rounded-2xl overflow-hidden relative animate-in zoom-in duration-300">
            <button onclick="closeTokenModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white transition z-10 w-8 h-8 flex items-center justify-center rounded-full bg-black/50 overflow-hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="flex flex-col md:flex-row h-full">
                <!-- Image Section -->
                <div class="w-full md:w-2/3 bg-black/40 aspect-square">
                    <img id="modal-token-image" src="" alt="Token NFT" class="w-full h-full object-contain shadow-2xl">
                </div>
                <!-- Info Section -->
                <div class="w-full md:w-1/3 p-6 flex flex-col justify-center border-t md:border-t-0 md:border-l border-white/10 bg-kmnft-navy/40">
                    <div class="space-y-6">
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Token ID</div>
                            <div id="modal-token-id" class="text-2xl font-bold font-mono text-white">#000000</div>
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Season Rank</div>
                                <div id="modal-token-rank" class="text-3xl font-bold neon-text">#1</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Total KSP</div>
                                <div id="modal-token-points" class="text-3xl font-bold text-kmnft-green font-mono">0<span class="text-xs text-gray-500 ml-1">PT</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tap background to close -->
        <div class="absolute inset-0 -z-10" onclick="closeTokenModal()"></div>
    </div>

    <footer class="mt-auto py-10 border-t border-gray-800 text-center">
        <div class="text-[10px] text-gray-600 uppercase tracking-widest mb-2">Developed by</div>
        <div class="text-sm font-bold text-gray-400">KAMAKURA STADIUM NFT PROJECT</div>
        <div class="mt-4 flex justify-center space-x-6">
            <a href="<?php echo home_url('/dashboard'); ?>" class="text-[10px] text-gray-500 hover:text-white transition">Cockpit</a>
            <a href="<?php echo home_url('/contact'); ?>" class="text-[10px] text-gray-500 hover:text-white transition">Contact</a>
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

        function openTokenModal(tokenId, rank, points) {
            document.getElementById('modal-token-id').textContent = '#' + tokenId;
            document.getElementById('modal-token-rank').textContent = '#' + rank;
            document.getElementById('modal-token-points').innerHTML = points + '<span class="text-xs text-gray-500 ml-1">PT</span>';
            const img = document.getElementById('modal-token-image');
            img.src = imageBaseUrl + tokenId + '.png';
            img.onerror = function() {
                this.src = fallbackImage;
                this.style.opacity = '0.5';
            };
            
            const modal = document.getElementById('token-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
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