<?php

namespace PrestoPlayer\Models;

class Webhook extends Model
{
    /**
     * Table used to access db
     *
     * @var string
     */
    protected $table = 'presto_player_webhooks';

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
            'url' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_url'
            ],
            'method' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'email_name' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'headers' => [
                'type' => 'array'
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
    protected $queryable = ['name'];

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
            return new \WP_Error('missing_parameter', __('You must enter a name for the webhook.', 'presto-player'));
        }

        // generate slug on the fly
        $args['name'] = !empty($args['name']) ? $args['name'] : sanitize_title($args['name']);

        // create
        return parent::create($args);
    }
}
