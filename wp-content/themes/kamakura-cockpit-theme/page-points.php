<?php
/**
 * Template Name: KMNFT Point History
 */

global $wpdb;

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

$current_user = wp_get_current_user();

// Define table names
$table_token_ksp = $wpdb->prefix . 'kmnft_token_ksp';
$table_holdings = $wpdb->prefix . 'kmnft_holdings';

// Get available seasons for the user's tokens
$user_tokens = $wpdb->get_col($wpdb->prepare("SELECT token_id FROM $table_holdings WHERE user_id = %d", $current_user->ID));

$all_seasons = array();
if (!empty($user_tokens)) {
    $placeholders = implode(',', array_fill(0, count($user_tokens), '%s'));
    $all_seasons = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT season FROM $table_token_ksp WHERE token_id IN ($placeholders) ORDER BY season DESC",
        $user_tokens
    ));
}

// Determine current season (Starts April 1st)
$month = intval(date('n'));
$year = intval(date('Y'));
$current_season_str = strval(($month >= 4) ? $year : $year - 1);

// Ensure current season is in the list
if (!in_array($current_season_str, $all_seasons)) {
    array_unshift($all_seasons, $current_season_str);
    // Re-sort just in case, though current is usually the latest
    rsort($all_seasons);
}

// Default to latest season
$selected_season = isset($_GET['season']) ? sanitize_text_field($_GET['season']) : (!empty($all_seasons) ? $all_seasons[0] : '');

// Fetch Point History
$point_history = array();
$tokens_ksp_summary = array();
$tokens_ksp_history = array();
$holdings_data = array();
$latest_season_label = $selected_season;

if (!empty($user_tokens) && $selected_season) {
    // 1. Point History Details
    $placeholders = implode(',', array_fill(0, count($user_tokens), '%s'));
    $query = $wpdb->prepare(
        "SELECT * FROM $table_token_ksp 
         WHERE token_id IN ($placeholders) AND season = %s 
         ORDER BY acquisition_date DESC, id DESC",
        array_merge($user_tokens, array($selected_season))
    );
    $point_history = $wpdb->get_results($query);

    // 2. Token Level KSP Summary and History (for the modal)
    if (class_exists('KMNFT_User_Manager')) {
        $kmnft_manager = new KMNFT_User_Manager();
        $tokens_ksp_summary = $kmnft_manager->get_tokens_ksp_summary($user_tokens, $selected_season);
        $tokens_ksp_history = $kmnft_manager->get_tokens_ksp_history($user_tokens);
    }

    // 3. Fetch Coordinates for all relevant tokens
    $holdings_results = $wpdb->get_results($wpdb->prepare(
        "SELECT token_id, zone_x, zone_y FROM $table_holdings WHERE user_id = %d AND token_id IN ($placeholders)",
        array_merge(array($current_user->ID), $user_tokens)
    ));
    foreach ($holdings_results as $h) {
        $holdings_data[$h->token_id] = $h;
    }

    // 4. Fetch User Summary for the selected season (Total Points & Rank)
    $user_summary = $kmnft_manager->get_user_ksp_summary($current_user->ID);
    $selected_season_stats = null;
    foreach ($user_summary as $stats) {
        if ($stats->season === $selected_season) {
            $selected_season_stats = $stats;
            break;
        }
    }
}

