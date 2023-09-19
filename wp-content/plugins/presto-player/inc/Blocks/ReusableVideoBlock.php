<?php

namespace PrestoPlayer\Blocks;

use PrestoPlayer\Models\ReusableVideo;

class ReusableVideoBlock
{
    /**
     * Block name
     *
     * @var string
     */
    protected $name = 'reusable-display';

    /**
     * Register Block
     *
     * @return void
     */
    public function register()
    {
        register_block_type(
            "presto-player/$this->name",
            [
                'render_callback' => [$this, 'html'],
            ]
        );
    }

    /**
     * Dynamic block output
     *
     * @param array $attributes
     * @param string $content
     * @return void
     */
    public function html($attributes)
    {
        $block = new ReusableVideo($attributes['id']);
        return $block->renderBlock();
    }
}
