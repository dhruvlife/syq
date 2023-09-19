<?php

namespace PrestoPlayer\Models;

class Preset extends Model
{
    /**
     * Table used to access db
     *
     * @var string
     */
    protected $table = 'presto_player_presets';

    /**
     * Model Schema
     *
     * @var array
     */
    public function schema()
    {
        return [
            'id' => [
                'type' => 'integer'
            ],
            'name' => [
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post'
            ],
            'slug' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_title'
            ],
            'icon' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'skin' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'caption_style' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'caption_background' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_hex_color'
            ],
            'play' => [
                'type' => 'boolean',
                'default' => false
            ],
            'play-large' => [
                'type' => 'boolean',
                'default' => false
            ],
            'rewind' => [
                'type' => 'boolean',
                'default' => false
            ],
            'fast-forward' => [
                'type' => 'boolean',
                'default' => false
            ],
            'progress' => [
                'type' => 'boolean',
                'default' => false
            ],
            'current-time' => [
                'type' => 'boolean',
                'default' => false
            ],
            'mute' => [
                'type' => 'boolean',
                'default' => false
            ],
            'volume' => [
                'type' => 'boolean',
                'default' => false
            ],
            'speed' => [
                'type' => 'boolean',
                'default' => false
            ],
            'pip' => [
                'type' => 'boolean',
                'default' => false
            ],
            'fullscreen' => [
                'type' => 'boolean',
                'default' => false
            ],
            'captions' => [
                'type' => 'boolean',
                'default' => false
            ],
            'reset_on_end' => [
                'type' => 'boolean',
                'default' => false
            ],
            'auto_hide' => [
                'type' => 'boolean',
                'default' => false
            ],
            'captions_enabled' => [
                'type' => 'boolean',
                'default' => false
            ],
            'sticky_scroll' => [
                'type' => 'boolean',
                'default' => false
            ],
            'sticky_scroll_position' => [
                'type' => 'string',
                'default' => 'bottom right'
            ],
            'on_video_end' => [
                'type' => 'string',
                'default' => 'select'
            ],
            'play_video_viewport' => [
                'type' => 'boolean',
                'default' => false
            ],
            'save_player_position' => [
                'type' => 'boolean',
                'default' => false
            ],
            'hide_youtube' => [
                'type' => 'boolean',
                'default' => false
            ],
            'lazy_load_youtube' => [
                'type' => 'boolean',
                'default' => false
            ],
            'hide_logo' => [
                'type' => 'boolean',
                'default' => false
            ],
            'border_radius' => [
                'type' => 'integer',
                'default' => 0
            ],
            'cta' => [
                'type' => 'array',
            ],
            'watermark' => [
                'type' => 'array'
            ],
            'search' => [
                'type' => 'array'
            ],
            'email_collection' => [
                'type' => 'array',
            ],
            'action_bar' => [
                'type' => 'array',
            ],
            'is_locked' => [
                'type' => 'boolean',
                'default' => false
            ],
            'created_by' => [
                'type' => 'integer',
                'default' => get_current_user_id()
            ],
            'created_at' => [
                'type' => 'string'
            ],
            'updated_at' => [
                'type' => 'string',
                'default' => current_time('mysql')
            ],
            'deleted_at' => [
                'type' => 'string'
            ]
        ];
    }

    /**
     * These attributes are queryable
     *
     * @var array
     */
    protected $queryable = ['slug'];

    /**
     * Create a preset in the db
     *
     * @param array $args
     * @return integer
     */
    public function create($args = [])
    {
        // name is required
        if (empty($args['name'])) {
            return new \WP_Error('missing_parameter', __('You must enter a name for the preset.', 'presto-player'));
        }

        // generate slug on the fly
        $args['slug'] = !empty($args['slug']) ? $args['slug'] : sanitize_title($args['name']);

        // create
        return parent::create($args);
    }
}