$is_logged_in = is_user_logged_in();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Points - KAMAKURA STADIUM NFT PORTAL(β)</title>
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
                class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs transition">POINTS</a>
            <a href="<?php echo home_url('/ranking'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">RANKING</a>
            <a href="<?php echo home_url('/contact'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">CONTACT</a>
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
            <a href="<?php echo home_url('/points'); ?>" class="kmnft-drawer-link border border-kmnft-green text-kmnft-green text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:bg-kmnft-green hover:text-black transition">POINTS</a>
            <a href="<?php echo home_url('/ranking'); ?>" class="kmnft-drawer-link border border-gray-600 text-gray-300 text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:border-kmnft-green hover:text-kmnft-green transition">RANKING</a>
            <a href="<?php echo home_url('/contact'); ?>" class="kmnft-drawer-link border border-gray-600 text-gray-300 text-sm font-bold px-4 py-2 rounded text-center tracking-widest hover:border-kmnft-green hover:text-kmnft-green transition">CONTACT</a>
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



    <main class="flex-grow pt-24 px-6 pb-10 max-w-5xl mx-auto w-full">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold neon-text uppercase tracking-widest">Point History</h1>
                <p class="text-xs text-gray-400 mt-2">このページでは、あなたが現在保有しているNFTが獲得したポイントの明細を表示しています。</p>
                <p class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider">This page displays the points earned
                    by the NFTs you currently own.</p>
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

        <!-- Season Stats Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
            <div class="glass-card p-6 rounded-lg border-l-4 border-kmnft-gold">
                <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Season Total KSP</div>
                <div class="text-3xl font-bold text-kmnft-gold font-mono">
                    <?php echo $selected_season_stats ? number_format($selected_season_stats->total_points) : '0'; ?><span
                        class="text-xs text-gray-500 ml-1">PT</span>
                </div>
            </div>
            <div class="glass-card p-6 rounded-lg border-l-4 border-kmnft-green">
                <div class="text-[10px] text-gray-500 uppercase tracking-widest mb-1">Season Rank</div>
                <div class="text-3xl font-bold text-kmnft-green neon-text">
                    <?php echo ($selected_season_stats && $selected_season_stats->rank > 0) ? '#' . esc_html($selected_season_stats->rank) : '-'; ?>
                </div>
            </div>
        </div>

        <!-- Point History Table -->
        <div class="glass-card rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table id="points-table" class="w-full text-left text-sm whitespace-nowrap md:whitespace-normal">
                    <thead class="bg-white/5 text-gray-400 uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="px-4 py-3 font-medium cursor-pointer hover:text-kmnft-green transition group"
                                onclick="sortTable(0, 'date')">
                                <div class="flex items-center gap-1">
                                    Date
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-3 w-3 opacity-0 group-hover:opacity-100 transition" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-medium cursor-pointer hover:text-kmnft-green transition group"
                                onclick="sortTable(1, 'number')">
                                <div class="flex items-center gap-1">
                                    Token ID
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-3 w-3 opacity-0 group-hover:opacity-100 transition" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-medium text-right cursor-pointer hover:text-kmnft-green transition group"
                                onclick="sortTable(2, 'number')">
                                <div class="flex items-center justify-end gap-1">
                                    Points
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-3 w-3 opacity-0 group-hover:opacity-100 transition" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-medium">Reason 1</th>
                            <th class="px-4 py-3 font-medium">Reason 2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($point_history)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">No point history
                                    available for this season.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($point_history as $item):
                                $image_url = KMNFT_IMAGE_BASE_URL . esc_attr($item->token_id) . '.png';
                                $token_summary = isset($tokens_ksp_summary[$item->token_id]) ? $tokens_ksp_summary[$item->token_id] : null;
                                $pts = $token_summary ? number_format($token_summary->total_points) : '0';
                                $rnk = ($token_summary && $token_summary->rank > 0) ? $token_summary->rank : '';
                                $holding = isset($holdings_data[$item->token_id]) ? $holdings_data[$item->token_id] : null;
                                $zx = $holding ? $holding->zone_x : '';
                                $zy = $holding ? $holding->zone_y : '';
                                ?>
                                <tr class="hover:bg-white/5 transition group/row"
                                    data-date="<?php echo esc_attr($item->acquisition_date); ?>"
                                    data-token="<?php echo esc_attr($item->token_id); ?>"
                                    data-points="<?php echo esc_attr($item->acquisition_point); ?>">
                                    <td class="px-4 py-4 text-xs font-mono text-gray-400">
                                        <?php echo esc_html($item->acquisition_date); ?>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded border border-gray-700 overflow-hidden bg-gray-900 flex-shrink-0 group-hover/row:border-kmnft-green transition duration-300 cursor-pointer"
                                                onclick="openTokenModal('<?php echo esc_js($item->token_id); ?>', '<?php echo esc_js($rnk); ?>', '<?php echo esc_js($pts); ?>', '<?php echo esc_js($zx); ?>', '<?php echo esc_js($zy); ?>', '<?php echo esc_js($selected_season); ?>')">
                                                <img src="<?php echo $image_url; ?>"
                                                    alt="<?php echo esc_attr($item->token_id); ?>"
                                                    class="w-full h-full object-cover group-hover/row:scale-110 transition duration-500"
                                                    onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';this.style.opacity='0.5';">
                                            </div>
                                            <span
                                                class="font-mono text-white text-sm">#<?php echo esc_html($item->token_id); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-bold text-kmnft-green font-mono">
                                        +<?php echo number_format($item->acquisition_point); ?>
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-300">
                                        <?php echo esc_html($item->reason_1); ?>
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-400">
                                        <?php echo esc_html($item->reason_2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="text-[10px] text-gray-500 mt-4 px-2 italic">* ポイントの明細はシーズンごとに集計されます。項目名をクリックすると並び替えができます。</p>
    </main>

    <footer class="mt-auto py-10 border-t border-gray-800 text-center">
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
        const imageBaseUrl = '<?php echo KMNFT_IMAGE_BASE_URL; ?>';
        const fallbackImage = '<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';
        const latestSeasonLabel = '<?php echo esc_js($selected_season); ?>';
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
            img.style.opacity = '1';
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
            setTimeout(() => {
                document.getElementById('modal-token-image').src = '';
            }, 200);
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeTokenModal();
            }
        });

        let currentSortCol = -1;
        let isAsc = true;

        function sortTable(colIndex, type) {
            const table = document.getElementById("points-table");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            if (rows.length <= 1 && rows[0].cells.length === 1) return;

            if (currentSortCol === colIndex) {
                isAsc = !isAsc;
            } else {
                isAsc = true;
                currentSortCol = colIndex;
            }

            rows.sort((a, b) => {
                let valA, valB;

                if (colIndex === 0) { // Date
                    valA = a.getAttribute('data-date');
                    valB = b.getAttribute('data-date');
                } else if (colIndex === 1) { // Token ID
                    valA = parseInt(a.getAttribute('data-token'));
                    valB = parseInt(b.getAttribute('data-token'));
                } else if (colIndex === 2) { // Points
                    valA = parseInt(a.getAttribute('data-points'));
                    valB = parseInt(b.getAttribute('data-points'));
                }

                if (type === 'number') {
                    return isAsc ? valA - valB : valB - valA;
                } else {
                    return isAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
                }
            });

            rows.forEach(row => tbody.appendChild(row));
        }
    </script>

    <!-- Asset Detail Modal -->
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

</body>

</html>