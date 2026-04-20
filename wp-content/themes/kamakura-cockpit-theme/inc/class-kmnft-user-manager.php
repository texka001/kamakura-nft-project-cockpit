<?php
/**
 * Handles User Management and CSV Import/Export.
 */
class KMNFT_User_Manager
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        // User Actions
        add_action('admin_post_kmnft_import_users', array($this, 'process_csv_import'));
        add_action('admin_post_kmnft_download_sample', array($this, 'process_download_sample')); // New Action
        add_action('admin_post_kmnft_export_users', array($this, 'process_user_export'));
        add_action('admin_post_kmnft_delete_users', array($this, 'process_user_delete'));

        // Asset Actions
        add_action('admin_post_kmnft_import_assets', array($this, 'process_asset_csv_import'));
        add_action('admin_post_kmnft_download_sample_assets', array($this, 'process_download_sample_assets')); // New Action
        add_action('admin_post_kmnft_export_assets', array($this, 'process_asset_export'));
        add_action('admin_post_kmnft_delete_assets', array($this, 'process_asset_delete'));

        // Token KSP Actions
        add_action('admin_post_kmnft_import_token_ksp', array($this, 'process_token_ksp_import'));
        add_action('admin_post_kmnft_download_sample_token_ksp', array($this, 'process_download_sample_token_ksp'));
        add_action('admin_post_kmnft_export_token_ksp', array($this, 'process_token_ksp_export'));
        add_action('admin_post_kmnft_delete_token_ksp', array($this, 'process_token_ksp_delete'));
        add_action('admin_post_kmnft_delete_token_ksp_by_date', array($this, 'process_token_ksp_delete_by_date'));
        add_action('admin_post_kmnft_delete_token_ksp_by_only_date', array($this, 'process_token_ksp_delete_by_only_date'));
        add_action('admin_post_kmnft_delete_token_ksp_by_season', array($this, 'process_token_ksp_delete_by_season'));
        add_action('admin_post_kmnft_aggregate_token_ksp', array($this, 'process_token_ksp_aggregation'));
        add_action('admin_post_kmnft_export_token_summary', array($this, 'process_token_summary_export'));
        add_action('admin_post_kmnft_export_user_summary', array($this, 'process_user_summary_export'));

        // Match Results Actions
        add_action('admin_post_kmnft_save_match', array($this, 'process_match_save'));
        add_action('admin_post_kmnft_delete_match', array($this, 'process_match_delete'));

        // Standings Actions
        add_action('admin_post_kmnft_save_standings', array($this, 'process_standings_save'));
        add_action('admin_post_kmnft_delete_standings', array($this, 'process_standings_delete'));
        add_action('admin_post_kmnft_download_standings', array($this, 'process_standings_download'));
        add_action('admin_post_kmnft_download_sample_standings_csv', array($this, 'process_download_sample_standings_csv'));

        // League Schedule Actions
        add_action('admin_post_kmnft_save_league_schedule', array($this, 'process_league_schedule_save'));
        add_action('admin_post_kmnft_delete_league_schedule', array($this, 'process_league_schedule_delete'));
        add_action('admin_post_kmnft_download_sample_league_schedule_csv', array($this, 'process_download_sample_league_schedule_csv'));
        add_action('admin_post_kmnft_download_league_schedule_csv', array($this, 'process_download_league_schedule_csv'));

        // Settings Actions
        add_action('admin_post_kmnft_save_settings', array($this, 'process_settings_save'));

        // Icon Settings Actions
        add_action('admin_post_kmnft_upload_default_icon', array($this, 'process_icon_upload'));
        add_action('admin_post_kmnft_delete_default_icon', array($this, 'process_icon_delete'));

        // Enqueue Admin Scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Ensure Tables Exist
        $this->ensure_match_table();
        $this->ensure_standings_table();
        $this->ensure_league_schedule_table();
        $this->ensure_token_ksp_table();
        $this->ensure_ksp_summary_tables();
    }

    public function enqueue_admin_scripts()
    {
        if (isset($_GET['page']) && ($_GET['page'] === 'kmnft-match-results' || $_GET['page'] === 'kmnft-standings' || $_GET['page'] === 'kmnft-league-schedule')) {
            wp_enqueue_media();
        }
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Kamakura NFT Console',
            'KMNFT Console',
            'manage_options',
            'kmnft-console',
            array($this, 'render_admin_page'),
            'dashicons-tickets',
            55
        );
        add_submenu_page(
            'kmnft-console',
            'User Import',
            'User Import',
            'manage_options',
            'kmnft-user-import',
            array($this, 'render_import_page')
        );
        add_submenu_page(
            'kmnft-console',
            'Asset Import',
            'Asset Import',
            'manage_options',
            'kmnft-asset-import',
            array($this, 'render_asset_import_page')
        );
        add_submenu_page(
            'kmnft-console',
            'Token KSP',
            'Token KSP',
            'manage_options',
            'kmnft-token-ksp',
            array($this, 'render_token_ksp_page')
        );
        add_submenu_page(
            'kmnft-console',
            'Aggregation',
            'Aggregation',
            'manage_options',
            'kmnft-token-ksp-aggregation',
            array($this, 'render_ksp_aggregation_page')
        );
        add_submenu_page(
            'kmnft-console',
            'Match Results',
            'Match Results',
            'manage_options',
            'kmnft-match-results',
            array($this, 'render_match_results_page')
        );
        add_submenu_page(
            'kmnft-console',
            'League Standings',
            'Standings',
            'manage_options',
            'kmnft-standings',
            array($this, 'render_standings_page')
        );
        add_submenu_page(
            'kmnft-console',
            'League Schedule',
            'League Schedule',
            'manage_options',
            'kmnft-league-schedule',
            array($this, 'render_league_schedule_page')
        );
        add_submenu_page(
            'kmnft-console',
            'Settings',
            'Settings',
            'manage_options',
            'kmnft-settings',
            array($this, 'render_settings_page')
        );
        add_submenu_page(
            'kmnft-console',
            'Icon Settings',
            'Icon Settings',
            'manage_options',
            'kmnft-icon-settings',
            array($this, 'render_icon_settings_page')
        );
    }

    public function render_admin_page()
    {
        echo '<div class="wrap"><h1>Kamakura NFT Cockpit Console</h1><p>Select a submenu to manage users or assets.</p></div>';
    }

    public function render_import_page()
    {
        ?>
        <div class="wrap">
            <h1>User Import & Tools</h1>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> <?php echo intval($_GET['count']); ?> users processed successfully.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'deleted'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> <?php echo intval($_GET['count']); ?> users deleted.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error:</strong>
                            <?php echo isset($_GET['msg']) ? esc_html($_GET['msg']) : 'Failed to process request.'; ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            $skipped_users = get_transient('kmnft_import_skipped_users');
            if ($skipped_users):
                delete_transient('kmnft_import_skipped_users');
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong>Notice:</strong> The following <?php echo count($skipped_users); ?> users were skipped because they
                        already exist (LoginID or Email duplication).</p>
                    <table class="wp-list-table widefat fixed striped" style="margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th>Login ID</th>
                                <th>Email</th>
                                <th>Display Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($skipped_users as $user): ?>
                                <tr>
                                    <td><?php echo esc_html($user['login_id']); ?></td>
                                    <td><?php echo esc_html($user['email']); ?></td>
                                    <td><?php echo esc_html($user['display_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2>Batch Import Users</h2>
                <p>Upload a CSV file to bulk import/update users and their NFT holdings.</p>

                <h3>CSVフォーマット仕様</h3>
                <p><strong>カラム順:</strong> <code>login_id</code>, <code>email</code>, <code>password</code>,
                    <code>display_name</code>
                </p>
                <p><em>注意: このインポート処理によってユーザーが作成・更新されても、メール通知は<strong>送信されません</strong>。</em></p>
                <p><em>注意: CSVの1行目はヘッダー行として扱われ、<strong>無視されます</strong>。データは2行目から記述してください。</em></p>



                <p>
                    <a href="<?php echo admin_url('admin-post.php?action=kmnft_download_sample'); ?>"
                        class="button button-secondary">
                        <span class="dashicons dashicons-download" style="vertical-align: text-bottom;"></span> Sample CSV
                        Download
                    </a>
                </p>

                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="kmnft_import_users">
                    <?php wp_nonce_field('kmnft_import_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="csv_file">CSV File</label></th>
                            <td><input type="file" name="csv_file" id="csv_file" accept=".csv" required></td>
                        </tr>
                    </table>
                    <?php submit_button('Import Users'); ?>
                </form>
            </div>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2>Export Users</h2>
                <p>Download the current list of registered users as a CSV file (Password excluded).</p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="kmnft_export_users">
                    <?php wp_nonce_field('kmnft_user_export_nonce', 'kmnft_nonce'); ?>
                    <?php submit_button('Download User CSV', 'secondary'); ?>
                </form>
            </div>

            <div
                style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px; border-left: 4px solid #d63638;">
                <h2 style="color: #d63638;">Delete Users</h2>
                <p>Delete users by specifying their Login IDs (e.g., k779...). You can enter multiple IDs (one per line or comma
                    separated).</p>
                <p><strong>Warning:</strong> This will delete the user and all linked data. This cannot be undone.</p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                    onsubmit="return confirm('Are you sure you want to PERMANENTLY delete these users?');">
                    <input type="hidden" name="action" value="kmnft_delete_users">
                    <?php wp_nonce_field('kmnft_user_delete_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="login_ids">Login IDs</label></th>
                            <td>
                                <textarea name="login_ids" id="login_ids" rows="5" class="large-text code"
                                    placeholder="k779000010&#10;k779000020"></textarea>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Delete Users', 'delete'); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function render_asset_import_page()
    {
        ?>
        <div class="wrap">
            <h1>Asset Ownership Batch Import & Tools</h1>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> <?php echo intval($_GET['count']); ?> assets processed successfully.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'deleted'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> <?php echo intval($_GET['count']); ?> assets deleted.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error:</strong>
                            <?php echo isset($_GET['msg']) ? esc_html($_GET['msg']) : 'Failed to process request.'; ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2>Batch Import</h2>
                <p>Upload a CSV file to assign Token IDs to Users (Login IDs) with Zone coordinates.</p>

                <h3>CSVフォーマット仕様</h3>
                <p><strong>カラム順:</strong> <code>token_id</code>, <code>zone_x</code>, <code>zone_y</code>, <code>login_id</code>
                </p>
                <p><em>注意: <code>zone_x</code>, <code>zone_y</code> は3桁の数字などを推奨。省略時は空になります。</em></p>
                <p><em>注意: CSVの1行目はヘッダー行として扱われ、<strong>無視されます</strong>。</em></p>
                <p><em>注意: 同じアセット番号のデータをアップロードした場合、データを上書きます。</em></p>

                <p>
                    <a href="<?php echo admin_url('admin-post.php?action=kmnft_download_sample_assets'); ?>"
                        class="button button-secondary">
                        <span class="dashicons dashicons-download" style="vertical-align: text-bottom;"></span> Sample CSV
                        Download
                    </a>
                </p>

                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="kmnft_import_assets">
                    <?php wp_nonce_field('kmnft_asset_import_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="csv_file">CSV File</label></th>
                            <td><input type="file" name="csv_file" id="csv_file" accept=".csv" required></td>
                        </tr>
                    </table>
                    <?php submit_button('Import Assets'); ?>
                </form>
            </div>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2>Export Assets</h2>
                <p>Download the current list of registered assets as a CSV file.</p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="kmnft_export_assets">
                    <?php wp_nonce_field('kmnft_asset_export_nonce', 'kmnft_nonce'); ?>
                    <?php submit_button('Download CSV', 'secondary'); ?>
                </form>
            </div>

            <div
                style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px; border-left: 4px solid #d63638;">
                <h2 style="color: #d63638;">Delete Assets</h2>
                <p>Delete assets by specifying their Token IDs. You can enter multiple IDs (one per line or comma separated).
                </p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                    onsubmit="return confirm('Are you sure you want to delete these assets? This action cannot be undone.');">
                    <input type="hidden" name="action" value="kmnft_delete_assets">
                    <?php wp_nonce_field('kmnft_asset_delete_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="token_ids">Token IDs</label></th>
                            <td>
                                <textarea name="token_ids" id="token_ids" rows="5" class="large-text code"
                                    placeholder="ID1&#10;ID2"></textarea>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Delete Assets', 'delete'); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function render_token_ksp_page()
    {
        ?>
        <div class="wrap">
            <h1>Token KSP Data Management</h1>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible">
                        <?php if (isset($_GET['msg'])): ?>
                            <p><strong>Success!</strong> <?php echo esc_html($_GET['msg']); ?></p>
                        <?php else: ?>
                            <p><strong>Success!</strong> <?php echo isset($_GET['count']) ? intval($_GET['count']) : 0; ?> records processed
                                successfully.
                                <?php if (isset($_GET['skipped']) && intval($_GET['skipped']) > 0): ?>
                                    (Skipped <?php echo intval($_GET['skipped']); ?> records with no points)
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($_GET['status'] === 'deleted'): ?>
                    <div class="notice notice-success is-dismissible">
                        <?php if (isset($_GET['msg'])): ?>
                            <p><strong>Success!</strong> <?php echo esc_html($_GET['msg']); ?></p>
                        <?php else: ?>
                            <p><strong>Success!</strong> <?php echo isset($_GET['count']) ? intval($_GET['count']) : 0; ?> records deleted.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error:</strong>
                            <?php echo isset($_GET['msg']) ? esc_html($_GET['msg']) : 'Failed to process request.'; ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2>Batch Import Token KSP</h2>
                <p>Upload a CSV file to register acquisition points for Tokens.</p>

                <h3>CSV Format Specification</h3>
                <p><strong>Column Order:</strong> <code>token_id</code>, <code>acquisition_date</code>,
                    <code>acquisition_point</code>, <code>season</code>, <code>reason_1</code>, <code>reason_2</code>
                </p>
                <p><em>Note: Date format can be YYYY-MM-DD or YYYY/MM/DD (e.g. 2025/1/1).</em></p>
                <p><em>Note: Season is optional (e.g. 2026). Reasons are optional text notes.</em></p>
                <p><em>Note: First row matches header and is ignored.</em></p>
                <p style="color: #d63638;"><strong>重要:</strong> データは常に追記（INSERT）されます。同じ日付の既存データは更新されません。重複ファイルのアップロードにご注意ください。
                </p>

                <p style="background: #fff8e1; border-left: 4px solid #ffb900; padding: 10px; margin-bottom: 20px;">
                    <strong>案内:</strong> インポートしたデータをランキングやサマリに反映させるには、別メニューの「<a
                        href="<?php echo admin_url('admin.php?page=kmnft-token-ksp-aggregation'); ?>">Aggregation</a>」にて集計（Run
                    Aggregation）を実行してください。
                </p>

                <p>
                    <a href="<?php echo admin_url('admin-post.php?action=kmnft_download_sample_token_ksp'); ?>"
                        class="button button-secondary">
                        <span class="dashicons dashicons-download" style="vertical-align: text-bottom;"></span> Sample CSV
                        Download
                    </a>
                </p>

                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="kmnft_import_token_ksp">
                    <?php wp_nonce_field('kmnft_token_ksp_import_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="csv_file">CSV File</label></th>
                            <td><input type="file" name="csv_file" id="csv_file" accept=".csv" required></td>
                        </tr>
                    </table>
                    <?php submit_button('Import Token KSP'); ?>
                </form>
            </div>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2>Export Token KSP</h2>
                <p>Download the current list of Token KSP records. <br>
                    <span style="color: #d63638;"><strong>Note:</strong> At least one filter (Season, Token ID, or Acquisition
                        Date) must be specified.</span>
                </p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="kmnft_export_token_ksp">
                    <?php wp_nonce_field('kmnft_token_ksp_export_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="export_season">Season</label></th>
                            <td><input type="text" name="season" id="export_season" class="regular-text"
                                    placeholder="e.g. 2026"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="export_token_id">Token ID</label></th>
                            <td><input type="text" name="token_id" id="export_token_id" class="regular-text"
                                    placeholder="e.g. 12345678901"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="export_acquisition_date">Acquisition Date</label></th>
                            <td>
                                <input type="date" name="acquisition_date" id="export_acquisition_date" class="regular-text">
                                <p class="description">Only records from this specific date will be exported.</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Download Filtered CSV', 'secondary'); ?>
                </form>
            </div>

            <div
                style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px; border-left: 4px solid #d63638;">
                <h2 style="color: #d63638;">Delete Token KSP (Token ID)</h2>
                <p>Delete records by specifying Token IDs. All records for the specified Token IDs will be deleted.</p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                    onsubmit="return confirm('Are you sure you want to delete KSP data for these tokens?');">
                    <input type="hidden" name="action" value="kmnft_delete_token_ksp">
                    <?php wp_nonce_field('kmnft_token_ksp_delete_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="token_ids">Token IDs</label></th>
                            <td>
                                <textarea name="token_ids" id="token_ids" rows="5" class="large-text code"
                                    placeholder="ID1&#10;ID2"></textarea>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Delete Token KSP (By Token ID)', 'delete'); ?>
                </form>
            </div>

            <div
                style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px; border-left: 4px solid #d63638;">
                <h2 style="color: #d63638;">Delete Token KSP (Date)</h2>
                <p>Delete all records for a specific acquisition date. This is useful for undoing a whole batch import.</p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                    onsubmit="return confirm('Are you sure you want to delete ALL KSP records for this date?');">
                    <input type="hidden" name="action" value="kmnft_delete_token_ksp_by_only_date">
                    <?php wp_nonce_field('kmnft_token_ksp_delete_by_only_date_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="delete_date">Acquisition Date</label></th>
                            <td>
                                <input type="date" name="acquisition_date" id="delete_date" class="regular-text" required>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Delete Records for this Date', 'delete'); ?>
                </form>
            </div>

            <div
                style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px; border-left: 4px solid #d63638;">
                <h2 style="color: #d63638;">Delete Token KSP (Year)</h2>
                <p>Delete all records for a specific year (season). This is useful for clearing out a whole season's data.</p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                    onsubmit="return confirm('Are you sure you want to delete ALL KSP records for this Year?');">
                    <input type="hidden" name="action" value="kmnft_delete_token_ksp_by_season">
                    <?php wp_nonce_field('kmnft_token_ksp_delete_by_season_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="delete_season">Year / Season</label></th>
                            <td>
                                <input type="number" name="delete_season" id="delete_season" class="regular-text" placeholder="e.g. 2024" required>
                                <p class="description">Enter the year (e.g., 2024)</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Delete Records for this Year', 'delete'); ?>
                </form>
            </div>

            <div
                style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px; border-left: 4px solid #d63638;">
                <h2 style="color: #d63638;">Delete Token KSP (By Token ID & Date)</h2>
                <p>Delete specific records by specifying Token ID and Acquisition Date pairs.</p>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                    onsubmit="return confirm('Are you sure you want to delete these specific records?');">
                    <input type="hidden" name="action" value="kmnft_delete_token_ksp_by_date">
                    <?php wp_nonce_field('kmnft_token_ksp_delete_by_date_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="token_id_date_pairs">Pairs (TokenID, Date)</label></th>
                            <td>
                                <textarea name="token_id_date_pairs" id="token_id_date_pairs" rows="5" class="large-text code"
                                    placeholder="12345678901, 2024-01-01&#10;12345678901, 2024/1/1"></textarea>
                                <p class="description">Enter one pair per line: <code>TokenID, YYYY-MM-DD</code> or
                                    <code>TokenID, YYYY/MM/DD</code>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Delete Specified Records', 'delete'); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function render_ksp_aggregation_page()
    {
        ?>
        <div class="wrap">
            <h1>KSP Aggregation & Reporting</h1>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible">
                        <?php if (isset($_GET['msg'])): ?>
                            <p><strong>Success!</strong> <?php echo esc_html($_GET['msg']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error:</strong>
                            <?php echo isset($_GET['msg']) ? esc_html($_GET['msg']) : 'Failed to process request.'; ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div
                style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px; border-left: 4px solid #2271b1;">
                <h2>Aggregation Batch (集計バッチ)</h2>
                <p>Run the aggregation process to calculate total KSP for Tokens and Users for a specific Season.</p>
                <p><strong>Note:</strong> This is a "Wash & Replace" operation. Existing summary data for the specified Season
                    will be deleted and recreated.</p>
                <p>ユーザー集計仕様: <strong>現在の保有トークン</strong>に基づき、指定年度の獲得ポイントを合算します。</p>

                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                    onsubmit="return confirm('Start aggregation for this season? This may take a few seconds.');">
                    <input type="hidden" name="action" value="kmnft_aggregate_token_ksp">
                    <input type="hidden" name="redirect_to" value="kmnft-token-ksp-aggregation">
                    <?php wp_nonce_field('kmnft_token_ksp_aggregate_nonce', 'kmnft_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="agg_season">Season (Year)</label></th>
                            <td>
                                <input type="text" name="season" id="agg_season" class="regular-text" placeholder="e.g. 2026"
                                    required value="<?php echo date('Y'); ?>">
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Run Aggregation', 'primary'); ?>
                </form>
            </div>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2>Export Aggregated KSP (集計結果エクスポート)</h2>
                <p>Download the aggregated KSP data for a specific Season.</p>
                <div style="display: flex; align-items: flex-end; gap: 20px;">
                    <div>
                        <label for="export_season_input"
                            style="display:block; margin-bottom: 5px; font-weight:bold;">Season</label>
                        <input type="text" id="export_season_input" class="regular-text" placeholder="e.g. 2026"
                            value="<?php echo date('Y'); ?>">
                    </div>
                    <div>
                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" style="display:inline-block;">
                            <input type="hidden" name="action" value="kmnft_export_token_summary">
                            <input type="hidden" name="season" id="hidden_season_token">
                            <?php wp_nonce_field('kmnft_export_token_summary_nonce', 'kmnft_nonce'); ?>
                            <input type="submit" name="submit" id="submit_token_export" class="button button-secondary"
                                value="Export Token Summary"
                                onclick="document.getElementById('hidden_season_token').value = document.getElementById('export_season_input').value;">
                        </form>
                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                            style="display:inline-block; margin-left: 10px;">
                            <input type="hidden" name="action" value="kmnft_export_user_summary">
                            <input type="hidden" name="season" id="hidden_season_user">
                            <?php wp_nonce_field('kmnft_export_user_summary_nonce', 'kmnft_nonce'); ?>
                            <input type="submit" name="submit" id="submit_user_export" class="button button-secondary"
                                value="Export User Summary"
                                onclick="document.getElementById('hidden_season_user').value = document.getElementById('export_season_input').value;">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // --- SETTINGS FUNCTIONS ---

    public function render_settings_page()
    {
        $prefix = get_option('kmnft_contact_subject_prefix', '[Contact Form]');
        $recipients = get_option('kmnft_contact_recipients', '');
        $cc_recipients = get_option('kmnft_contact_cc_recipients', '');
        ?>
        <div class="wrap">
            <h1>お問い合わせフォーム設定 (Contact Form Settings)</h1>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>保存しました。</strong></p>
                </div>
            <?php endif; ?>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="kmnft_save_settings">
                    <?php wp_nonce_field('kmnft_settings_nonce', 'kmnft_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="subject_prefix">メール件名の接頭辞<br>(Subject Prefix)</label></th>
                            <td>
                                <input type="text" name="subject_prefix" id="subject_prefix"
                                    value="<?php echo esc_attr($prefix); ?>" class="regular-text">
                                <p class="description">
                                    お問い合わせメールの件名の先頭に付く文字列です。<br>
                                    例: <code>【KMNFT】</code> や <code>[お問い合わせ]</code> など。<br>
                                    デフォルト: <code>[Contact Form]</code>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="recipients">TO通知先メールアドレス<br>(TO Recipient Emails)</label></th>
                            <td>
                                <textarea name="recipients" id="recipients" rows="5"
                                    class="large-text code"><?php echo esc_textarea($recipients); ?></textarea>
                                <p class="description">
                                    お問い合わせがあった際に通知を受け取るメインのメールアドレスを入力してください。<br>
                                    <strong>複数入力する場合は、改行して1行に1つのアドレスを入力してください。</strong><br>
                                    <br>
                                    入力例:<br>
                                    <code>admin@example.com</code><br>
                                    <code>support@example.com</code><br>
                                    <br>
                                    ※空欄の場合は、サイトの管理者メールアドレス (<code><?php echo get_option('admin_email'); ?></code>)
                                    に送信されます。
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cc_recipients">CC通知先メールアドレス<br>(CC Recipient Emails)</label></th>
                            <td>
                                <textarea name="cc_recipients" id="cc_recipients" rows="5"
                                    class="large-text code"><?php echo esc_textarea($cc_recipients); ?></textarea>
                                <p class="description">
                                    CCで通知を受け取るメールアドレスを入力してください。<br>
                                    <strong>複数入力する場合は、改行して1行に1つのアドレスを入力してください。</strong>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('設定を保存'); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function process_settings_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_settings_nonce', 'kmnft_nonce');

        $prefix = isset($_POST['subject_prefix']) ? sanitize_text_field($_POST['subject_prefix']) : '[Contact Form]';
        $recipients = isset($_POST['recipients']) ? trim($_POST['recipients']) : '';
        $cc_recipients = isset($_POST['cc_recipients']) ? trim($_POST['cc_recipients']) : '';

        update_option('kmnft_contact_subject_prefix', $prefix);
        update_option('kmnft_contact_recipients', $recipients);
        update_option('kmnft_contact_cc_recipients', $cc_recipients);

        wp_redirect(admin_url('admin.php?page=kmnft-settings&status=success'));
        exit;
    }

    // --- USER FUNCTIONS ---

    public function process_user_export()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_user_export_nonce', 'kmnft_nonce');

        global $wpdb;

        // Fetch Users joined with Meta
        // Note: Using WP_User_Query is safer but direct SQL is faster for simple CSV export
        // We need login, email, display_name, rank (from meta), ksp (from ledger sum)

        // 1. Get Base Users
        $users = $wpdb->get_results("SELECT ID, user_login, user_email, display_name FROM {$wpdb->users}");

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="kmnft_users_export_' . date('Y-m-d') . '.csv"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('login_id', 'email', 'password', 'display_name')); // Header

        foreach ($users as $user) {
            fputcsv($fp, array($user->user_login, $user->user_email, '', $user->display_name));
        }

        fclose($fp);
        exit;
    }

    public function process_user_delete()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_user_delete_nonce', 'kmnft_nonce');

        $input = isset($_POST['login_ids']) ? $_POST['login_ids'] : '';
        if (empty($input)) {
            wp_redirect(admin_url('admin.php?page=kmnft-user-import&status=error&msg=No IDs provided'));
            exit;
        }

        $login_ids = preg_split('/[\r\n,]+/', $input);
        $login_ids = array_map('trim', $login_ids);
        $login_ids = array_filter($login_ids);

        if (empty($login_ids)) {
            wp_redirect(admin_url('admin.php?page=kmnft-user-import&status=error&msg=No valid IDs provided'));
            exit;
        }

        $deleted_count = 0;

        // This process handles WP User delete + Custom Table Cleanup via hooks if setup, 
        // OR we manually cleanup if standard WP delete doesn't catch external tables.
        // For now, wp_delete_user cleans up meta. We might need to manually clean up holdings/ledger if they aren't keyed by user_id with cascade (WP DB usually isn't)
        // But let's rely on manual cleanup here for safety.

        require_once(ABSPATH . 'wp-admin/includes/user.php'); // Required for wp_delete_user

        foreach ($login_ids as $login_id) {
            $user = get_user_by('login', $login_id);
            if ($user) {
                // Delete Custom Data first
                global $wpdb;
                $wpdb->delete($wpdb->prefix . 'kmnft_user_meta', array('user_id' => $user->ID));
                $wpdb->delete($wpdb->prefix . 'kmnft_holdings', array('user_id' => $user->ID));
                $wpdb->delete($wpdb->prefix . 'kmnft_ksp_ledger', array('user_id' => $user->ID));

                // Delete WP User
                if (wp_delete_user($user->ID)) {
                    $deleted_count++;
                }
            }
        }

        wp_redirect(admin_url('admin.php?page=kmnft-user-import&status=deleted&count=' . intval($deleted_count)));
        exit;
    }

    public function process_csv_import()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_import_nonce', 'kmnft_nonce');

        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if ($handle !== FALSE) {
                $header = fgetcsv($handle);
                // Format: login_id, email, password, display_name

                $row_count = 0;
                $skipped_users = array();
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Shift-JIS (Excel形式)等でアップロードされた場合の文字化け・消失対策
                    mb_convert_variables('UTF-8', 'SJIS-win, UTF-8, auto', $data);

                    $login_id = sanitize_user($data[0]);
                    $email = sanitize_email($data[1]);
                    $password = $data[2];
                    $display_name = sanitize_text_field($data[3]);

                    // Skip if User exists
                    $user_id = username_exists($login_id);
                    if (!$user_id) {
                        $user_id = email_exists($email);
                    }

                    if ($user_id) {
                        $skipped_users[] = array(
                            'login_id' => $login_id,
                            'email' => $email,
                            'display_name' => $display_name
                        );
                        continue;
                    }

                    // Create WP User (Only New)
                    $user_id = wp_create_user($login_id, $password, $email);

                    if (!is_wp_error($user_id)) {
                        wp_update_user(array('ID' => $user_id, 'display_name' => $display_name));
                        // ニックネームはユーザIDでご希望のため、$login_id をセット
                        update_user_meta($user_id, 'nickname', $login_id);

                        global $wpdb;

                        $row_count++;
                    }
                }
                fclose($handle);

                if (!empty($skipped_users)) {
                    set_transient('kmnft_import_skipped_users', $skipped_users, 60);
                }

                wp_redirect(admin_url('admin.php?page=kmnft-user-import&status=success&count=' . $row_count));
                exit;
            }
        }
        wp_die('File upload failed.');
    }

    // --- ASSET FUNCTIONS ---

    public function process_download_sample()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $file = get_stylesheet_directory() . '/assets/sample_users.csv';

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="sample_users.csv"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            wp_die('Sample file not found at: ' . $file);
        }
    }

    public function process_download_sample_assets()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $file = get_stylesheet_directory() . '/assets/sample_assets.csv';

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="sample_assets.csv"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            wp_die('Sample file not found at: ' . $file);
        }
    }

    public function process_asset_csv_import()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_asset_import_nonce', 'kmnft_nonce');

        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if ($handle !== FALSE) {
                fgetcsv($handle); // Skip Header

                $row_count = 0;
                global $wpdb;
                $table_name = $wpdb->prefix . 'kmnft_holdings';

                // Ensure DB columns exist (Migration logic on the fly)
                $col_x = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'zone_x'");
                if (empty($col_x)) {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN zone_x VARCHAR(10) DEFAULT ''");
                }
                $col_y = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'zone_y'");
                if (empty($col_y)) {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN zone_y VARCHAR(10) DEFAULT ''");
                }

                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Shift-JIS対策
                    mb_convert_variables('UTF-8', 'SJIS-win, UTF-8, auto', $data);

                    $token_id = sanitize_text_field($data[0]);
                    // New Order: token_id, zone_x, zone_y, login_id
                    $zone_x = isset($data[1]) ? sanitize_text_field($data[1]) : '';
                    $zone_y = isset($data[2]) ? sanitize_text_field($data[2]) : '';
                    $login_id = isset($data[3]) ? sanitize_user($data[3]) : '';

                    if (empty($token_id)) {
                        continue;
                    }

                    $user_id = 0; // Default to unassigned
                    if (!empty($login_id)) {
                        // Find User ID
                        $user = get_user_by('login', $login_id);
                        if (!$user) {
                            continue; // User specified but not found -> skip
                        }
                        $user_id = $user->ID;
                    }

                    // Check if Asset Exists
                    $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE token_id = %s", $token_id));

                    if ($existing_id) {
                        // Update
                        $data_to_update = array('user_id' => $user_id);
                        if (!empty($zone_x))
                            $data_to_update['zone_x'] = $zone_x;
                        if (!empty($zone_y))
                            $data_to_update['zone_y'] = $zone_y;

                        $wpdb->update(
                            $table_name,
                            $data_to_update,
                            array('id' => $existing_id)
                        );
                    } else {
                        // Insert New
                        $wpdb->insert(
                            $table_name,
                            array(
                                'user_id' => $user_id,
                                'token_id' => $token_id,
                                'zone_x' => $zone_x,
                                'zone_y' => $zone_y,
                                'zone_code' => "{$zone_x}-{$zone_y}", // Fallback composite
                                'zone_name' => "Zone {$zone_x}-{$zone_y}" // Fallback name
                            ),
                            array('%d', '%s', '%s', '%s', '%s', '%s')
                        );
                    }
                    $row_count++;
                }
                fclose($handle);
                wp_redirect(admin_url('admin.php?page=kmnft-asset-import&status=success&count=' . $row_count));
                exit;
            }
        }
        wp_die('File upload failed.');
    }

    public function process_asset_export()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_asset_export_nonce', 'kmnft_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_holdings';

        // Fetch data joined with users to get login_id
        $results = $wpdb->get_results(
            "SELECT h.token_id, u.user_login as login_id, h.zone_x, h.zone_y 
             FROM $table_name h 
             LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID"
        );

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="kmnft_assets_export_' . date('Y-m-d') . '.csv"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('token_id', 'zone_x', 'zone_y', 'login_id')); // Header

        foreach ($results as $row) {
            fputcsv($fp, array($row->token_id, $row->zone_x, $row->zone_y, $row->login_id));
        }
        fclose($fp);
        exit;
    }

    public function process_asset_delete()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_asset_delete_nonce', 'kmnft_nonce');

        $input = isset($_POST['token_ids']) ? $_POST['token_ids'] : '';
        if (empty($input)) {
            wp_redirect(admin_url('admin.php?page=kmnft-asset-import&status=error&msg=No IDs provided'));
            exit;
        }

        // Split by newlines and commas
        $token_ids = preg_split('/[\r\n,]+/', $input);
        $token_ids = array_map('trim', $token_ids);
        $token_ids = array_filter($token_ids); // Remove input empty

        if (empty($token_ids)) {
            wp_redirect(admin_url('admin.php?page=kmnft-asset-import&status=error&msg=No valid IDs provided'));
            exit;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_holdings';

        // Prepare placeholders
        $placeholders = implode(',', array_fill(0, count($token_ids), '%s'));

        $sql = "DELETE FROM $table_name WHERE token_id IN ($placeholders)";
        $prepared = $wpdb->prepare($sql, $token_ids);

        $deleted = $wpdb->query($prepared);

        wp_redirect(admin_url('admin.php?page=kmnft-asset-import&status=deleted&count=' . intval($deleted)));
        exit;
    }

    // --- TOKEN KSP FUNCTIONS ---

    public function process_download_sample_token_ksp()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $file = get_stylesheet_directory() . '/assets/sample_token_ksp.csv';

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="sample_token_ksp.csv"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            wp_die('Sample file not found at: ' . $file);
        }
    }

    public function process_token_ksp_import()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_token_ksp_import_nonce', 'kmnft_nonce');

        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if ($handle !== FALSE) {
                fgetcsv($handle); // Skip Header

                $row_count = 0;
                $skip_count = 0;
                global $wpdb;
                $table_name = $wpdb->prefix . 'kmnft_token_ksp';

                // Ensure columns exist (Migration logic on the fly)
                $col_season = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'season'");
                if (empty($col_season)) {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN season VARCHAR(20) DEFAULT ''");
                }
                $col_r1 = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'reason_1'");
                if (empty($col_r1)) {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN reason_1 TEXT");
                }
                $col_r2 = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'reason_2'");
                if (empty($col_r2)) {
                    $wpdb->query("ALTER TABLE $table_name ADD COLUMN reason_2 TEXT");
                }

                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Shift-JIS対策
                    mb_convert_variables('UTF-8', 'SJIS-win, UTF-8, auto', $data);

                    // Format: token_id, acquisition_date, acquisition_point, season, reason_1, reason_2
                    $token_id = sanitize_text_field($data[0]);

                    // Date Parsing
                    $raw_date = sanitize_text_field($data[1]);
                    $time = strtotime(str_replace('/', '-', $raw_date));
                    $acquisition_date = ($time !== false) ? date('Y-m-d', $time) : '';

                    $acquisition_point = isset($data[2]) ? trim($data[2]) : '';
                    $season = isset($data[3]) ? sanitize_text_field($data[3]) : '';
                    $reason_1 = isset($data[4]) ? sanitize_textarea_field($data[4]) : '';
                    $reason_2 = isset($data[5]) ? sanitize_textarea_field($data[5]) : '';

                    if (empty($token_id) || empty($acquisition_date)) {
                        continue;
                    }

                    // Skip if points are 0, NULL, or empty
                    if ($acquisition_point === '' || intval($acquisition_point) === 0) {
                        $skip_count++;
                        continue;
                    }

                    // Always Insert (Append Mode)
                    $wpdb->insert(
                        $table_name,
                        array(
                            'token_id' => $token_id,
                            'acquisition_date' => $acquisition_date,
                            'acquisition_point' => intval($acquisition_point),
                            'season' => $season,
                            'reason_1' => $reason_1,
                            'reason_2' => $reason_2
                        ),
                        array('%s', '%s', '%d', '%s', '%s', '%s')
                    );
                    $row_count++;
                }
                fclose($handle);
                wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=success&count=' . $row_count . '&skipped=' . $skip_count));
                exit;
            }
        }
        wp_die('File upload failed.');
    }

    public function process_token_ksp_export()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_token_ksp_export_nonce', 'kmnft_nonce');

        if (empty($_POST['season']) && empty($_POST['token_id']) && empty($_POST['acquisition_date'])) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=At least one filter (Season, Token ID, or Date) is required for Export.'));
            exit;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_token_ksp';

        $where = array();
        $params = array();

        if (!empty($_POST['season'])) {
            $where[] = "season = %s";
            $params[] = sanitize_text_field($_POST['season']);
        }
        if (!empty($_POST['token_id'])) {
            $where[] = "token_id = %s";
            $params[] = sanitize_text_field($_POST['token_id']);
        }
        if (!empty($_POST['acquisition_date'])) {
            $where[] = "acquisition_date = %s";
            $params[] = sanitize_text_field($_POST['acquisition_date']);
        }

        $sql = "SELECT token_id, acquisition_date, acquisition_point, season, reason_1, reason_2 FROM $table_name WHERE " . implode(' AND ', $where) . " ORDER BY acquisition_date DESC";

        if (!empty($params)) {
            $results = $wpdb->get_results($wpdb->prepare($sql, $params));
        } else {
            $results = $wpdb->get_results($sql);
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="kmnft_token_ksp_export_' . date('Y-m-d') . '.csv"');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('token_id', 'acquisition_date', 'acquisition_point', 'season', 'reason_1', 'reason_2')); // Header

        foreach ($results as $row) {
            fputcsv($fp, array($row->token_id, $row->acquisition_date, $row->acquisition_point, $row->season, $row->reason_1, $row->reason_2));
        }
        fclose($fp);
        exit;
    }

    public function process_token_ksp_delete()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_token_ksp_delete_nonce', 'kmnft_nonce');

        $input = isset($_POST['token_ids']) ? $_POST['token_ids'] : '';
        if (empty($input)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=No IDs provided'));
            exit;
        }

        $token_ids = preg_split('/[\r\n,]+/', $input);
        $token_ids = array_map('trim', $token_ids);
        $token_ids = array_filter($token_ids);

        if (empty($token_ids)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=No valid IDs provided'));
            exit;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_token_ksp';

        $placeholders = implode(',', array_fill(0, count($token_ids), '%s'));
        $sql = "DELETE FROM $table_name WHERE token_id IN ($placeholders)";
        $prepared = $wpdb->prepare($sql, $token_ids);

        $deleted = $wpdb->query($prepared);

        wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=deleted&count=' . intval($deleted)));
        exit;
    }

    public function process_token_ksp_delete_by_date()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_token_ksp_delete_by_date_nonce', 'kmnft_nonce');

        $input = isset($_POST['token_id_date_pairs']) ? $_POST['token_id_date_pairs'] : '';
        if (empty($input)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=No data provided'));
            exit;
        }

        $lines = preg_split('/[\r\n]+/', $input);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines);

        if (empty($lines)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=No valid lines provided'));
            exit;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_token_ksp';
        $deleted_count = 0;

        foreach ($lines as $line) {
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) < 2) {
                continue;
            }
            $token_id = $parts[0];
            if (empty($token_id) || empty($parts[1])) {
                continue;
            }

            // Robust Date Parsing
            $raw_date = trim($parts[1]);
            // Convert slash to dash just in case, then strtotime usually handles YYYY-MM-DD and YYYY/MM/DD well
            // forcing Y-m-d format
            $time = strtotime(str_replace('/', '-', $raw_date));
            if ($time === false) {
                continue; // Invalid date
            }
            $acquisition_date = date('Y-m-d', $time);

            // Using simple delete per row for safety and clarity
            $result = $wpdb->delete(
                $table_name,
                array('token_id' => $token_id, 'acquisition_date' => $acquisition_date),
                array('%s', '%s')
            );

            if ($result !== false && $result > 0) {
                $deleted_count += $result;
            }
        }
        wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=deleted&count=' . intval($deleted_count)));
        exit;
    }

    public function process_token_ksp_delete_by_only_date()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_token_ksp_delete_by_only_date_nonce', 'kmnft_nonce');

        $date = isset($_POST['acquisition_date']) ? sanitize_text_field($_POST['acquisition_date']) : '';
        if (empty($date)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=No date provided'));
            exit;
        }

        $time = strtotime($date);
        if ($time === false) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=Invalid date format'));
            exit;
        }
        $acquisition_date = date('Y-m-d', $time);

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_token_ksp';

        $deleted = $wpdb->delete(
            $table_name,
            array('acquisition_date' => $acquisition_date),
            array('%s')
        );

        wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=deleted&count=' . intval($deleted)));
        exit;
    }

    public function process_token_ksp_delete_by_season()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_token_ksp_delete_by_season_nonce', 'kmnft_nonce');

        $season = isset($_POST['delete_season']) ? sanitize_text_field($_POST['delete_season']) : '';
        if (empty($season)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=No year provided'));
            exit;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_token_ksp';

        $deleted = $wpdb->delete(
            $table_name,
            array('season' => $season),
            array('%s')
        );

        wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=deleted&count=' . intval($deleted)));
        exit;
    }

    public function process_token_ksp_aggregation()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_token_ksp_aggregate_nonce', 'kmnft_nonce');

        $season = isset($_POST['season']) ? sanitize_text_field($_POST['season']) : '';
        if (empty($season)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp&status=error&msg=Season is required'));
            exit;
        }

        global $wpdb;
        $table_token_ksp = $wpdb->prefix . 'kmnft_token_ksp';
        $table_holdings = $wpdb->prefix . 'kmnft_holdings';
        $table_token_summary = $wpdb->prefix . 'kmnft_ksp_token_summary';
        $table_user_summary = $wpdb->prefix . 'kmnft_ksp_user_summary';

        // 1. Transaction Start (Optional/Simulated by order)

        // Check if data exists
        $debug_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_token_ksp} WHERE season = %s", $season));
        if ($debug_count == 0) {
            // Optional: You might want to allow clearing out a season even if no new data exists
            // But for safety, we'll just return with a notice instead of dying.
            $msg = urlencode("No KSP point records found for season " . $season . ". Aggregation skipped.");
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp-aggregation&status=success&msg=' . $msg));
            exit;
        }


        // Wash and Replace: Token Summary
        $wpdb->query($wpdb->prepare("DELETE FROM {$table_token_summary} WHERE season = %s", $season));

        // Fetch Token Data for PHP-side processing
        $token_results = $wpdb->get_results($wpdb->prepare(
            "SELECT token_id, SUM(acquisition_point) as total_points 
             FROM {$table_token_ksp} 
             WHERE season = %s 
             GROUP BY token_id 
             ORDER BY total_points DESC",
            $season
        ));

        if ($token_results) {
            $values = [];
            $placeholders = [];
            $rank = 0;
            $row_num = 0;
            $prev_points = null;

            foreach ($token_results as $row) {
                $row_num++;
                if ($prev_points !== $row->total_points) {
                    $rank = $row_num;
                }
                $prev_points = $row->total_points;

                array_push($values, $row->token_id, $season, $row->total_points, $rank);
                $placeholders[] = "(%s, %s, %d, %d, NOW())";
            }

            // Bulk Insert Token Summary
            // Split into chunks if too large (e.g. 500 rows)
            $chunks = array_chunk($placeholders, 500);
            $value_chunks = array_chunk($values, 500 * 4); // 4 values per row

            foreach ($chunks as $i => $chunk) {
                $query = "INSERT INTO {$table_token_summary} (`token_id`, `season`, `total_points`, `rank`, `updated_at`) VALUES " . implode(', ', $chunk);
                $result = $wpdb->query($wpdb->prepare($query, $value_chunks[$i]));
                if ($result === false) {
                    wp_die("DB INSERT ERROR (Tokens): " . $wpdb->last_error);
                }
            }
        }

        // Wash and Replace: User Summary (based on current holdings)
        $wpdb->query($wpdb->prepare("DELETE FROM {$table_user_summary} WHERE season = %s", $season));

        // Fetch User Data for PHP-side processing
        $user_results = $wpdb->get_results($wpdb->prepare(
            "SELECT h.user_id, SUM(tk.acquisition_point) as total_points
             FROM {$table_holdings} h
             JOIN {$table_token_ksp} tk ON h.token_id = tk.token_id
             WHERE tk.season = %s
             GROUP BY h.user_id
             ORDER BY total_points DESC",
            $season
        ));

        if ($user_results) {
            $values = [];
            $placeholders = [];
            $rank = 0;
            $row_num = 0;
            $prev_points = null;

            foreach ($user_results as $row) {
                $row_num++;
                if ($prev_points !== $row->total_points) {
                    $rank = $row_num;
                }
                $prev_points = $row->total_points;

                array_push($values, $row->user_id, $season, $row->total_points, $rank);
                $placeholders[] = "(%d, %s, %d, %d, NOW())";
            }

            // Bulk Insert User Summary
            $chunks = array_chunk($placeholders, 500);
            $value_chunks = array_chunk($values, 500 * 4); // 4 values per row

            foreach ($chunks as $i => $chunk) {
                $query = "INSERT INTO {$table_user_summary} (`user_id`, `season`, `total_points`, `rank`, `updated_at`) VALUES " . implode(', ', $chunk);
                $result = $wpdb->query($wpdb->prepare($query, $value_chunks[$i]));
                if ($result === false) {
                    wp_die("DB INSERT ERROR (Users): " . $wpdb->last_error);
                }
            }
        }

        // Calculate counts for messaging
        $token_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_token_summary WHERE season = %s", $season));
        $user_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_user_summary WHERE season = %s", $season));

        $msg = sprintf(
            'Aggregation for Season %s completed. (Tokens: %d, Users: %d)',
            esc_html($season),
            intval($token_count),
            intval($user_count)
        );

        $redirect_to = isset($_POST['redirect_to']) ? sanitize_text_field($_POST['redirect_to']) : 'kmnft-token-ksp';

        wp_redirect(admin_url('admin.php?page=' . $redirect_to . '&status=success&msg=' . urlencode($msg)));
        exit;
    }

    public function process_token_summary_export()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_export_token_summary_nonce', 'kmnft_nonce');

        $season = isset($_POST['season']) ? sanitize_text_field($_POST['season']) : '';
        if (empty($season)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp-aggregation&status=error&msg=Season is required'));
            exit;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_ksp_token_summary';

        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE season = %s ORDER BY total_points DESC", $season), ARRAY_A);

        if (empty($results)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp-aggregation&status=error&msg=No data found for season ' . esc_attr($season)));
            exit;
        }

        $filename = 'kmnft_token_summary_' . $season . '_' . date('Ymd') . '.csv';

        // Clear all output buffers to prevent header corruption
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('rank', 'token_id', 'season', 'total_points', 'updated_at'));

        foreach ($results as $row) {
            fputcsv($fp, array(
                $row['rank'],
                $row['token_id'],
                $row['season'],
                $row['total_points'],
                $row['updated_at']
            ));
        }
        fclose($fp);
        exit;
    }

    public function process_user_summary_export()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_export_user_summary_nonce', 'kmnft_nonce');

        $season = isset($_POST['season']) ? sanitize_text_field($_POST['season']) : '';
        if (empty($season)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp-aggregation&status=error&msg=Season is required'));
            exit;
        }

        global $wpdb;
        $summary_table = $wpdb->prefix . 'kmnft_ksp_user_summary';
        $users_table = $wpdb->users;

        // Join with users table to get login name
        $sql = $wpdb->prepare("
            SELECT s.*, u.user_login, u.display_name 
            FROM $summary_table s
            LEFT JOIN $users_table u ON s.user_id = u.ID
            WHERE s.season = %s
            ORDER BY s.total_points DESC
        ", $season);

        $results = $wpdb->get_results($sql, ARRAY_A);

        if (empty($results)) {
            wp_redirect(admin_url('admin.php?page=kmnft-token-ksp-aggregation&status=error&msg=No data found for season ' . esc_attr($season)));
            exit;
        }

        $filename = 'kmnft_user_summary_' . $season . '_' . date('Ymd') . '.csv';

        // Clear all output buffers to prevent header corruption
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('rank', 'user_id', 'user_login', 'display_name', 'season', 'total_points', 'updated_at'));

        foreach ($results as $row) {
            fputcsv($fp, array(
                $row['rank'],
                $row['user_id'],
                $row['user_login'],
                $row['display_name'],
                $row['season'],
                $row['total_points'],
                $row['updated_at']
            ));
        }
        fclose($fp);
        exit;
    }

    private function ensure_ksp_summary_tables()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Token Summary
        $table_ksp_token_summary = $wpdb->prefix . 'kmnft_ksp_token_summary';
        $sql_ksp_token_summary = "CREATE TABLE $table_ksp_token_summary (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            token_id varchar(100) NOT NULL,
            season varchar(20) NOT NULL,
            total_points int(11) NOT NULL DEFAULT 0,
            `rank` int(11) DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY token_season (token_id, season)
        ) $charset_collate;";
        dbDelta($sql_ksp_token_summary);

        $row_rank_token = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table_ksp_token_summary' AND COLUMN_NAME = 'rank'");
        if (empty($row_rank_token)) {
            $wpdb->query("ALTER TABLE $table_ksp_token_summary ADD `rank` int(11) DEFAULT 0");
        }

        // User Summary
        $table_ksp_user_summary = $wpdb->prefix . 'kmnft_ksp_user_summary';
        $sql_ksp_user_summary = "CREATE TABLE $table_ksp_user_summary (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            season varchar(20) NOT NULL,
            total_points int(11) NOT NULL DEFAULT 0,
            `rank` int(11) DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_season (user_id, season)
        ) $charset_collate;";
        dbDelta($sql_ksp_user_summary);

        $row_rank_user = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table_ksp_user_summary' AND COLUMN_NAME = 'rank'");
        if (empty($row_rank_user)) {
            $wpdb->query("ALTER TABLE $table_ksp_user_summary ADD `rank` int(11) DEFAULT 0");
        }
    }

    private function ensure_token_ksp_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_token_ksp';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            token_id varchar(50) NOT NULL,
            acquisition_date date NOT NULL,
            acquisition_point int(11) NOT NULL DEFAULT 0,
            season varchar(20) DEFAULT '' NOT NULL,
            reason_1 text DEFAULT '' NOT NULL,
            reason_2 text DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // --- MATCH RESULTS FUNCTIONS ---

    private function ensure_match_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_match_results';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            section_label varchar(50) DEFAULT '' NOT NULL,
            match_date date NOT NULL,
            opponent varchar(100) NOT NULL,
            result_score varchar(20) NOT NULL,
            is_win tinyint(1) NOT NULL DEFAULT 0,
            goal_token_ids text NOT NULL,
            goal_images text NOT NULL,
            shoot_prize_memo text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $row_videos = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table_name' AND COLUMN_NAME = 'goal_videos'");
        if (empty($row_videos)) {
            $wpdb->query("ALTER TABLE $table_name ADD goal_videos text NOT NULL AFTER goal_images");
        }
    }

    public function render_match_results_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_match_results';

        // Handle Edit Mode
        $edit_match = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $edit_match = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        }

        $matches = $wpdb->get_results("SELECT * FROM $table_name ORDER BY match_date DESC");
        ?>
        <div class="wrap">
            <h1>Match Results Manager</h1>
            <p>Register recent match results to display on the User Dashboard.</p>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> Match data saved.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'updated'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> Match data updated.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'deleted'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> Match deleted.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error:</strong> Failed to save data.
                            <?php echo isset($_GET['msg']) ? esc_html(urldecode($_GET['msg'])) : ''; ?>
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2><?php echo $edit_match ? 'Edit Match' : 'Add New Match'; ?></h2>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                    <input type="hidden" name="action" value="kmnft_save_match">
                    <?php if ($edit_match): ?>
                        <input type="hidden" name="match_id" value="<?php echo esc_attr($edit_match->id); ?>">
                    <?php endif; ?>
                    <?php wp_nonce_field('kmnft_match_nonce', 'kmnft_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th><label for="section_label">Section (節)</label></th>
                            <td><input type="text" name="section_label" id="section_label" class="regular-text"
                                    placeholder="e.g. 第1節"
                                    value="<?php echo $edit_match ? esc_attr($edit_match->section_label) : ''; ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="match_date">Match Date</label></th>
                            <td><input type="date" name="match_date" id="match_date" required
                                    value="<?php echo $edit_match ? esc_attr($edit_match->match_date) : ''; ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="opponent">Opponent</label></th>
                            <td><input type="text" name="opponent" id="opponent" class="regular-text"
                                    placeholder="e.g. Shonan Bellmare" required
                                    value="<?php echo $edit_match ? esc_attr($edit_match->opponent) : ''; ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="result_score">Score</label></th>
                            <td><input type="text" name="result_score" id="result_score" placeholder="e.g. 2-1" required
                                    value="<?php echo $edit_match ? esc_attr($edit_match->result_score) : ''; ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="is_win">Result</label></th>
                            <td>
                                <select name="is_win" id="is_win">
                                    <option value="1" <?php echo ($edit_match && $edit_match->is_win == 1) ? 'selected' : ''; ?>>
                                        Win</option>
                                    <option value="0" <?php echo ($edit_match && $edit_match->is_win == 0) ? 'selected' : ''; ?>>
                                        Lose</option>
                                    <option value="2" <?php echo ($edit_match && $edit_match->is_win == 2) ? 'selected' : ''; ?>>
                                        Draw</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="goal_token_ids">Goal Token IDs（11桁）</label></th>
                            <td>
                                <textarea name="goal_token_ids" id="goal_token_ids" rows="5" class="large-text"
                                    placeholder="10089172280（1行 = 1ゴール目）&#10;10091172239, 10091172240（2行 = 2ゴール目、複数いる場合はカンマ区切り）"><?php echo "\n" . ($edit_match ? esc_textarea($edit_match->goal_token_ids) : ''); ?></textarea>
                                <p class="description">
                                    1行につき1ゴールとして入力してください。1つのゴールに複数のアセットを紐付ける場合は、同じ行内でカンマ区切りで入力してください（「1点目」などの文字は不要です）。</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="goal_images">Goal Images</label></th>
                            <td>
                                <div id="goal-images-container"
                                    style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                                    <?php
                                    if ($edit_match && !empty($edit_match->goal_images)) {
                                        // Handle both multiline and comma separated for preview
                                        $all_images = preg_split('/[\n,]+/', $edit_match->goal_images);
                                        foreach ($all_images as $img_url) {
                                            $img_url = trim($img_url);
                                            if (empty($img_url))
                                                continue;
                                            echo '<div style="position:relative; width:80px; height:80px;">';
                                            echo '<img src="' . esc_url($img_url) . '" style="width:100%; height:100%; object-fit:cover; border:1px solid #ccc;">';
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <textarea name="goal_images" id="goal_images_textarea" rows="5" class="large-text"
                                    placeholder="url1（1行 = 1ゴール目）&#10;url2, url3（2行 = 2ゴール目、複数ある場合はカンマ区切り）"><?php echo "\n" . ($edit_match ? esc_textarea($edit_match->goal_images) : ''); ?></textarea>

                                <div style="margin-top:10px;">
                                    <button type="button" class="button"
                                        id="upload_goal_image_btn"><?php echo $edit_match ? '画像を追加' : '画像を追加'; ?></button>
                                    <button type="button" class="button" id="clear_goal_images_btn">入力をクリア</button>
                                </div>
                                <p class="description">
                                    1行につき1ゴールの画像を登録してください。1つのゴールに複数画像がある場合は、同じ行内でカンマ区切りで入力してください（「1点目」などの文字は不要です）。<br>
                                    複数ゴールある時は、行ごとに複数回画像追加ボタンを押して、選択してください。<br>
                                    1行目＝1点目（ボタン押して追加、そして改行）<br>
                                    2行目＝2点目（ボタン押して追加、そして改行）<br>
                                    行の順序は Goal Token IDs の指定順と一致させる必要があります。
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="goal_videos">Goal Videos URL</label></th>
                            <td>
                                <textarea name="goal_videos" id="goal_videos" rows="5" class="large-text"
                                    placeholder="https://youtube.com/watch?v=video1（1行 = 1ゴール目）&#10;https://youtube.com/watch?v=video2（2行 = 2ゴール目）"><?php echo "\n" . ($edit_match ? esc_textarea($edit_match->goal_videos) : ''); ?></textarea>
                                <p class="description">1行につき1ゴールの動画URL（YouTube等）を登録してください。1つのゴールに対して複数の動画は登録できません。<br>
                                    行の順序は Goal Token IDs の指定順と一致させる必要があります。</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="shoot_prize_memo">SHOOT ZONE Prize<br>(Text Memo)</label></th>
                            <td>
                                <textarea name="shoot_prize_memo" id="shoot_prize_memo" rows="6" class="large-text"
                                    placeholder="e.g. &#10;ピンポイント賞【X098、Y37】&#10;ニアピン賞【X097、Y36】..."><?php echo $edit_match ? esc_textarea($edit_match->shoot_prize_memo) : ''; ?></textarea>
                                <p class="description">Enter prize details here. Line breaks are preserved.</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button($edit_match ? 'Update Match Result' : 'Save Match Result'); ?>
                    <?php if ($edit_match): ?>
                        <a href="<?php echo admin_url('admin.php?page=kmnft-match-results'); ?>"
                            class="button button-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="margin-top: 30px;">
                <h2>Existing Matches</h2>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Date</th>
                            <th>Opponent</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Goal Tokens</th>
                            <th>Goal Images</th>
                            <th>Videos</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($matches): ?>
                            <?php foreach ($matches as $match): ?>
                                <tr>
                                    <td><?php echo esc_html($match->section_label); ?></td>
                                    <td><?php echo esc_html($match->match_date); ?></td>
                                    <td><?php echo esc_html($match->opponent); ?></td>
                                    <td><?php echo esc_html($match->result_score); ?></td>
                                    <td>
                                        <?php if ($match->is_win == 1): ?>
                                            <span style="color: green; font-weight: bold;">WIN</span>
                                        <?php elseif ($match->is_win == 2): ?>
                                            <span style="color: gray; font-weight: bold;">DRAW</span>
                                        <?php else: ?>
                                            <span style="color: red;">LOSE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($match->goal_token_ids); ?></td>
                                    <td>
                                        <?php if (!empty($match->goal_images)): ?>
                                            <?php
                                            // Split by both newline and comma for the preview icons
                                            $imgs = preg_split('/[\n,]+/', $match->goal_images);
                                            foreach ($imgs as $img):
                                                $img = trim($img);
                                                if (empty($img))
                                                    continue;
                                                ?>
                                                <img src="<?php echo esc_url($img); ?>" style="max-width:30px; height:auto;">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo nl2br(esc_html($match->goal_videos)); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=kmnft-match-results&action=edit&id=' . $match->id); ?>"
                                            class="button button-small">Edit</a>
                                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                                            onsubmit="return confirm('Delete this match?');" style="display:inline;">
                                            <input type="hidden" name="action" value="kmnft_delete_match">
                                            <input type="hidden" name="match_id" value="<?php echo $match->id; ?>">
                                            <?php wp_nonce_field('kmnft_match_delete_nonce', 'kmnft_nonce'); ?>
                                            <button type="submit" class="button button-small button-link-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">No matches recorded.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                var mediaUploader;

                function updatePreview() {
                    var content = $('#goal_images_textarea').val();
                    var $container = $('#goal-images-container');
                    $container.empty();

                    if (!content) return;

                    var urls = content.split(/[\n,]+/);
                    $.each(urls, function (i, url) {
                        url = url.trim();
                        if (url) {
                            $container.append('<div style="position:relative; width:80px; height:80px;"><img src="' + url + '" style="width:100%; height:100%; object-fit:cover; border:1px solid #ccc;"></div>');
                        }
                    });
                }

                // Initial preview update
                updatePreview();

                // Update preview on manual textarea change
                $('#goal_images_textarea').on('input propertychange', function () {
                    updatePreview();
                });

                $('#upload_goal_image_btn').click(function (e) {
                    e.preventDefault();
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }
                    mediaUploader = wp.media.frames.file_frame = wp.media({
                        title: 'Choose Goal Image',
                        button: {
                            text: 'Add to List'
                        },
                        multiple: true
                    });

                    mediaUploader.on('select', function () {
                        var selection = mediaUploader.state().get('selection');
                        var currentVal = $('#goal_images_textarea').val();
                        var newUrls = [];

                        selection.each(function (attachment) {
                            attachment = attachment.toJSON();
                            newUrls.push(attachment.url);
                        });

                        if (newUrls.length > 0) {
                            // If there's existing text and it doesn't end with a newline, add one
                            if (currentVal.length > 0 && !currentVal.match(/\n$/)) {
                                currentVal += "\n";
                            }
                            $('#goal_images_textarea').val(currentVal + newUrls.join(', '));
                            updatePreview();
                        }
                    });
                    mediaUploader.open();
                });

                $('#clear_goal_images_btn').click(function () {
                    if (confirm('入力内容をクリアしますか？')) {
                        $('#goal_images_textarea').val('');
                        updatePreview();
                    }
                });
            });
        </script>
        <?php
    }

    public function process_match_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_match_nonce', 'kmnft_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_match_results';

        $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
        $section_label = sanitize_text_field($_POST['section_label']);
        $match_date = sanitize_text_field($_POST['match_date']);
        $opponent = sanitize_text_field($_POST['opponent']);
        $result_score = sanitize_text_field($_POST['result_score']);
        $is_win = intval($_POST['is_win']);
        // goal_token_ids: Preserve empty lines
        $raw_token_ids = isset($_POST['goal_token_ids']) ? $_POST['goal_token_ids'] : '';
        $shoot_prize_memo = sanitize_textarea_field($_POST['shoot_prize_memo']);
        $id_lines = preg_split('/\r\n|\r|\n/', $raw_token_ids);
        $normalized_id_lines = array();
        foreach ($id_lines as $line) {
            $ids = explode(',', $line);
            $ids = array_map('trim', $ids);
            $ids = array_map('sanitize_text_field', $ids);
            $ids = array_filter($ids);
            $normalized_id_lines[] = implode(',', $ids);
        }
        $clean_token_ids = implode("\n", $normalized_id_lines);
        // goal_images: Preserve empty lines
        $raw_goal_images = isset($_POST['goal_images']) ? $_POST['goal_images'] : '';
        // Using regex split to catch different newline types but we want to process line by line
        $img_lines = preg_split('/\r\n|\r|\n/', $raw_goal_images);
        $normalized_img_lines = array();

        // If the last line is empty and created by a trailing newline of the previous content, 
        // split might create an empty element. However, we want to PRESERVE user intent.
        // If user entered "A\n\nB", split gives ["A", "", "B"]. We keep "" to handle 2nd goal empty.

        foreach ($img_lines as $line) {
            $imgs = explode(',', $line);
            $imgs = array_map('trim', $imgs);
            $imgs = array_filter($imgs); // Remove empty strings from comma separated values

            // Re-join strictly with commas. If empty, it becomes an empty string.
            $normalized_img_lines[] = implode(',', $imgs);
        }
        // Trim trailing empty lines only if desired, but for now let's just join back.
        // Actually, preventing infinite trailing newlines is good, but inner newlines must be kept.
        // Let's trim the array from the end if they are empty, BUT we must be careful not to remove "middle" empty lines.
        // Implementation: Just join.
        $clean_goal_images = implode("\n", $normalized_img_lines);

        // goal_videos: Preserve empty lines
        $raw_goal_videos = isset($_POST['goal_videos']) ? $_POST['goal_videos'] : '';
        $video_lines = preg_split('/\r\n|\r|\n/', $raw_goal_videos);
        $normalized_video_lines = array();

        foreach ($video_lines as $line) {
            $line = sanitize_text_field($line); // Sanitize each line individually
            $normalized_video_lines[] = $line;
        }
        $goal_videos = implode("\n", $normalized_video_lines);

        $data = array(
            'section_label' => $section_label,
            'match_date' => $match_date,
            'opponent' => $opponent,
            'result_score' => $result_score,
            'is_win' => $is_win,
            'goal_token_ids' => $clean_token_ids,
            'goal_images' => $clean_goal_images,
            'goal_videos' => $goal_videos,
            'shoot_prize_memo' => $shoot_prize_memo
        );
        $format = array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s');

        if ($match_id > 0) {
            // Update
            $result = $wpdb->update($table_name, $data, array('id' => $match_id), $format, array('%d'));
            $status = 'updated';
        } else {
            // Insert
            $result = $wpdb->insert($table_name, $data, $format);
            $status = 'success';
        }

        if ($result === false) {
            $error_msg = urlencode($wpdb->last_error);
            wp_redirect(admin_url('admin.php?page=kmnft-match-results&status=error&msg=' . $error_msg));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=kmnft-match-results&status=' . $status));
        exit;
    }

    public function process_match_delete()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_match_delete_nonce', 'kmnft_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_match_results';
        $match_id = intval($_POST['match_id']);

        $wpdb->delete($table_name, array('id' => $match_id));

        wp_redirect(admin_url('admin.php?page=kmnft-match-results&status=deleted'));
        exit;
    }

    // --- STANDINGS FUNCTIONS ---

    public function ensure_standings_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_standings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            announcement_date date NOT NULL,
            display_title varchar(255) DEFAULT '' NOT NULL,
            data longtext NOT NULL,
            image_url varchar(255) DEFAULT '' NOT NULL,
            memo text DEFAULT '' NOT NULL,
            our_rank int(11) DEFAULT 0 NOT NULL,
            our_points int(11) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function ensure_league_schedule_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_league_schedule';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            season_year varchar(10) NOT NULL,
            data longtext NOT NULL,
            summary_stats text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function render_standings_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_standings';

        // Handle Edit
        $edit_item = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $edit_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        }

        $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY announcement_date DESC");
        ?>
        <div class="wrap">
            <h1>League Standings Manager</h1>
            <p>Upload the latest league standings CSV.</p>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> Saved.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'deleted'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> Deleted.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error:</strong> <?php echo esc_html(urldecode($_GET['msg'])); ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2
                    style="<?php echo $edit_item ? 'background: #f0f6fb; border-left: 4px solid #2271b1; padding: 10px; margin-left: -20px; margin-right: -20px; margin-top: -20px; margin-bottom: 20px;' : ''; ?>">
                    <?php echo $edit_item ? 'Update Standings' : 'Add New Standings'; ?>
                </h2>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="kmnft_save_standings">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="item_id" value="<?php echo esc_attr($edit_item->id); ?>">
                    <?php endif; ?>
                    <?php wp_nonce_field('kmnft_standings_nonce', 'kmnft_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th><label for="announcement_date">Announcement Date</label></th>
                            <td><input type="date" name="announcement_date" id="announcement_date" required
                                    value="<?php echo $edit_item ? esc_attr($edit_item->announcement_date) : date('Y-m-d'); ?>"
                                    <?php echo $edit_item ? 'readonly style="background-color: #f0f0f1; cursor: not-allowed;"' : ''; ?>>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="display_title">Display Title</label></th>
                            <td><input type="text" name="display_title" id="display_title" class="regular-text"
                                    value="<?php echo $edit_item ? esc_attr($edit_item->display_title) : ''; ?>"
                                    placeholder="e.g. 第3節終了時点">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="csv_file">CSV File</label></th>
                            <td>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" <?php echo $edit_item ? '' : 'required'; ?>>
                                <p class="description">
                                    Columns: <code>rank</code>, <code>clubname</code>, <code>PL</code>, <code>W</code>,
                                    <code>D</code>, <code>L</code>, <code>GD</code>, <code>PT</code><br>
                                    Auto-detects "Kamakura" or "鎌倉" to set Our Rank/Points.<br>
                                    <?php if ($edit_item): ?>
                                        <strong>Note:</strong> Uploading a new CSV will replace the existing data. Leave empty to
                                        keep current data.
                                    <?php endif; ?>
                                </p>
                                <p>
                                    <a href="<?php echo admin_url('admin-post.php?action=kmnft_download_sample_standings_csv'); ?>"
                                        class="button button-secondary">Download Sample CSV</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="memo">Memo</label></th>
                            <td><textarea name="memo" id="memo" rows="3"
                                    class="large-text"><?php echo $edit_item ? esc_textarea($edit_item->memo) : ''; ?></textarea>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button($edit_item ? 'Update Standings' : 'Save Standings'); ?>
                    <?php if ($edit_item): ?>
                        <a href="<?php echo admin_url('admin.php?page=kmnft-standings'); ?>"
                            class="button button-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="margin-top: 30px;">
                <h2>History</h2>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Display Title</th>
                            <th>Teams</th>
                            <th>Our Rank</th>
                            <th>Our Points</th>
                            <th>Memo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items): ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $data = json_decode($item->data, true);
                                $count = is_array($data) ? count($data) : 0;
                                ?>
                                <tr>
                                    <td><?php echo esc_html($item->announcement_date); ?></td>
                                    <td><?php echo esc_html($item->display_title); ?></td>
                                    <td><?php echo $count; ?> Teams</td>
                                    <td><?php echo esc_html($item->our_rank); ?></td>
                                    <td><?php echo esc_html($item->our_points); ?></td>
                                    <td><?php echo esc_html($item->memo); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=kmnft-standings&action=edit&id=' . $item->id); ?>"
                                            class="button button-small">Edit</a>
                                        <a href="<?php echo admin_url('admin-post.php?action=kmnft_download_standings&id=' . $item->id); ?>"
                                            class="button button-small">Download</a>
                                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                                            onsubmit="return confirm('Delete?');" style="display:inline;">
                                            <input type="hidden" name="action" value="kmnft_delete_standings">
                                            <input type="hidden" name="item_id" value="<?php echo $item->id; ?>">
                                            <?php wp_nonce_field('kmnft_standings_delete_nonce', 'kmnft_nonce'); ?>
                                            <button type="submit" class="button button-small button-link-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public function process_standings_save()
    {
        if (!current_user_can('manage_options'))
            wp_die('Unauthorized');
        check_admin_referer('kmnft_standings_nonce', 'kmnft_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_standings';

        $id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
        $date = sanitize_text_field($_POST['announcement_date']);
        $display_title = sanitize_text_field($_POST['display_title']);
        $memo = sanitize_textarea_field($_POST['memo']);

        $data_json = '';
        $our_rank = 0;
        $our_points = 0;

        // Handle File Upload
        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if ($handle !== FALSE) {
                // Header: rank, clubname, PL ,W,D,L,GD,PT
                // We assume 1st row is header, or check if numeric
                // Let's just read all.
                $csv_data = array();
                $row_idx = 0;
                while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Shift-JIS対策
                    mb_convert_variables('UTF-8', 'SJIS-win, UTF-8, auto', $row);

                    $row_idx++;
                    // Basic heuristic to skip header line if it contains "rank" or "clubname"
                    if ($row_idx === 1 && (stripos($row[0], 'rank') !== false || stripos($row[1], 'club') !== false)) {
                        continue;
                    }
                    // Map to keys
                    if (count($row) < 8)
                        continue; // Ensure enough cols

                    $entry = array(
                        'rank' => intval($row[0]),
                        'clubname' => sanitize_text_field($row[1]),
                        'pl' => intval($row[2]),
                        'w' => intval($row[3]),
                        'd' => intval($row[4]),
                        'l' => intval($row[5]),
                        'gd' => intval($row[6]),
                        'pt' => intval($row[7]),
                    );
                    $csv_data[] = $entry;

                    // Kamakura Check
                    if (strpos($entry['clubname'], '鎌倉') !== false || stripos($entry['clubname'], 'Kamakura') !== false) {
                        $our_rank = $entry['rank'];
                        $our_points = $entry['pt'];
                    }
                }
                fclose($handle);
                $data_json = json_encode($csv_data, JSON_UNESCAPED_UNICODE);
            }
        } elseif ($id > 0) {
            // Keep existing data if updating and no file uploaded
            $existing = $wpdb->get_row($wpdb->prepare("SELECT data, our_rank, our_points FROM $table_name WHERE id = %d", $id));
            $data_json = $existing->data;
            // Retain old values if valid, mostly just ensures we don't zero them out accidentally if not recalculating
            $our_rank = $existing->our_rank;
            $our_points = $existing->our_points;
        }

        if (empty($data_json) && $id == 0) {
            wp_redirect(admin_url('admin.php?page=kmnft-standings&status=error&msg=' . urlencode('CSV File Required')));
            exit;
        }

        $data_to_save = array(
            'announcement_date' => $date,
            'display_title' => $display_title,
            'data' => $data_json,
            'our_rank' => $our_rank,
            'our_points' => $our_points,
            'memo' => $memo
        );
        $format = array('%s', '%s', '%s', '%d', '%d', '%s');

        if ($id > 0) {
            $wpdb->update($table_name, $data_to_save, array('id' => $id), $format, array('%d'));
        } else {
            $wpdb->insert($table_name, $data_to_save, $format);
        }

        wp_redirect(admin_url('admin.php?page=kmnft-standings&status=success'));
        exit;
    }

    public function process_standings_delete()
    {
        if (!current_user_can('manage_options'))
            wp_die('Unauthorized');
        check_admin_referer('kmnft_standings_delete_nonce', 'kmnft_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_standings';
        $id = intval($_POST['item_id']);

        $wpdb->delete($table_name, array('id' => $id));

        wp_redirect(admin_url('admin.php?page=kmnft-standings&status=deleted'));
        exit;
    }

    public function process_standings_download()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        if (!isset($_GET['id'])) {
            wp_die('ID missing');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_standings';
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));

        if (!$item) {
            wp_die('Record not found');
        }

        $data = json_decode($item->data, true);
        if (!is_array($data)) {
            wp_die('Data error');
        }

        $filename = 'standings_' . $item->announcement_date . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

        // Header
        fputcsv($output, array('rank', 'clubname', 'PL', 'W', 'D', 'L', 'GD', 'PT'));

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, array(
                $row['rank'],
                $row['clubname'],
                $row['pl'],
                $row['w'],
                $row['d'],
                $row['l'],
                $row['gd'],
                $row['pt']
            ));
        }

        fclose($output);
        exit;
    }

    public function render_league_schedule_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_league_schedule';

        // Handle Edit
        $edit_item = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $edit_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        }

        $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY season_year DESC");
        ?>
        <div class="wrap">
            <h1>League Schedule Manager</h1>
            <p>Upload the league schedule/results CSV for a season.</p>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] === 'success'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> Saved.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'deleted'): ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success!</strong> Deleted.</p>
                    </div>
                <?php elseif ($_GET['status'] === 'error'): ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error:</strong> <?php echo esc_html(urldecode($_GET['msg'])); ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin-top: 20px;">
                <h2><?php echo $edit_item ? 'Update Schedule' : 'Add New Schedule'; ?></h2>
                <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="kmnft_save_league_schedule">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="item_id" value="<?php echo esc_attr($edit_item->id); ?>">
                    <?php endif; ?>
                    <?php wp_nonce_field('kmnft_league_schedule_nonce', 'kmnft_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th><label for="season_year">Season Year</label></th>
                            <td><input type="text" name="season_year" id="season_year" required
                                    value="<?php echo $edit_item ? esc_attr($edit_item->season_year) : date('Y'); ?>">
                                <p class="description">e.g. 2025</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="csv_file">CSV File</label></th>
                            <td>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" <?php echo $edit_item ? '' : 'required'; ?>>
                                <p class="description">
                                    Format: <code>Section</code>, <code>Date(YYYY/MM/DD)</code>, <code>Time</code>,
                                    <code>Score(H - A)</code>, <code>Opponent</code>, <code>Location</code><br>
                                    Example: <code>1, 2025/04/06, 13:00, 3 - 1, イトゥアーノFC横浜, 鎌倉スタジアム</code><br>
                                    <?php if ($edit_item): ?>
                                        <br><strong
                                            style="color: #dc3232;">注意：新しいCSVファイルをアップロードすると、このシーズンの既存データはすべて上書きされます。</strong>
                                    <?php endif; ?>
                                </p>
                                <p>
                                    <a href="<?php echo admin_url('admin-post.php?action=kmnft_download_sample_league_schedule_csv'); ?>"
                                        class="button button-secondary">Download Sample CSV</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button($edit_item ? 'Update Schedule' : 'Save Schedule'); ?>
                    <?php if ($edit_item): ?>
                        <a href="<?php echo admin_url('admin.php?page=kmnft-league-schedule'); ?>"
                            class="button button-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="margin-top: 30px;">
                <h2>History</h2>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Season</th>
                            <th>Matches</th>
                            <th>Summary (W-L-D)</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items): ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $data = json_decode($item->data, true);
                                $stats = !empty($item->summary_stats) ? json_decode($item->summary_stats, true) : null;
                                $count = is_array($data) ? count($data) : 0;
                                $summary_text = ($stats && isset($stats['win'])) ? "{$stats['win']} - {$stats['lose']} - {$stats['draw']}" : '-';
                                ?>
                                <tr>
                                    <td><?php echo esc_html($item->season_year); ?></td>
                                    <td><?php echo $count; ?> Matches</td>
                                    <td><?php echo esc_html($summary_text); ?></td>
                                    <td><?php echo esc_html($item->created_at); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=kmnft-league-schedule&action=edit&id=' . $item->id); ?>"
                                            class="button button-small">Edit</a>
                                        <a href="<?php echo admin_url('admin-post.php?action=kmnft_download_league_schedule_csv&item_id=' . $item->id); ?>"
                                            class="button button-small button-secondary">Download</a>
                                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post"
                                            onsubmit="return confirm('Delete?');" style="display:inline;">
                                            <input type="hidden" name="action" value="kmnft_delete_league_schedule">
                                            <input type="hidden" name="item_id" value="<?php echo $item->id; ?>">
                                            <?php wp_nonce_field('kmnft_league_schedule_delete_nonce', 'kmnft_nonce'); ?>
                                            <button type="submit" class="button button-small button-link-delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public function process_league_schedule_save()
    {
        if (!current_user_can('manage_options'))
            wp_die('Unauthorized');
        check_admin_referer('kmnft_league_schedule_nonce', 'kmnft_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_league_schedule';

        $id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
        $season_year = sanitize_text_field($_POST['season_year']);

        $data_json = '';
        $summary_stats_json = '';

        // Handle File Upload
        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if ($handle !== FALSE) {
                // BOM check
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                $csv_data = array();
                $win = 0;
                $lose = 0;
                $draw = 0;
                $total = 0;

                $row_idx = 0;
                while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Shift-JIS対策
                    mb_convert_variables('UTF-8', 'SJIS-win, UTF-8, auto', $row);

                    $row_idx++;
                    // Skip header if it looks like one (contains "Section" or "Score")
                    if ($row_idx === 1 && (stripos($row[0], 'section') !== false || stripos($row[3], 'score') !== false)) {
                        continue;
                    }
                    if (count($row) < 5)
                        continue;

                    // Parse Score: "3 - 1" or "3-1"
                    $score_str = $row[3];
                    $parts = explode('-', str_replace(' ', '', $score_str));
                    $is_win = 0; // 0: lose, 1: win, 2: draw

                    $home_score = '';
                    $away_score = '';

                    if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                        $home = intval($parts[0]);
                        $away = intval($parts[1]);
                        if ($home > $away) {
                            $is_win = 1;
                            $win++;
                        } elseif ($home < $away) {
                            $is_win = 0;
                            $lose++;
                        } else {
                            $is_win = 2; // Draw
                            $draw++;
                        }
                    } else {
                        // Maybe empty or scheduled
                        $is_win = -1; // Unknown/Scheduled
                    }

                    if ($is_win !== -1) {
                        $total++;
                    }

                    $entry = array(
                        'section' => sanitize_text_field($row[0]),
                        'date' => sanitize_text_field($row[1]),
                        'time' => sanitize_text_field($row[2]),
                        'score' => sanitize_text_field($row[3]),
                        'opponent' => sanitize_text_field($row[4]),
                        'location' => isset($row[5]) ? sanitize_text_field($row[5]) : '',
                        'is_win' => $is_win
                    );
                    $csv_data[] = $entry;
                }
                fclose($handle);
                $data_json = json_encode($csv_data, JSON_UNESCAPED_UNICODE);
                $summary_stats_json = json_encode(array('total' => $total, 'win' => $win, 'lose' => $lose, 'draw' => $draw));
            }
        } elseif ($id > 0) {
            // Keep existing data
            $existing = $wpdb->get_row($wpdb->prepare("SELECT data, summary_stats FROM $table_name WHERE id = %d", $id));
            $data_json = $existing->data;
            $summary_stats_json = $existing->summary_stats;
        }

        if (empty($data_json) && $id == 0) {
            wp_redirect(admin_url('admin.php?page=kmnft-league-schedule&status=error&msg=' . urlencode('CSV File Required')));
            exit;
        }

        $data_to_save = array(
            'season_year' => $season_year,
            'data' => $data_json,
            'summary_stats' => $summary_stats_json
        );
        $format = array('%s', '%s', '%s');

        if ($id > 0) {
            $wpdb->update($table_name, $data_to_save, array('id' => $id), $format, array('%d'));
        } else {
            $wpdb->insert($table_name, $data_to_save, $format);
        }

        wp_redirect(admin_url('admin.php?page=kmnft-league-schedule&status=success'));
        exit;
    }

    public function process_league_schedule_delete()
    {
        if (!current_user_can('manage_options'))
            wp_die('Unauthorized');
        check_admin_referer('kmnft_league_schedule_delete_nonce', 'kmnft_nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_league_schedule';
        $id = intval($_POST['item_id']);

        $wpdb->delete($table_name, array('id' => $id));

        wp_redirect(admin_url('admin.php?page=kmnft-league-schedule&status=deleted'));
        exit;
    }

    public function process_download_sample_standings_csv()
    {
        if (!current_user_can('manage_options'))
            wp_die('Unauthorized');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=sample_standings.csv');

        $output = fopen('php://output', 'w');
        // Add BOM for Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($output, array('rank', 'clubname', 'PL', 'W', 'D', 'L', 'GD', 'PT'));
        // Sample Data
        fputcsv($output, array('1', 'Kamakura International FC', '18', '14', '2', '2', '25', '44'));
        fputcsv($output, array('2', 'Sample Team A', '18', '12', '3', '3', '15', '39'));
        fputcsv($output, array('3', 'Sample Team B', '18', '10', '4', '4', '8', '34'));
        fputcsv($output, array('4', 'Sample Team C', '18', '8', '5', '5', '2', '29'));

        fclose($output);
        exit;
    }

    public function process_download_sample_league_schedule_csv()
    {
        if (!current_user_can('manage_options'))
            wp_die('Unauthorized');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=sample_league_schedule.csv');

        $output = fopen('php://output', 'w');
        // Add BOM for Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($output, array('Section', 'Date(YYYY/MM/DD)', 'Time', 'Score(H - A)', 'Opponent', 'Location'));
        // Sample Data
        fputcsv($output, array('1', '2025/04/06', '13:00', '3 - 1', 'Sample FC', 'Kamakura Stadium'));
        fputcsv($output, array('2', '2025/04/13', '11:00', '1 - 2', 'Test City', 'Away Ground'));
        fputcsv($output, array('3', '2025/04/20', '14:00', '2 - 2', 'Demo United', 'Kamakura Stadium'));

        fclose($output);
        exit;
    }

    public function process_download_league_schedule_csv()
    {
        if (!current_user_can('manage_options'))
            wp_die('Unauthorized');

        $id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
        if (!$id) {
            wp_die('Invalid ID');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'kmnft_league_schedule';
        $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        if (!$item) {
            wp_die('Record not found');
        }

        $season = $item->season_year;
        $filename = "league_schedule_{$season}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // Add BOM for Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($output, array('Section', 'Date(YYYY/MM/DD)', 'Time', 'Score(H - A)', 'Opponent', 'Location'));

        $data = json_decode($item->data, true);
        if (is_array($data)) {
            foreach ($data as $row) {
                // Ensure correct field order
                $csv_row = array(
                    isset($row['section']) ? $row['section'] : '',
                    isset($row['date']) ? $row['date'] : '',
                    isset($row['time']) ? $row['time'] : '',
                    isset($row['score']) ? $row['score'] : '',
                    isset($row['opponent']) ? $row['opponent'] : '',
                    isset($row['location']) ? $row['location'] : ''
                );
                fputcsv($output, $csv_row);
            }
        }

        fclose($output);
        exit;
    }

    public function get_user_annual_ksp($user_id)
    {
        global $wpdb;
        $user_id = intval($user_id);

        // 1. Get User's Token IDs
        $holdings_table = $wpdb->prefix . 'kmnft_holdings';
        $token_ids = $wpdb->get_col($wpdb->prepare("SELECT token_id FROM $holdings_table WHERE user_id = %d", $user_id));

        if (empty($token_ids)) {
            return array();
        }

        // 2. Aggregate KSP by Season for these tokens
        $ksp_table = $wpdb->prefix . 'kmnft_token_ksp';
        $placeholders = implode(',', array_fill(0, count($token_ids), '%s'));

        $sql = "SELECT season, SUM(acquisition_point) as total_points 
                FROM $ksp_table 
                WHERE token_id IN ($placeholders) 
                GROUP BY season 
                ORDER BY season DESC";

        $results = $wpdb->get_results($wpdb->prepare($sql, $token_ids));

        return $results;
    }
    public function get_user_ksp_summary($user_id)
    {
        global $wpdb;
        $user_id = intval($user_id);
        $table_user_summary = $wpdb->prefix . 'kmnft_ksp_user_summary';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT season, total_points, `rank` 
             FROM $table_user_summary 
             WHERE user_id = %d 
             ORDER BY season DESC",
            $user_id
        ));
    }
    public function get_tokens_ksp_summary($token_ids, $season)
    {
        global $wpdb;
        if (empty($token_ids)) {
            return array();
        }

        $table_token_summary = $wpdb->prefix . 'kmnft_ksp_token_summary';
        $placeholders = implode(',', array_fill(0, count($token_ids), '%s'));

        $sql = "SELECT token_id, total_points, `rank` 
                FROM $table_token_summary 
                WHERE season = %s 
                AND token_id IN ($placeholders)";

        $query_params = array_merge(array($season), $token_ids);
        $results = $wpdb->get_results($wpdb->prepare($sql, $query_params));

        // Re-index by token_id for easier consumption
        $indexed_results = array();
        foreach ($results as $row) {
            $indexed_results[$row->token_id] = $row;
        }

        return $indexed_results;
    }

    /**
     * Fetch all season history for given tokens.
     */
    public function get_tokens_ksp_history($token_ids)
    {
        global $wpdb;
        if (empty($token_ids)) {
            return array();
        }

        $table_token_summary = $wpdb->prefix . 'kmnft_ksp_token_summary';
        $placeholders = implode(',', array_fill(0, count($token_ids), '%s'));

        $sql = "SELECT token_id, season, total_points, `rank` 
                FROM $table_token_summary 
                WHERE token_id IN ($placeholders)
                ORDER BY season DESC";

        $results = $wpdb->get_results($wpdb->prepare($sql, $token_ids));

        // Group by token_id
        $history = array();
        foreach ($results as $row) {
            if (!isset($history[$row->token_id])) {
                $history[$row->token_id] = array();
            }
            $history[$row->token_id][] = array(
                'season' => $row->season,
                'points' => number_format(intval($row->total_points)),
                'rank' => ($row->rank > 0) ? $row->rank : '-'
            );
        }

        return $history;
    }

    /**
     * Render Icon Settings Page
     */
    public function render_icon_settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html('Default Icon Management'); ?></h1>

            <!-- Current Icons -->
            <h2><?php echo esc_html('Current Icons'); ?></h2>
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 30px;">
                <?php
                $icons_dir = get_stylesheet_directory() . '/assets/images/default-icons/';
                $icons_url = get_stylesheet_directory_uri() . '/assets/images/default-icons/';

                if (is_dir($icons_dir)) {
                    $files = glob($icons_dir . 'icon-*.{png,jpg,jpeg}', GLOB_BRACE);
                    if (!empty($files)) {
                        foreach ($files as $file) {
                            $filename = basename($file);
                            $icon_url = $icons_url . $filename;
                            ?>
                            <div style="text-align: center; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                                <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($filename); ?>"
                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; margin-bottom: 10px;">
                                <div style="font-size: 12px; margin-bottom: 10px;"><?php echo esc_html($filename); ?></div>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this icon?');">
                                    <?php wp_nonce_field('kmnft_delete_default_icon'); ?>
                                    <input type="hidden" name="action" value="kmnft_delete_default_icon">
                                    <input type="hidden" name="filename" value="<?php echo esc_attr($filename); ?>">
                                    <button type="submit" class="button button-small button-link-delete"
                                        style="color: #b32d2e;">Delete</button>
                                </form>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p>No icons found.</p>';
                    }
                } else {
                    echo '<p>Icons directory does not exist.</p>';
                }
                ?>
            </div>

            <!-- Upload New Icon -->
            <h2><?php echo esc_html('Upload New Icon'); ?></h2>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field('kmnft_upload_default_icon'); ?>
                <input type="hidden" name="action" value="kmnft_upload_default_icon">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="icon_file">Icon File</label></th>
                        <td>
                            <input type="file" name="icon_file" id="icon_file" accept="image/png,image/jpeg,image/jpg" required>
                            <p class="description">Allowed formats: PNG, JPG, JPEG. Maximum size: 2MB. The file will be
                                automatically numbered (e.g., icon-11.png).</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Upload Icon'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Process Icon Upload
     */
    public function process_icon_upload()
    {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_upload_default_icon');

        // Check if file was uploaded
        if (!isset($_FILES['icon_file']) || $_FILES['icon_file']['error'] !== UPLOAD_ERR_OK) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=upload_failed'));
            exit;
        }

        $file = $_FILES['icon_file'];

        // Validate file type
        $allowed_types = array('image/png', 'image/jpeg', 'image/jpg');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=invalid_type'));
            exit;
        }

        // Validate file size (2MB max)
        if ($file['size'] > 2 * 1024 * 1024) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=file_too_large'));
            exit;
        }

        // Validate image
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=invalid_image'));
            exit;
        }

        // Determine next icon number
        $icons_dir = get_stylesheet_directory() . '/assets/images/default-icons/';
        if (!is_dir($icons_dir)) {
            mkdir($icons_dir, 0755, true);
        }

        $existing_files = glob($icons_dir . 'icon-*.{png,jpg,jpeg}', GLOB_BRACE);
        $max_number = 0;
        foreach ($existing_files as $existing_file) {
            if (preg_match('/icon-(\d+)\.(png|jpg|jpeg)$/i', basename($existing_file), $matches)) {
                $max_number = max($max_number, intval($matches[1]));
            }
        }
        $next_number = $max_number + 1;

        // Determine file extension
        $extension = 'png';
        if ($mime_type === 'image/jpeg' || $mime_type === 'image/jpg') {
            $extension = 'jpg';
        }

        // Move uploaded file
        $new_filename = 'icon-' . $next_number . '.' . $extension;
        $destination = $icons_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&success=uploaded'));
        } else {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=move_failed'));
        }
        exit;
    }

    /**
     * Process Icon Delete
     */
    public function process_icon_delete()
    {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('kmnft_delete_default_icon');

        // Validate filename
        $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : '';
        if (empty($filename) || !preg_match('/^icon-\d+\.(png|jpg|jpeg)$/i', $filename)) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=invalid_filename'));
            exit;
        }

        // Delete file
        $icons_dir = get_stylesheet_directory() . '/assets/images/default-icons/';
        $file_path = $icons_dir . $filename;

        // Prevent path traversal
        if (realpath($file_path) !== $file_path || dirname($file_path) !== realpath($icons_dir)) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=invalid_path'));
            exit;
        }

        if (file_exists($file_path) && unlink($file_path)) {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&success=deleted'));
        } else {
            wp_redirect(admin_url('admin.php?page=kmnft-icon-settings&error=delete_failed'));
        }
        exit;
    }
}
