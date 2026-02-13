<?php
/**
 * Template Name: KMNFT Full Gallery
 */

// Initial Data Fetch
global $wpdb;
$initial_limit = 24;
$initial_query = $wpdb->prepare(
    "SELECT DISTINCT token_id FROM {$wpdb->prefix}kmnft_holdings ORDER BY id DESC LIMIT %d",
    $initial_limit
);
$initial_tokens = $wpdb->get_col($initial_query);
$total_count = $wpdb->get_var("SELECT COUNT(DISTINCT token_id) FROM {$wpdb->prefix}kmnft_holdings");
$has_more = ($initial_limit < $total_count);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFT Gallery - KAMAKURA STADIUM NFT PORTAL(β)</title>
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
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <!-- Navbar -->
    <header class="w-full h-16 glass-card flex items-center justify-between px-6 fixed top-0 z-50">
        <div class="flex items-center space-x-4">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="text-kmnft-green font-bold tracking-widest text-lg hover:text-white transition">
                KAMAKURA STADIUM NFT PORTAL(β)
            </a>
        </div>
        <div class="flex items-center space-x-4 ml-auto">
            <a href="<?php echo home_url('/dashboard'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">DASHBOARD</a>
            <a href="<?php echo home_url('/points'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">POINTS</a>
            <a href="<?php echo home_url('/ranking'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">RANKING</a>
            <a href="<?php echo home_url('/contact'); ?>"
                class="px-4 py-1 border border-gray-600 text-gray-300 rounded text-xs hover:border-kmnft-green hover:text-kmnft-green transition">CONTACT</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-24 px-6 pb-10 max-w-7xl mx-auto w-full">

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-white tracking-wide border-b-2 border-kmnft-green pb-2">
                FULL NFT GALLERY
            </h1>
            <span class="text-sm text-gray-400">
                Total: <span class="text-kmnft-green font-mono">
                    <?php echo number_format($total_count); ?>
                </span> Items
            </span>
        </div>

        <!-- Gallery Grid -->
        <div id="gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php
            if ($initial_tokens) {
                foreach ($initial_tokens as $token_id) {
                    $original_url = KMNFT_IMAGE_BASE_URL . esc_attr($token_id) . '.png';
                    // Use cache if available
                    $thumb_url = function_exists('kmnft_get_remote_thumbnail')
                        ? kmnft_get_remote_thumbnail($original_url, $token_id)
                        : $original_url;
                    ?>
                    <div class="gallery-item aspect-square rounded-lg overflow-hidden border border-gray-800 hover:border-kmnft-green transition cursor-pointer relative group"
                        onclick="openImageModal('<?php echo $original_url; ?>')">
                        <img src="<?php echo $thumb_url; ?>" alt="Token <?php echo esc_attr($token_id); ?>"
                            class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-300"
                            loading="lazy"
                            onerror="this.src='<?php echo get_template_directory_uri(); ?>/assets/images/creative_logo.jpg';this.style.opacity='0.5';">
                        <div
                            class="absolute bottom-0 inset-x-0 bg-black/60 p-2 translate-y-full group-hover:translate-y-0 transition duration-300">
                            <span class="text-xs font-mono text-white block text-center">#
                                <?php echo esc_html($token_id); ?>
                            </span>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="text-gray-500 col-span-full text-center py-10">No NFTs found.</p>';
            }
            ?>
        </div>

        <!-- Load More Button -->
        <?php if ($has_more): ?>
            <div class="mt-10 text-center">
                <button id="load-more-btn"
                    class="px-8 py-3 bg-kmnft-navy border border-gray-700 hover:border-kmnft-green text-sm text-gray-300 hover:text-kmnft-green rounded transition uppercase tracking-wider flex items-center justify-center gap-2 mx-auto disabled:opacity-50 disabled:cursor-not-allowed">
                    <span>LOAD MORE</span>
                    <svg id="spinner" class="animate-spin h-4 w-4 text-kmnft-green hidden"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </div>
        <?php endif; ?>

    </main>

    <!-- Image Modal -->
    <div id="image-modal"
        class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/90 opacity-0 transition-opacity duration-300"
        onclick="closeImageModal()">
        <img id="modal-img" src=""
            class="max-w-[90%] max-h-[90%] rounded-lg shadow-[0_0_20px_rgba(0,255,65,0.3)] transform scale-95 transition-transform duration-300"
            onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-white hover:text-kmnft-green transition" onclick="closeImageModal()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <script>
        // Modal Logic
        function openImageModal(src) {
            const modal = document.getElementById('image-modal');
            const img = document.getElementById('modal-img');
            img.src = src;
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                img.classList.remove('scale-95');
                img.classList.add('scale-100');
            }, 10);
        }

        function closeImageModal() {
            const modal = document.getElementById('image-modal');
            const img = document.getElementById('modal-img');
            modal.classList.add('opacity-0');
            img.classList.remove('scale-100');
            img.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                img.src = '';
            }, 300);
        }

        // Load More Logic
        let currentPage = 1;
        const limit = 24;
        const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        const nonce = '<?php echo wp_create_nonce('kmnft_dashboard_nonce'); ?>';

        const loadMoreBtn = document.getElementById('load-more-btn');
        const spinner = document.getElementById('spinner');
        const grid = document.getElementById('gallery-grid');

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function () {
                // Formatting
                const btnText = loadMoreBtn.querySelector('span');
                loadMoreBtn.disabled = true;
                btnText.textContent = 'LOADING...';
                spinner.classList.remove('hidden');

                // Request
                const nextPage = currentPage + 1;
                const formData = new FormData();
                formData.append('action', 'kmnft_load_more_gallery');
                formData.append('nonce', nonce);
                formData.append('page', nextPage);
                formData.append('limit', limit);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.data.html) {
                                grid.insertAdjacentHTML('beforeend', data.data.html);
                                currentPage = nextPage;
                            }

                            if (!data.data.has_more) {
                                loadMoreBtn.style.display = 'none';
                            }
                        } else {
                            console.error('Error:', data.data.message);
                            alert('Error loading images.');
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        alert('Network error.');
                    })
                    .finally(() => {
                        loadMoreBtn.disabled = false;
                        btnText.textContent = 'LOAD MORE';
                        spinner.classList.add('hidden');
                    });
            });
        }
    </script>
</body>

</html>