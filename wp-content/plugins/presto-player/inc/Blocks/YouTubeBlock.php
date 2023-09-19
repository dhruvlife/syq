<?php

namespace PrestoPlayer\Blocks;

use PrestoPlayer\Models\Preset;
use PrestoPlayer\Support\Block;

class YouTubeBlock extends Block
{
    /**
     * Block name
     *
     * @var string
     */
    protected $name = 'youtube';

    /**
     * Add url to template
     *
     * @param array $attributes
     * @return array
     */
    public function additionalAttributes()
    {
        return [
            'src' => [
                'type' => 'string'
            ]
        ];
    }

    /**
     * Make youtube URL from attributes
     *
     * @param array $attributes
     * @return string
     */
    public function makeUrl($attributes)
    {
        $id = $this->getIdFromURL(!empty($attributes['src']) ? $attributes['src'] : '');

        // build youtube url
        $url = !empty($id) ? "//www.youtube.com/embed/{$id}" : "";
        return add_query_arg([
            'iv_load_policy' => 3,
            'modestbranding' => 1,
            'playinline' => !empty($attributes['playsInline']) ? 1 : 0,
            'showinfo' => 0,
            'rel' => 0,
            'enablejsapi' => 1,
            // 'autoplay' => !empty($attributes['autoplay']) ? 1 : 0
        ], $url);
    }

    /**
     * Add src
     *
     * @param array $attributes
     * @return void
     */
    public function sanitizeAttributes($attributes, $default_config)
    {
        $preset = new Preset($attributes['preset']);
        $id = $this->getIdFromURL(!empty($attributes['src']) ? $attributes['src'] : '');

        return [
            'video_id' => !empty($attributes['id']) ? $attributes['id'] : 0,
            'provider_video_id' => $id,
            'src'   => $this->makeUrl($attributes),
            'poster' => isset($attributes['poster']) ? esc_url($attributes['poster']) : false,
            'hide_youtube' => !empty((bool)$preset->hide_youtube),
        ];
    }

    /**
     * Gets the id from the Youtube URL
     *
     * @param string $url
     * @return string
     */
    public function getIdFromURL($url = '')
    {
        preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
        return !empty($matches[1]) ? $matches[1] : '';
    }

    /**
     * Register the block type.
     *
     * @return void
     */
    public function registerBlockType()
    {
        register_block_type(
            PRESTO_PLAYER_PLUGIN_DIR . 'src/admin/blocks/blocks/youtube',
            array(
                'render_callback' => [$this, 'html'],
            )
        );
    }
}
