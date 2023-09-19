<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Plugin;
use PrestoPlayer\Models\Setting;

class Settings
{
    /**
     * Register our settings
     *
     * @return void
     */
    public function register()
    {
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('rest_api_init', [$this, 'registerSettings']);
    }

    public function registerSettings()
    {
        /**
         * Analytics settings
         */
        \register_setting(
            'presto_player',
            'presto_player_analytics',
            [
                'type'              => 'object',
                'description'       => __('Analytics settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_analytics',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'enable' => [
                                'type' => 'boolean',
                            ],
                            'purge_data' => [
                                'type' => 'boolean',
                            ]
                        ]
                    ]
                ],
                'default' => [
                    'enable' => false,
                    'purge_data' => true
                ]
            ]
        );

        /**
         * Branding settings
         */
        \register_setting(
            'presto_player',
            'presto_player_branding',
            [
                'type'              => 'object',
                'description'       => __('Branding settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_branding',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'logo' => [
                                'type' => 'string',
                                'sanitize_callback' => 'esc_url_raw'
                            ],
                            'logo_width' => [
                                'type' => 'number',
                                'sanitize_callback' => 'intval'
                            ],
                            'color' => [
                                'type' => 'string',
                                'sanitize_callback' => 'sanitize_hex_color'
                            ],
                            'player_css' => [
                                'type' => 'string'
                            ]
                        ]
                    ]
                ],
                'default' => [
                    'logo' => '',
                    'logo_width' => 150,
                    'color' => Setting::getDefaultColor(),
                    'player_css' => ''
                ]
            ]
        );

        \register_setting(
            'presto_player',
            'presto_player_performance',
            [
                'type'              => 'object',
                'description'       => __('Performance settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_performance',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'module_enabled' => [
                                'type' => 'boolean',
                            ],
                            'automations' => [
                                'type' => 'boolean'
                            ]
                        ]
                    ]
                ],
                'default' => [
                    'module_enabled' => false,
                    'automations' => true
                ]
            ]
        );

        /**
         * Uninstall settings
         */
        \register_setting(
            'presto_player',
            'presto_player_uninstall',
            [
                'type'              => 'object',
                'description'       => __('Uninstall settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_uninstall',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'uninstall_data' => [
                                'type' => 'boolean',
                            ],
                        ]
                    ]
                ],
                'default' => [
                    'uninstall_data' => false,
                ]
            ]
        );

        /**
         * Analytics settings
         */
        \register_setting(
            'presto_player',
            'presto_player_google_analytics',
            [
                'type'              => 'object',
                'description'       => __('Google Analytics settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_google_analytics',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'enable' => [
                                'type' => 'boolean',
                            ],
                            'use_existing_tag' => [
                                'type' => 'boolean'
                            ],
                            'measurement_id' => [
                                'type' => 'string'
                            ]
                        ]
                    ]
                ],
                'default' => [
                    'enable' => false,
                    'use_existing_tag' => false,
                    'measurement_id' => ''
                ]
            ]
        );

        /**
         * General settings
         */
        \register_setting(
            'presto_player',
            'presto_player_presets',
            [
                'type'              => 'object',
                'description'       => __('Preset settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_presets',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'default_player_preset' => [
                                'type' => 'integer',
                                'sanitize_callback' => 'intval'
                            ]
                        ]
                    ]
                ],
                'default' => [
                    'default_player_preset' => 1,
                ]
            ]
        );

        \register_setting(
            'presto_player',
            'presto_player_audio_presets',
            [
                'type'              => 'object',
                'description'       => __('Preset settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_audio_presets',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'default_player_preset' => [
                                'type' => 'integer',
                                'sanitize_callback' => 'intval'
                            ]
                        ]
                    ]
                ],
                'default' => [
                    'default_player_preset' => 1,
                ]
            ]
        );

        /**
         * Youtube Settings
         */
        \register_setting(
            'presto_player',
            'presto_player_youtube',
            [
                'type'              => 'object',
                'description'       => __('Youtube settings.', 'presto-player'),
                'show_in_rest'      => [
                    'name' =>  'presto_player_youtube',
                    'type'  => 'object',
                    'schema' => [
                        'properties' => [
                            'nocookie' => [
                                'type' => 'boolean',
                            ],
                            'channel_id' => [
                                'type' => 'string'
                            ]
                        ]
                    ]
                ],
                'default' => [
                    'nocookie' => false,
                    'channel_id' => ''
                ]
            ]
        );
    }

    public static function template()
    { ?>
        <?php do_action('presto_player_settings_header'); ?>
        <div class="presto-player-dashboard__header">
            <img class="presto-player-dashboard__logo" src="<?php echo esc_url(PRESTO_PLAYER_PLUGIN_URL . '/img/logo.svg'); ?>" />
            <div class="presto-player-dashboard__version">v<?php echo esc_html(Plugin::version()); ?></div>
        </div>
        <div id="presto-settings-page"></div>
        <?php wp_auth_check_html(); ?>
        <?php do_action('presto_player_settings_footer'); ?>
<?php }
}
