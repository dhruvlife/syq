<?php

namespace PrestoPlayer\Models;

use PrestoPlayer\Blocks\AudioBlock;
use PrestoPlayer\Blocks\VimeoBlock;
use PrestoPlayer\Blocks\YouTubeBlock;
use PrestoPlayer\Blocks\SelfHostedBlock;
use PrestoPlayer\Pro\Blocks\BunnyCDNBlock;
use PrestoPlayer\Pro\Blocks\PrivateSelfHostedBlock;
use WP_Query;

class ReusableVideo
{
    public $post;
    private $post_type = 'pp_video_block';

    public function __construct($id = 0)
    {
        if (!empty($id)) {
            $this->post = \get_post($id);
            return $this;
        }
        return $this;
    }

    /**
     * Get attributes properties
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return isset($this->post->$property) ? $this->post->$property : null;
    }

    public function create($args = [])
    {
        return wp_insert_post(wp_parse_args($args, [
            'post_type' => $this->post_type
        ]));
    }

    public function fetch($args = [])
    {
        $args = wp_parse_args($args, [
            'post_type' => $this->post_type,
            'post_status' => array( 'publish' )
        ]);

        return (new WP_Query($args))->posts;
    }

    public function all($args = [])
    {
        $args = wp_parse_args($args, [
            'post_type' => $this->post_type,
            'per_page' => -1
        ]);

        return get_posts($args);
    }

    public function first($args = [])
    {
        $fetched = $this->fetch(wp_parse_args($args, ['per_page' => 1]));
        return !empty($fetched[0]) ? new static($fetched[0]) : false;
    }

    /**
     * Get block from video post
     *
     * @return array
     */
    public function getBlock()
    {
        if (empty($this->post->post_content)) {
            return [];
        }
        $blocks = \parse_blocks($this->post->post_content);

        return !empty($blocks[0]['innerBlocks'][0]) ? $blocks[0]['innerBlocks'][0] : [];
    }

    public function getAttributes($overrides = [])
    {
        $block = $this->getBlock();
        if (empty($block)) {
            return '';
        }

        // allow overriding attributes
        $block['attrs'] = wp_parse_args($overrides, (array)$block['attrs']);

        // maybe switch provider depending on url
        if (!empty($overrides)) {
            $block = $this->maybeSwitchProvider($block);
        }

        switch ($block['blockName']) {
            case 'presto-player/self-hosted':
                return (new SelfHostedBlock())->getAttributes($block['attrs']);

            case 'presto-player/youtube':
                return (new YouTubeBlock())->getAttributes($block['attrs']);

            case 'presto-player/vimeo':
                return (new VimeoBlock())->getAttributes($block['attrs']);

            case 'presto-player/bunny':
                return (new BunnyCDNBlock())->getAttributes($block['attrs']);

            case 'presto-player/audio':
                return (new AudioBlock())->getAttributes($block['attrs']);
        }
    }

    public function renderBlock($overrides = [])
    {
        $block = $this->getBlock();
        if (empty($block)) {
            return '';
        }

        // allow overriding attributes
        $block['attrs'] = wp_parse_args($overrides, (array)$block['attrs']);

        // maybe switch provider depending on url
        if (!empty($overrides)) {
            $block = $this->maybeSwitchProvider($block);
        }

        // remove attachment_id if the src changes.
        if (!empty($overrides['src'])) {
            $block['attrs']['attachment_id'] = null;
        }

        switch ($block['blockName']) {
            case 'presto-player/self-hosted':
                return (new SelfHostedBlock(true, '1'))->html($block['attrs'], '');

            case 'presto-player/youtube':
                return (new YouTubeBlock(true, '1'))->html($block['attrs'], '');

            case 'presto-player/vimeo':
                return (new VimeoBlock(true, '1'))->html($block['attrs'], '');

            case 'presto-player/bunny':
                return (new BunnyCDNBlock(true, '1'))->html($block['attrs'], '');

            case 'presto-player/audio':
                return (new AudioBlock(true, '1'))->html($block['attrs'], '');
        }
    }

    /**
     * Maybe switch provider if the url is overridden
     */
    protected function maybeSwitchProvider($block)
    {
        if (empty($block) || !is_array($block)) {
            return $block;
        }

        if (!empty($block['attrs']['src'])) {
            if ($block['attrs']['src']) {
                $filetype = wp_check_filetype($block['attrs']['src']);
                if (isset($filetype['type']) && false !== strpos($filetype['type'], 'audio')) {
                    $block['blockName'] = 'presto-player/audio';
                    return $block;
                }
            }

            $yt_rx = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
            $has_match_youtube = preg_match($yt_rx, $block['attrs']['src'], $yt_matches);

            if ($has_match_youtube) {
                $block['blockName'] = 'presto-player/youtube';
                return $block;
            }

            $vm_rx = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
            $has_match_vimeo = preg_match($vm_rx, $block['attrs']['src'], $vm_matches);

            if ($has_match_vimeo) {
                $block['blockName'] = 'presto-player/vimeo';
                return $block;
            }

            // default to self-hsoted
            $block['blockName'] = 'presto-player/self-hosted';
            return $block;
        }

        return $block;
    }

    /**
     * Get reusable video block function.
     * 
     * @param mixed $id The ID of the reusable block.
     * @return $content The content of the block.
     */
    public function content()
    {
        return !empty($this->post->post_content) ? $this->post->post_content : '';
    }
}
