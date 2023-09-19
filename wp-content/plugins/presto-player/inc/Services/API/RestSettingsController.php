<?php

namespace PrestoPlayer\Services\API;

class RestSettingsController extends \WP_REST_Settings_Controller
{

    /**
     * Constructor.
     *
     * @since 4.7.0
     */
    public function __construct()
    {
        $this->namespace = 'presto-player/v1';
        $this->rest_base = 'settings';
    }

    /**
     * Register controller
     *
     * @return void
     */
    public function register()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Checks if a given request has access to read and manage settings.
     *
     * @since 4.7.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return bool True if the request has read access for the item, otherwise false.
     */
    public function get_item_permissions_check($request)
    {
        return current_user_can('edit_posts');
    }

    /**
     * Retrieves all of the registered options for the Settings API.
     *
     * @since 4.7.0
     *
     * @return array Array of registered options.
     */
    protected function get_registered_options()
    {
        $rest_options = array();

        foreach (get_registered_settings() as $name => $args) {
            if (!in_array($name, ['presto_player_branding', 'presto_player_youtube', 'presto_player_presets', 'presto_player_audio_presets'])) {
                continue;
            }

            if (empty($args['show_in_rest'])) {
                continue;
            }

            $rest_args = array();

            if (is_array($args['show_in_rest'])) {
                $rest_args = $args['show_in_rest'];
            }

            $defaults = array(
                'name'   => !empty($rest_args['name']) ? $rest_args['name'] : $name,
                'schema' => array(),
            );

            $rest_args = array_merge($defaults, $rest_args);

            $default_schema = array(
                'type'        => empty($args['type']) ? null : $args['type'],
                'description' => empty($args['description']) ? '' : $args['description'],
                'default'     => isset($args['default']) ? $args['default'] : null,
            );

            $rest_args['schema']      = array_merge($default_schema, $rest_args['schema']);
            $rest_args['option_name'] = $name;

            // Skip over settings that don't have a defined type in the schema.
            if (empty($rest_args['schema']['type'])) {
                continue;
            }

            /*
             * Allow the supported types for settings, as we don't want invalid types
             * to be updated with arbitrary values that we can't do decent sanitizing for.
             */
            if (!in_array($rest_args['schema']['type'], array('number', 'integer', 'string', 'boolean', 'array', 'object'), true)) {
                continue;
            }

            $rest_args['schema'] = rest_default_additional_properties_to_false($rest_args['schema']);

            $rest_options[$rest_args['name']] = $rest_args;
        }

        return $rest_options;
    }
}
