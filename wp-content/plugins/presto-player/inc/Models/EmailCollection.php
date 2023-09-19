<?php

namespace PrestoPlayer\Models;

class EmailCollection extends Model
{
    /**
     * Table used to access db
     *
     * @var string
     */
    protected $table = 'presto_player_email_collection';

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
            'enabled' => [
                'type' => 'boolean',
            ],
            'behavior' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'percentage' => [
                'type' => 'integer'
            ],
            'allow_skip' => [
                'type' => 'boolean'
            ],
            'email_provider' => [
                'type' => 'string'
            ],
            'email_provider_list' => [
                'type' => 'string'
            ],
            'email_provider_tag' => [
                'type' => 'string'
            ],
            'headline' =>  [
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post'
            ],
            'bottom_text' => [
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post'
            ],
            'button_text' => [
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post'
            ],
            'border_radius' => [
                'type' => 'integer'
            ],
            'preset_id' => [
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
}
