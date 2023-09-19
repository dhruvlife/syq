<?php

namespace PrestoPlayer;

use PrestoPlayer\Plugin;
use PrestoPlayer\Attachment;
use PrestoPlayer\Controller;
use PrestoPlayer\Support\Block;
use PrestoPlayer\Services\Scripts;
use PrestoPlayer\Services\BunnyCDN;
use PrestoPlayer\Services\Settings;
use PrestoPlayer\Services\AdminNotices;

class Factory
{
    const SHARED = ['shared' => true];

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function isPro()
    {
        return Plugin::isPro();
    }

    /**
     * Retrieves the rules for setting up the plugin.
     *
     * @since 2.1.0
     *
     * @return array
     */
    public function getRules()
    {
        return [
            BunnyCDN::class => self::SHARED,
            Visits::class => self::SHARED,
            ReusableVideos::class => self::SHARED,
            AdminNotices::class => self::SHARED,

            Settings::class => [
                'constructParams' => [
                    $this->isPro(),
                ]
            ],


            Attachment::class => [
                'constructParams' => [
                    $this->isPro(),
                ]
            ],

            // blocks
            Block::class => [
                'constructParams' => [
                    $this->isPro(),
                    $this->getPluginVersion(PRESTO_PLAYER_PLUGIN_FILE)
                ]
            ],

            // plugin controller
            Controller::class => [
                'constructParams' => [$this->getComponents()]
            ],

            Scripts::class => [
                'shared' => true,
                'constructParams' => [
                    $this->isPro(),
                    $this->getPluginVersion(PRESTO_PLAYER_PLUGIN_FILE)
                ]
            ]
        ];
    }

    /**
     * Retrieves the plugin version.
     *
     * @param  string $plugin_file The full plugin path.
     *
     * @return string
     */
    protected function getPluginVersion($plugin_file)
    {
        // Load version from plugin data.
        if (!\function_exists('get_plugin_data')) {
            require_once \ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return \get_plugin_data($plugin_file, false, false)['Version'];
    }

    /**
     * Retrieves the list of plugin components run during normal operations
     * (i.e. not including the Uninstallation component).
     */
    public function getComponents()
    {
        $config = require_once 'config/app.php';
        $components = $config['components'];
        $components = array_merge($components, $config['pro_components']);

        return $this->formatComponents($components);
    }

    /**
     * Formats components to use in DICE
     *
     * @param array $components
     * @return array
     */
    public function formatComponents($components = [])
    {
        $formatted = [];

        if (!$components) {
            return [];
        }

        foreach (array_filter($components) as $component) {
            $formatted[] = [$this->instance::INSTANCE => $component];
        }
        return $formatted;
    }
}
