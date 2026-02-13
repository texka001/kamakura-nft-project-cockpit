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

// Default to latest season
$selected_season = isset($_GET['season']) ? sanitize_text_field($_GET['season']) : (!empty($all_seasons) ? $all_seasons[0] : '');

// Fetch Point History
$point_history = array();
if (!empty($user_tokens) && $selected_season) {
    $placeholders = implode(',', array_fill(0, count($user_tokens), '%s'));
    $query = $wpdb->prepare(
        "SELECT * FROM $table_token_ksp 
         WHERE token_id IN ($placeholders) AND season = %s 
         ORDER BY acquisition_date DESC, id DESC",
        array_merge($user_tokens, array($selected_season))
    );
    $point_history = $wpdb->get_results($query);
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
                class="text-kmnft-green font-bold tracking-widest text-lg hover:opacity-80 transition">KAMAKURA STADIUM
                NFT PORTAL(β)</a>
        </div>
        <div class="flex items-center space-x-4 ml-auto">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">DASHBOARD</a>
            <a href="<?php echo home_url('/points'); ?>"
                class="px-4 py-1 border border-kmnft-green text-kmnft-green rounded text-xs transition">POINTS</a>
            <a href="<?php echo home_url('/ranking'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">RANKING</a>
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
            <h1 class="text-2xl font-bold neon-text uppercase tracking-widest">Point History</h1>

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
                                            <div
                                                class="w-10 h-10 rounded border border-gray-700 overflow-hidden bg-gray-900 flex-shrink-0 group-hover/row:border-kmnft-green transition duration-300">
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
        let currentSortCol = -1;
        let isAsc = true;

        function sortTable(colIndex, type) {
            const table = document.getElementById("points-table");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            if (rows.length <= 1 && rows[0].cells.length === 1) return; // Empty message row

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

            // Re-append rows
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>

</body>

</html>