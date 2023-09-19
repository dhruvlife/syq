<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Plugin;
use PrestoPlayer\WPackio\Enqueue;

class Menu
{
    protected $enqueue;

    public function register()
    {
        add_action('admin_menu', [$this, 'addMenu']);
    }

    public function addMenu()
    {
        add_menu_page(
            __('Presto Player', 'presto-player'),
            __('Presto Player', 'presto-player'),
            'publish_posts',
            'edit.php?post_type=pp_video_block',
            '',
            PRESTO_PLAYER_PLUGIN_URL . 'img/menu-icon.svg'
        );

        add_submenu_page(
            'edit.php?post_type=pp_video_block',
            __('Media Hub', 'presto-player'),
            __('Media Hub', 'presto-player'),
            'publish_posts',
            'edit.php?post_type=pp_video_block'
        );

        $analyics_page = add_submenu_page(
            'edit.php?post_type=pp_video_block',
            __('Analytics', 'presto-player'),
            !Plugin::isPro() ? __('Analytics', 'presto-player') . ' <span class="update-plugins" style="background-color: #ffffff1c"><span class="plugin-count">Pro</span></span>' : __('Analytics', 'presto-player'),
            'publish_posts',
            'presto-analytics',
            function () {
                ob_start();
?>
            <div class="presto-player-dashboard__header">
                <img class="presto-player-dashboard__logo" src="<?php echo esc_url(PRESTO_PLAYER_PLUGIN_URL . '/img/logo.svg'); ?>" />
                <div class="presto-player-dashboard__version">v<?php echo esc_html(Plugin::version()); ?></div>
            </div>
            <div id="presto-analytics-page"></div>
            <?php wp_auth_check_html(); ?>
<?php
                $page = ob_get_clean();
                echo $page;
            }
        );

        add_action("admin_print_scripts-{$analyics_page}", [$this, 'analyticsAssets']);

        $settings_page = add_submenu_page(
            'edit.php?post_type=pp_video_block',
            __('Presto Player Settings', 'presto-player'),
            __('Settings', 'presto-player'),
            'manage_options',
            'presto-player-settings',
            "PrestoPlayer\Services\Settings::template",
            5
        );

        add_action("admin_print_scripts-{$settings_page}", [$this, 'settingsAssets']);
    }

    /**
     * Scripts needed on settings page
     */
    public function settingsAssets()
    {
        wp_enqueue_media();
        wp_enqueue_code_editor(['type' => "text/css"]);

        $assets = include trailingslashit(PRESTO_PLAYER_PLUGIN_DIR) . 'dist/settings.asset.php';
        wp_enqueue_script(
            'surecart/settings/admin',
            trailingslashit(PRESTO_PLAYER_PLUGIN_URL) . 'dist/settings.js',
            array_merge(['wp-codemirror'], $assets['dependencies']),
            $assets['version'],
            true
        );
        // setting style.
        wp_enqueue_style('surecart/settings/admin', trailingslashit(PRESTO_PLAYER_PLUGIN_URL) . 'dist/settings.css', [], $assets['version']);

        wp_enqueue_style('wp-components');

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('surecart/settings/admin', 'presto-player');
        }

        wp_localize_script(
            'surecart/settings/admin',
            'prestoPlayer',
            apply_filters('presto-settings-js-options', [
                'root' => esc_url_raw(get_rest_url()),
                'nonce' => wp_create_nonce('wp_rest'),
                'proVersion' => Plugin::proVersion(),
                'isSetup' => [
                    'bunny' => false
                ],
                'isPremium' => Plugin::isPro(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'wpVersionString' => 'wp/v2/',
                'prestoVersionString' => 'presto-player/v1/',
                'debug' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG
            ])
        );
    }

    /**
     * Scripts needed on analytics page
     */
    public function analyticsAssets()
    {

        $assets = include trailingslashit(PRESTO_PLAYER_PLUGIN_DIR) . 'dist/analytics.asset.php';
        wp_enqueue_script(
            'surecart/analytics/admin',
            trailingslashit(PRESTO_PLAYER_PLUGIN_URL) . 'dist/analytics.js',
            array_merge(['hls.js', 'presto-components', 'media'], $assets['dependencies']),
            $assets['version'],
            true
        );
        wp_enqueue_style('surecart/analytics/admin', trailingslashit(PRESTO_PLAYER_PLUGIN_URL) . 'dist/analytics.css', [], $assets['version']);

        wp_enqueue_style('wp-components');
        wp_enqueue_media();

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('surecart/analytics/admin', 'presto-player');
        }

        wp_localize_script('surecart/analytics/admin', 'prestoPlayer', [
            'root' => esc_url_raw(get_rest_url()),
            'isPremium' => Plugin::isPro(),
            'plugin_url' => esc_url_raw(trailingslashit(PRESTO_PLAYER_PLUGIN_URL)),
            'nonce' => wp_create_nonce('wp_rest'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'wpVersionString' => 'wp/v2/',
            'prestoVersionString' => 'presto-player/v1/',
            'i18n' => Translation::geti18n()
        ]);
    }

    public function template()
    {
        echo '<div id="presto-player-dashboard"></div>';
    }
}
