<?php

namespace PrestoPlayer\Services\API;

use PrestoPlayer\Models\Preset;

class RestPresetsController extends \WP_REST_Controller
{
    protected $namespace = 'presto-player';
    protected $version = 'v1';
    protected $base = 'preset';

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
     * Register presets routes
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route("{$this->namespace}/{$this->version}", '/' . $this->base, [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args'                => [],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args'                => $this->get_endpoint_args_for_item_schema(true),
            ],
            'schema' => [$this, 'get_preset_schema']
        ]);

        register_rest_route("{$this->namespace}/{$this->version}", '/' . $this->base . '/(?P<id>\d+)', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param) {
                            return is_numeric($param);
                        }
                    ],
                    'context' => [
                        'default' => 'view',
                    ],
                ],
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
                'args'                => $this->get_endpoint_args_for_item_schema(false),
            ],
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
                'args'                => [
                    'force' => [
                        'default' => false,
                    ],
                ],
            ],
            'schema' => [$this, 'get_preset_schema']
        ]);
    }

    public function get_preset_schema()
    {
        return [
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'title'                => 'preset',
            'type'                 => 'object',
            'properties'           => [
                'id' => [
                    'description'  => esc_html__('Unique identifier for the object.', 'presto-player'),
                    'type'         => 'integer',
                    'context'      => array('view', 'edit', 'embed'),
                    'readonly'     => true,
                ],
                'name' => [
                    'description'  => esc_html__('Name for the preset.', 'presto-player'),
                    'type' => 'string',
                    'required' => true,
                    'validate_callback' => 'is_string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'slug' => [
                    'description'  => esc_html__('Preset url slug', 'presto-player'),
                    'type'         => 'string',
                    'readonly'     => true,
                ],
                'icon' => [
                    'description'  => esc_html__('Icon for the preset.', 'presto-player'),
                    'type' => 'string',
                    'required' => true,
                    'validate_callback' => 'is_string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'skin' => [
                    'description'  => esc_html__('Skin for the preset.', 'presto-player'),
                    'type' => 'string',
                    'validate_callback' => 'is_string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'caption_style' => [
                    'description'  => esc_html__('Style for the caption.', 'presto-player'),
                    'type' => 'string',
                    'validate_callback' => 'is_string',
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'caption_background' => [
                    'description'  => esc_html__('Caption backgrounds.', 'presto-player'),
                    'type' => 'string',
                    'validate_callback' => 'is_string',
                    'sanitize_callback' => 'sanitize_hex_color'
                ],
                'created_by' => [
                    'description'  => esc_html__('The id of the user object, if author was a user.', 'presto-player'),
                    'type'         => 'integer',
                    'readonly'     => true,
                ],
                'play' =>  [
                    'type' => 'boolean'
                ],
                'play-large' => [
                    'type' => 'boolean',
                ],
                'rewind' => [
                    'type' => 'boolean',
                ],
                'fast-forward' => [
                    'type' => 'boolean',
                ],
                'current-time' => [
                    'type' => 'boolean'
                ],
                'progress' => [
                    'type' => 'boolean',
                ],
                'mute' => [
                    'type' => 'boolean',
                ],
                'volume' => [
                    'type' => 'boolean',
                ],
                'speed' => [
                    'type' => 'boolean',
                ],
                'pip' => [
                    'type' => 'boolean',
                ],
                'fullscreen' => [
                    'type' => 'boolean',
                ],
                'captions' => [
                    'type' => 'boolean',
                ],
                'reset_on_end' => [
                    'type' => 'boolean',
                ],
                'auto_hide' => [
                    'type' => 'boolean',
                ],
                'captions_enabled' => [
                    'type' => 'boolean',
                ],
                'sticky_scroll' => [
                    'type' => 'boolean',
                ],
                'sticky_scroll_position' => [
                    'type' => 'string',
                ],
                'on_video_end' => [
                    'type' => 'string',
                ],
                'play_video_viewport' => [
                    'type' => 'boolean',
                ],
                'save_player_position' => [
                    'type' => 'boolean'
                ],
                'hide_youtube' => [
                    'type' => 'boolean',
                ],
                'lazy_load_youtube' => [
                    'type' => 'boolean',
                ],
                'hide_logo' => [
                    'type' => 'boolean',
                ],
                'border_radius' => [
                    'type' => 'integer'
                ],
                'watermark' => [
                    'type' => 'object',
                    'properties' => [
                        'enabled' => [
                            'type' => 'boolean'
                        ],
                        'text' => [
                            'type' => 'string'
                        ],
                        'backgroundColor' => [
                            'type' => 'string'
                        ],
                        'color' => [
                            'type' => 'string'
                        ],
                        'opacity' => [
                            'type' => 'integer'
                        ],
                        'position' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'search' => [
                    'type' => 'object',
                    'properties' => [
                        'enabled' => [
                            'type' => 'boolean'
                        ],
                        'minMatchCharLength' => [
                            'type' => 'integer'
                        ],
                        'threshold' => [
                            'type' => 'number'
                        ],
                        'placeholder' => [
                            'type' => 'string'
                        ]
                    ]
                ],
                'cta' => [
                    'type' => 'object',
                    'properties' => [
                        'enabled' => [
                            'type' => 'boolean'
                        ],
                        'percentage' => [
                            'type' => 'integer'
                        ],
                        'show_rewatch' => [
                            'type' => 'boolean'
                        ],
                        'show_skip' => [
                            'type' => 'boolean'
                        ],
                        'headline' => [
                            'type' => 'string'
                        ],
                        'show_button' => [
                            'type' => 'boolean'
                        ],
                        'bottom_text' => [
                            'type' => 'string'
                        ],
                        'button_color' => [
                            'type' => 'string'
                        ],
                        'button_text_color' => [
                            'type' => 'string'
                        ],
                        'background_opacity' => [
                            'type' => 'integer'
                        ],
                        'button_text' => [
                            'type' => 'string'
                        ],
                        'button_link' => [
                            'type' => 'object',
                            'required' => true
                        ],
                        'border_radius' => [
                            'type' => 'integer'
                        ],
                    ]
                ],
                'email_collection' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer'
                        ],
                        'enabled' => [
                            'type' => 'boolean'
                        ],
                        'behavior' =>  [
                            'type' => 'string'
                        ],
                        'percentage' => [
                            'type' => 'integer'
                        ],
                        'allow_skip' => [
                            'type' => 'boolean'
                        ],
                        'provider' => [
                            'type' => 'string'
                        ],
                        'provider_list' => [
                            'type' => 'string'
                        ],
                        'provider_tag' => [
                            'type' => 'string'
                        ],
                        'button_radius' => [
                            'type' => 'integer'
                        ],
                        'headline' => [
                            'type' => 'string'
                        ],
                        'bottom_text' => [
                            'type' => 'string'
                        ],
                        'button_text' => [
                            'type' => 'string'
                        ],
                        'button_color' => [
                            'type' => 'string'
                        ],
                        'button_text_color' => [
                            'type' => 'string'
                        ],
                    ]
                ],
                'action_bar' => [
                    'type' => 'object',
                    'properties' => [
                        'enabled' => [
                            'type' => 'boolean'
                        ],
                        'percentage_start' => [
                            'type' => 'integer'
                        ],
                        'text' => [
                            'type' => 'string'
                        ],
                        'background_color' => [
                            'type' => 'string'
                        ],
                        'button_type' => [
                            'type' => 'string'
                        ],
                        'button_count' => [
                            'type' => 'boolean'
                        ],
                        'button_text' => [
                            'type' => 'string'
                        ],
                        'button_radius' => [
                            'type' => 'integer'
                        ],
                        'button_color' => [
                            'type' => 'string'
                        ],
                        'button_text_color' => [
                            'type' => 'string'
                        ],
                        'button_link' => [
                            'type' => 'object',
                        ],
                    ]
                ],
                'is_locked' => [
                    'type' => 'boolean',
                    'readonly' => true,
                ],
                'created_at' => [
                    'type' => 'string',
                    'readonly' => true
                ]
            ],
        ];
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items($request)
    {
        $preset = new Preset();
        $items = $preset->fetch([
            'per_page' => 10000,
            'order_by' => [
                'is_locked' => 'DESC',
                'created_at' => 'ASC',
            ]
        ]);

        if (is_wp_error($items)) {
            return $items;
        }

        if (!isset($items->data)) {
            return new \WP_Error('error', 'Something went wrong');
        }

        foreach ($items->data as $item) {
            $itemdata = $this->prepare_item_for_response($item, $request);
            $data[] = $this->prepare_response_for_collection($itemdata);
        }

        return new \WP_REST_Response($data, 200);
    }

    /**
     * Get one item from the collection
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_Error|\WP_REST_Response
     */
    public function get_item($request)
    {
        $item = new Preset($request['id']);
        $data = $this->prepare_item_for_response($item, $request);
        return new \WP_REST_Response($data, 200);
    }

    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function create_item($request)
    {
        $item = $this->prepare_item_for_database($request);

        $preset = new Preset();
        $preset->create($item);
        $preset->fresh();

        $data = $this->prepare_item_for_response($preset, $request);

        if (is_wp_error($data)) {
            return $data;
        }

        if (!empty($data)) {
            return new \WP_REST_Response($data, 200);
        }

        return new \WP_Error('cant-create', __('Cannot create preset.', 'presto-player'), ['status' => 500]);
    }

    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function update_item($request)
    {
        $item = $this->prepare_item_for_database($request);

        $preset = new Preset($request['id']);

        $preset->update($item);

        $data = $this->prepare_item_for_response($preset, $request);

        if (is_wp_error($data)) {
            return $data;
        }

        if (!empty($data)) {
            return new \WP_REST_Response($data, 200);
        }

        return new \WP_Error('cant-update', __('Cannot update preset.', 'presto-player'), ['status' => 500]);
    }

    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_item($request)
    {
        $preset = new Preset($request['id']);
        $trashed = $preset->trash();

        if ($trashed) {
            return new \WP_REST_Response(true, 200);
        }

        if (is_wp_error($trashed)) {
            return $trashed;
        }

        return new \WP_Error('cant-trash', __('This preset could not be trashed.', 'presto-player'), ['status' => 500]);
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_items_permissions_check($request)
    {
        return $this->get_item_permissions_check($request);
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_item_permissions_check($request)
    {
        return current_user_can('edit_posts');
    }

    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check($request)
    {
        return $this->get_item_permissions_check($request);
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function update_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }

    /**
     * Check if a given request has access to delete a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function delete_item_permissions_check($request)
    {
        return $this->create_item_permissions_check($request);
    }


    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database($request)
    {
        $email = wp_parse_args(
            $request['email_collection'],
            [
                'enabled' => false,
                'behavior' => 'pause',
                'percentage' => 0,
                'allow_skip' => false,
                'provider' => '',
                'provider_tag' => '',
                'provider_list' => '',
                'border-radius' => 0,
                'headline' => '',
                'bottom_text' => '',
                'button_text' => ''
            ]
        );

        $watermark = wp_parse_args(
            $request['watermark'],
            [
                'enabled' => false,
                'text' => '',
                'backgroundColor' => '',
                'color' => '',
                'opacity' => 0,
                'position' => ''
            ]
        );

        $search = wp_parse_args(
            $request['search'],
            [
                'enabled' => false,
                'minMatchCharLength' => 1,
                'threshold' => 0.3,
                'placeholder' => 'Search'
            ]
        );

        $cta = wp_parse_args(
            $request['cta'],
            [
                'enabled' => false,
                'percentage' => 100,
                'show_rewatch' => true,
                'show_skip' => true,
                'headline' => '',
                'bottom_text' => '',
                'show_button' => true,
                'button_text' => '',
                'button_color' => '',
                'button_text_color' => '',
                'background_opacity' => 0,
                'button_radius' => 0,
                'button_link' => [
                    'id' => '',
                    'url' => '',
                    'type' => '',
                    'opensInNewTab' => false,
                ]
            ]
        );

        $action_bar = wp_parse_args(
            $request['action_bar'],
            [
                'enabled' => false,
                'percentage_start' => 0,
                'text' => '',
                'background_color' => '',
                'button_type' => 'none',
                'button_count' => false,
                'button_text' => '',
                'button_radius' => 0,
                'button_color' => '',
                'button_text_color' => '',
                'button_link' => [
                    'id' => '',
                    'url' => '',
                    'type' => '',
                    'opensInNewTab' => false,
                ],
            ]
        );

        $prepared = [
            'name' => sanitize_text_field($request['name']),
            'skin' => sanitize_text_field($request['skin']),
            'play-large' => (bool) $request['play-large'],
            'rewind' => (bool) $request['rewind'],
            'play' => (bool) $request['play'],
            'fast-forward' => (bool) $request['fast-forward'],
            'progress' => (bool) $request['progress'],
            'current-time' => (bool) $request['current-time'],
            'mute' => (bool) $request['mute'],
            'volume' => (bool) $request['volume'],
            'speed' => (bool) $request['speed'],
            'pip' => (bool) $request['pip'],
            'fullscreen' => (bool) $request['fullscreen'],
            'captions' => (bool) $request['captions'],
            // behavior
            'save_player_position' => (bool) $request['save_player_position'],
            'reset_on_end' => (bool) $request['reset_on_end'],
            'auto_hide' => (bool) $request['auto_hide'],
            'captions_enabled' => (bool) $request['captions_enabled'],
            'sticky_scroll' =>  (bool) $request['sticky_scroll'],
            'sticky_scroll_position' =>  sanitize_text_field($request['sticky_scroll_position']),
            'on_video_end' =>  sanitize_text_field($request['on_video_end']),
            'play_video_viewport' =>  (bool)$request['play_video_viewport'],
            'hide_youtube' =>  (bool) $request['hide_youtube'],
            'lazy_load_youtube' => (bool) $request['lazy_load_youtube'],
            // style
            'hide_logo' => (bool) $request['hide_logo'],
            'caption_background' => sanitize_hex_color($request['caption_background']),
            'caption_style' => sanitize_text_field($request['caption_style']),
            'border_radius' => (int) $request['border_radius'],
            'watermark' => [
                'enabled' => (bool)$watermark['enabled'],
                'text' => wp_kses_post($watermark['text']),
                'backgroundColor' => sanitize_hex_color($watermark['backgroundColor']),
                'color' => sanitize_hex_color($watermark['color']),
                'opacity' => (int) $watermark['opacity'],
                'position' => sanitize_text_field($watermark['position'])
            ],
            'search' => [
                'enabled' => (bool)$search['enabled'],
                'minMatchCharLength' => (int) $search['minMatchCharLength'],
                'threshold' => (float) $search['threshold'],
                'placeholder' => sanitize_text_field($search['placeholder']),
            ],
            'cta' => [
                'enabled' => (bool) $cta['enabled'],
                'percentage' => (int) $cta['percentage'],
                'show_rewatch' => (bool) $cta['show_rewatch'],
                'show_skip' => (bool) $cta['show_skip'],
                'headline' => sanitize_text_field($cta['headline']),
                'bottom_text' => wp_kses_post($cta['bottom_text']),
                'show_button' => (bool)$cta['show_button'],
                'button_text' => sanitize_text_field($cta['button_text']),
                'button_color' => sanitize_hex_color($cta['button_color']),
                'button_text_color' => sanitize_hex_color($cta['button_text_color']),
                'background_opacity' => (int) $cta['background_opacity'],
                'button_link' => [
                    'id' => sanitize_text_field(wp_kses_post($cta['button_link']['id'])),
                    'url' => esc_url_raw($cta['button_link']['url']),
                    'type' => sanitize_text_field(wp_kses_post($cta['button_link']['type'])),
                    'opensInNewTab' => (bool) $cta['button_link']['opensInNewTab'],
                ],
                'button_radius' => (int) $cta['button_radius'],
            ],
            'email_collection' => [
                'enabled' => (bool)$email['enabled'],
                'behavior' => sanitize_text_field($email['behavior']),
                'percentage' => (int) $email['percentage'],
                'allow_skip' => (bool) $email['allow_skip'],
                'provider' => sanitize_text_field($email['provider']),
                'provider_list' => sanitize_text_field($email['provider_list']),
                'provider_tag' => sanitize_text_field($email['provider_tag']),
                'border_radius' => (int) $email['border_radius'],
                'headline' => sanitize_text_field($email['headline']),
                'bottom_text' => wp_kses_post($email['bottom_text']),
                'button_text' => sanitize_text_field($email['button_text']),
                'button_color' => sanitize_hex_color($email['button_color']),
                'button_text_color' => sanitize_hex_color($email['button_text_color']),
            ],
            'action_bar' => [
                'enabled' => (bool) $action_bar['enabled'],
                'percentage_start' => (int) $action_bar['percentage_start'],
                'text' => wp_kses_post($action_bar['text']),
                'background_color' => sanitize_hex_color($action_bar['background_color']),
                'button_type' => sanitize_text_field(wp_kses_post($action_bar['button_type'])),
                'button_count' => (bool) $action_bar['button_count'],
                'button_text' => sanitize_text_field(wp_kses_post($action_bar['button_text'])),
                'button_radius' => (int) $action_bar['button_radius'],
                'button_color' => sanitize_hex_color($action_bar['button_color']),
                'button_text_color' => sanitize_hex_color($action_bar['button_text_color']),
                'button_link' => [
                    'id' => sanitize_text_field(wp_kses_post($action_bar['button_link']['id'])),
                    'url' => esc_url_raw($action_bar['button_link']['url']),
                    'type' => sanitize_text_field(wp_kses_post($action_bar['button_link']['type'])),
                    'opensInNewTab' => (bool) $action_bar['button_link']['opensInNewTab'],
                ],
            ]
        ];


        return $prepared;
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response($item, $request)
    {
        $item = $item->toArray();
        $schema = $this->get_preset_schema();
        $prepared = [];
        foreach ($item as $name => $value) {
            if (!empty($schema['properties'][$name])) {
                $prepared[$name] = rest_sanitize_value_from_schema($value, $schema['properties'][$name], $name);
            }
        }

        return $prepared;
    }

    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params()
    {
        return [
            'page'     => [
                'description'       => 'Current page of the collection.',
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ],
            'per_page' => [
                'description'       => 'Maximum number of items to be returned in result set.',
                'type'              => 'integer',
                'default'           => 10,
                'sanitize_callback' => 'absint',
            ],
            'search'   => [
                'description'       => 'Limit results to those matching a string.',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];
    }
}
