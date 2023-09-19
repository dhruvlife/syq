<?php

namespace PrestoPlayer\Services;

class VideoPostType
{
    protected $post_type = 'pp_video_block';

    public function register()
    {
        global $wp_version;

        add_action('init', [$this, 'init']);

        if (version_compare($wp_version, '5.8', ">=")) {
            add_filter("allowed_block_types_all", [$this, 'allowedTypes'], 10, 2);
        } else {
            add_filter("allowed_block_types", [$this, 'allowedTypesDeprecated'], 10, 2);
        }

        add_filter('enter_title_here', [$this, 'videoTitle']);

        // post type ui
        add_filter("manage_{$this->post_type}_posts_columns", [$this, 'postTypeColumns'], 1);
        add_action("manage_{$this->post_type}_posts_custom_column", [$this, 'postTypeContent'], 10, 2);

        // filter by tags
        add_action('restrict_manage_posts', [$this, 'tagFilter']);
        add_action('parse_query', [$this, 'tagQuery']);

        // force gutenberg here
        add_action('use_block_editor_for_post', [$this, 'forceGutenberg'], 999, 2);

        // limit media hub posts
        add_filter('pre_get_posts', [$this, 'limitMediaHubPosts']);

        add_action( 'transition_post_status', [$this, 'set_title_on_publish_only'], 10, 3 );
    }

    /**
     * Limit media hub posts by author if cannot edit others posts
     *  
     * @param \WP_Query $query
     * @return \WP_Query
     */
    public function limitMediaHubPosts($query)
    {
        global $pagenow, $typenow;

        if ('edit.php' != $pagenow || !$query->is_admin || 'pp_video_block' !== $typenow) {
            return $query;
        }

        if (!current_user_can('edit_others_posts')) {
            $query->set('author', get_current_user_id());
        }

        return $query;
    }

    /**
     * Force gutenberg in case of classic editor
     */
    public function forceGutenberg($use, $post)
    {
        if ($this->post_type === $post->post_type) {
            return true;
        }

        return $use;
    }

    /**
     * Columns on all posts page
     *
     * @param array $defaults
     * @return array
     */
    public function postTypeColumns($defaults)
    {
        $columns = array_merge($defaults, array(
            'title' => $defaults['title'],
            'shortcode' => __('Shortcode', 'presto-player'),
            'php_function' => __('PHP Function', 'presto-player'),
        ));

        $v = $columns['taxonomy-pp_video_tag'];
        unset($columns['taxonomy-pp_video_tag']);
        $columns['taxonomy-pp_video_tag'] = $v;

        $v = $columns['date'];
        unset($columns['date']);
        $columns['date'] = $v;
        return $columns;
    }

    public function postTypeContent($column_name, $post_ID)
    {
        if ('shortcode' === $column_name) {
            echo '<code>[presto_player id=' . (int) $post_ID . ']</code>';
        }
        if ('php_function' === $column_name) {
            echo '<code>presto_player(' . (int) $post_ID . ')</code>';
        }
        if ('video_tags' === $column_name) {
            $tags = get_the_terms($post_ID, 'pp_video_tag');
            if (is_array($tags)) {
                foreach ($tags as $key => $tag) {
                    $tags[$key] = '<a href="?post_type=pp_video_block&pp_video_tag=' . $tag->term_id . '">' . $tag->name . '</a>';
                }
                echo implode(', ', $tags);
            }
        }
    }

    public function videoTitle($title)
    {
        $screen = get_current_screen();
        if ($this->post_type == $screen->post_type) {
            $title = __('Enter a title...', 'presto-player');
        }
        return $title;
    }

    /**
     * Allowed block types
     *
     * @param array $allowed_block_types
     * @param object $block_editor_content
     * @return void
     */
    public function allowedTypes($allowed_block_types, $block_editor_content)
    {
        if (!empty($block_editor_content->post->post_type)) {
            if ($block_editor_content->post->post_type === $this->post_type) {
                return [
                    'presto-player/reusable',
                    'presto-player/self-hosted',
                    'presto-player/youtube',
                    'presto-player/vimeo',
                    'presto-player/bunny',
                    'presto-player/audio'
                ];
            }
        }

        return $allowed_block_types;
    }

