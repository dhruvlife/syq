<?php

namespace PrestoPlayer\Models;

use PrestoPlayer\Services\Blocks\VimeoBlockService;
use PrestoPlayer\Services\Blocks\YoutubeBlockService;

class Video extends Model
{
    /**
     * Table used to access db
     *
     * @var string
     */
    protected $table = 'presto_player_videos';

    /**
     * Model Schema
     *
     * @var array
     */
    public function schema()
    {
        return [
            'id' => [
                'type' => 'integer',
            ],
            'title' => [
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post'
            ],
            'type' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'src' => [
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw'
            ],
            'external_id' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'attachment_id' => [
                'type' => 'integer',
            ],
            'post_id' => [
                'type' => 'integer'
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
    protected $queryable = [
        'src',
        'video_id',
        'title',
        'type',
        'attachment_id',
        'external_id'
    ];

    public function set($args)
    {
        parent::set($args);

        if (!empty($this->attributes->attachment_id)) {
            $title = get_the_title($this->attributes->attachment_id);
            $src = wp_get_attachment_url($this->attributes->attachment_id);
            $this->attributes->title = $title ? $title : $this->attributes->title;
            $this->attributes->src = $src ? $src : $this->attributes->src;
        }

        return $this;
    }

    public function maybeAutoCreateTitle($args)
    {
        // remotely get the title if not provided
        if (empty($args['title'])) {
            // youtube
            if ($args['type'] === 'youtube') {
                $youtube = new YoutubeBlockService();
                if(isset($args['external_id'])){
                  $api_response = $youtube->getRemoteVideoData($args['external_id']);
                  if (!empty($api_response['title'])) {
                    $args['title'] = $api_response['title'];
                  }
                }
            }
            // vimeo
            if ($args['type'] === 'vimeo') {
                $vimeo = new VimeoBlockService();
                $api_response = $vimeo->getRemoteVideoData($args['external_id']);
                if (!empty($api_response['title'])) {
                    $args['title'] = $api_response['title'];
                }
            }
        }

        // fallback to url
        $args['title'] = empty($args['title']) ? $args['src'] : $args['title'];

        return $args;
    }

    /**
     * Create a new video
     *
     * @param array $args
     * @return integer
     */
    public function create($args = [])
    {
        // required params
        if (empty($args['external_id']) && empty($args['attachment_id']) && empty($args['src'])) {
            return new \WP_Error('invalid_parameters', 'You must enter an attachment_id, external_id or src.');
        }

        $args = $this->maybeAutoCreateTitle($args);

        // create
        return parent::create($args);
    }

    /**
     * Maybe auto-create title if not set
     *
     * @param array $args
     * @return void
     */
    public function update($args = [])
    {
        if (!empty($args['attachment_id']) && !empty($args['title'])) {
            wp_update_post([
                'ID' => $args['attachment_id'],
                'post_title' => $args['title']
            ]);
        }
        return parent::update($args);
    }
}
