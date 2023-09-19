<?php
namespace PrestoPlayer;

use PrestoPlayer\Services\ReusableVideos;
use PrestoPlayer\Blocks\AudioBlock;
use PrestoPlayer\Blocks\SelfHostedBlock;
use PrestoPlayer\Blocks\VimeoBlock;
use PrestoPlayer\Blocks\YouTubeBlock;
use PrestoPlayer\Pro\Blocks\BunnyCDNBlock;

class Playlist
{
    /**
     * Parses the attributes with respect to the provider.
     * 
     * @param string $block_name Block name.
     * @param array $attributes Attributes of the block.
     * 
     * @return array
     */
    public function parsed_attributes($block_name, $attributes) {
        $attributes = wp_parse_args(
            $attributes,
            [
                'id' => '',
                'src' => '',
                'title' => '',
                'provider' => '',
                'class' => '',
                'custom_field' => '',
                'poster' => '',
                'preload' => 'auto',
                'preset' => 0,
                'autoplay' => false,
                'plays_inline' => false,
                'chapters' => [],
                'overlays' => [],
                'tracks' => [],
                'muted_autoplay_preview' => false,
                'muted_autoplay_caption_preview' => false,
            ],
        );

        switch ($block_name) {
            case 'presto-player/self-hosted':
                return (new SelfHostedBlock())->getAttributes($attributes, '');
        
            case 'presto-player/youtube':
                return (new YouTubeBlock())->getAttributes($attributes, '');
        
            case 'presto-player/vimeo':
                return (new VimeoBlock())->getAttributes($attributes, '');
        
            case 'presto-player/bunny':
                return (new BunnyCDNBlock())->getAttributes($attributes, '');
        
            case 'presto-player/audio':
                return (new AudioBlock())->getAttributes($attributes, '');
        }
    }
    /**
     * Get Video details
     *
     * @param $videos Array of video IDs.
     * @return array
     */
    public function get_playlist_details($video)
    {
        if ( empty( $video ) ) {
            return [];
        }
        $block = parse_blocks(ReusableVideos::get($video));
        // return $block;
        if ( !isset($block[0]['innerBlocks'][0]['attrs']) ) {
            return [];
        }
        $inner_block = $block[0]['innerBlocks'][0];
        $attributes = $inner_block['attrs'];
        $video_details = $this->parsed_attributes($inner_block['blockName'], $attributes);
        return $video_details;
    }
}
