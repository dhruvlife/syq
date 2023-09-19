<?php

namespace PrestoPlayer\Blocks;

use PrestoPlayer\Support\Block;

class VimeoBlock extends Block
{
    /**
     * Block name
     *
     * @var string
     */
    protected $name = 'vimeo';

    /**
     * Register the block type.
     *
     * @return void
     */
    public function registerBlockType()
    {
        register_block_type(
            PRESTO_PLAYER_PLUGIN_DIR . 'src/admin/blocks/blocks/vimeo',
            array(
                'render_callback' => [$this, 'html'],
            )
        );
    }
}