    public function allowedTypesDeprecated($allowed_block_types, $post)
    {
        if ($post->post_type !== $this->post_type) {
            return $allowed_block_types;
        }

        return [
            'presto-player/reusable',
            'presto-player/self-hosted',
            'presto-player/youtube',
            'presto-player/vimeo',
            'presto-player/bunny',
            'presto-player/audio'
        ];
    }

    /**
     * Register post type
     *
     * @return void
     */
    public function init()
    {
        register_taxonomy('pp_video_tag', 'pp_video_block', [
            'labels'                => array(
                'name'                     => _x('Media Tags', 'post type general name'),
                'singular_name'            => _x('Media Tag', 'post type singular name'),
                'search_items'             => _x('Search Media Tags', 'admin menu'),
                'popular_items'            => _x('Popular Media Tags', 'add new on admin bar'),
            ),
            'label' => __('Tag', 'presto-player'),
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
        ]);

        register_post_type(
            'pp_video_block',
            array(
                'labels'                => array(
                    'name'                     => _x('Media Hub', 'post type general name', 'presto-player'),
                    'singular_name'            => _x('Media', 'post type singular name', 'presto-player'),
                    'menu_name'                => _x('Media', 'admin menu', 'presto-player'),
                    'name_admin_bar'           => _x('Video', 'add new on admin bar', 'presto-player'),
                    'add_new'                  => _x('Add New', 'Video', 'presto-player'),
                    'add_new_item'             => __('Add New Video', 'presto-player'),
                    'new_item'                 => __('New Video', 'presto-player'),
                    'edit_item'                => __('Edit Video', 'presto-player'),
                    'view_item'                => __('View Video', 'presto-player'),
                    'all_items'                => __('All Videos', 'presto-player'),
                    'search_items'             => __('Search Media', 'presto-player'),
                    'not_found'                => __('No Videos found.', 'presto-player'),
                    'not_found_in_trash'       => __('No Videos found in Trash.', 'presto-player'),
                    'filter_items_list'        => __('Filter Videos list', 'presto-player'),
                    'items_list_navigation'    => __('Videos list navigation', 'presto-player'),
                    'items_list'               => __('Videos list', 'presto-player'),
                    'item_published'           => __('Video published.', 'presto-player'),
                    'item_published_privately' => __('Video published privately.', 'presto-player'),
                    'item_reverted_to_draft'   => __('Video reverted to draft.', 'presto-player'),
                    'item_scheduled'           => __('Video scheduled.', 'presto-player'),
                    'item_updated'             => __('Video updated.', 'presto-player'),
                ),
                'public'                => false,
                'show_ui'               => true,
                'show_in_menu'          => false,
                'rewrite'               => false,
                'show_in_rest'          => true,
                'rest_base'             => 'presto-videos',
                'rest_controller_class' => 'WP_REST_Blocks_Controller',
                'map_meta_cap'          => true,
                'supports'              => [
                    'title',
                    'editor',
                ],
                'taxonomies' => ['pp_video_tag'],
                'template' => [
                    ['presto-player/reusable-edit']
                ],
                'template_lock' => 'all'
            )
        );
    }

    /**
     * Adds a tag filter dropdown
     *
     * @return void
     */
    public function tagFilter()
    {
        global $typenow;

        $post_type = 'pp_video_block';
        $taxonomy  = 'pp_video_tag';

        if ($typenow !== $post_type) {
            return;
        }

        $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        $info_taxonomy = get_taxonomy($taxonomy);

        wp_dropdown_categories(array(
            'show_option_all' => sprintf(__('Show all %s', 'textdomain'), $info_taxonomy->label),
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'show_count'      => true,
            'hide_empty'      => true,
        ));
    }

    /**
     * Modify admin query for tag
     *
     * @param \WP_Query $query
     * @return void
     */
    public function tagQuery($query)
    {
        global $pagenow;

        $post_type = 'pp_video_block';
        $taxonomy  = 'pp_video_tag';

        $q_vars    = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    /**
     * Set media hub video title when kept empty before publish.
     */
    public function set_title_on_publish_only( $new_status, $old_status, $post ) {
        if ( ( 'publish' === $new_status && 'publish' !== $old_status )
            && 'pp_video_block' === $post->post_type
        ) {
            
            if( empty( $post->post_title ) ) {
                $new_title = "Presto Player #" . $post->ID;
    
                $post_update = array(
                    'ID'         => $post->ID,
                    'post_title' => $new_title
                );
            
                wp_update_post( $post_update );
            }
        }
    }
}
