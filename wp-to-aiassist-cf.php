<?php
/*
Plugin Name: WP to AIAssist via Cloudflare Worker
Description: Sends post data to a Cloudflare Worker when a post is published, where further processing such as AI translation, Telegram posting, or other automations can be performed.
Version: 1.0.0
Author: tarogo cloud
*/

if (!defined('ABSPATH')) {
    exit; // 防止被直接访问
}

// ==== 1. 常量定义 ====
if (!defined('WPAIASSIST_OPTION_ENDPOINT')) {
    define('WPAIASSIST_OPTION_ENDPOINT', 'wpaiassist_endpoint');
}
if (!defined('WPAIASSIST_OPTION_SECRET')) {
    define('WPAIASSIST_OPTION_SECRET', 'wpaiassist_secret');
}

// ==== 2. 激活钩子：初始化设置 / 可做兼容迁移 ====
register_activation_hook(__FILE__, 'wpaiassist_activate');
function wpaiassist_activate() {
    // 初始化新设置
    if (get_option(WPAIASSIST_OPTION_ENDPOINT) === false) {
        add_option(WPAIASSIST_OPTION_ENDPOINT, '');
    }
    if (get_option(WPAIASSIST_OPTION_SECRET) === false) {
        add_option(WPAIASSIST_OPTION_SECRET, wp_generate_password(16, false));
    }

    // （可选兼容）如果你之前用过旧插件，可以在这里做迁移：
    // 例如：
    // $old_endpoint = get_option('wptgcf_endpoint', '');
    // if (!empty($old_endpoint) && empty(get_option(WPAIASSIST_OPTION_ENDPOINT))) {
    //     update_option(WPAIASSIST_OPTION_ENDPOINT, $old_endpoint);
    // }
}

// ==== 3. 后台设置菜单 ====
add_action('admin_menu', 'wpaiassist_add_settings_page');
function wpaiassist_add_settings_page() {
    add_options_page(
        'WP → AIAssist (Cloudflare Worker)',
        'WP → AIAssist CF',
        'manage_options',
        'wpaiassist-settings',
        'wpaiassist_render_settings_page'
    );
}

// 设置页渲染
function wpaiassist_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // 保存表单
    if (isset($_POST['wpaiassist_save_settings'])) {
        check_admin_referer('wpaiassist_save_settings');

        $endpoint = isset($_POST['wpaiassist_endpoint']) ? esc_url_raw(trim($_POST['wpaiassist_endpoint'])) : '';
        $secret   = isset($_POST['wpaiassist_secret']) ? sanitize_text_field(trim($_POST['wpaiassist_secret'])) : '';

        update_option(WPAIASSIST_OPTION_ENDPOINT, $endpoint);
        update_option(WPAIASSIST_OPTION_SECRET, $secret);

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $endpoint = esc_attr(get_option(WPAIASSIST_OPTION_ENDPOINT, ''));
    $secret   = esc_attr(get_option(WPAIASSIST_OPTION_SECRET, ''));
    ?>
    <div class="wrap">
        <h1>WP → AIAssist via Cloudflare Worker</h1>
        <form method="post">
            <?php wp_nonce_field('wpaiassist_save_settings'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="wpaiassist_endpoint">Cloudflare Worker Endpoint</label>
                    </th>
                    <td>
                        <input type="url"
                               name="wpaiassist_endpoint"
                               id="wpaiassist_endpoint"
                               value="<?php echo $endpoint; ?>"
                               class="regular-text"
                               placeholder="https://your-worker.xxx.workers.dev">
                        <p class="description">
                            The full URL of your Cloudflare Worker that will handle post data
                            (e.g. AI translation, posting to Telegram, etc.).
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="wpaiassist_secret">Secret (for verification)</label>
                    </th>
                    <td>
                        <input type="text"
                               name="wpaiassist_secret"
                               id="wpaiassist_secret"
                               value="<?php echo $secret; ?>"
                               class="regular-text" />
                        <p class="description">
                            Shared secret between WordPress and the Worker.
                            Use it on the Worker side (e.g. <code>env.WP_SECRET</code>) to verify that
                            the request really comes from this site.
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit"
                        name="wpaiassist_save_settings"
                        class=
