<?php
/**
 * Loop -  Timeline
 *
 * This template can be overridden by copying it to mytheme/addons-for-elementor/addons/timeline/loop-timeline.php
 *
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$dir = is_rtl() ? ' dir="rtl"' : '';

$data_attr = '';

if ($settings['timeline_type'] == 'horizontal') {

    $carousel_settings = [
        'slider_id' => $settings['slider_id'],
        'arrows' => ('yes' === $settings['arrows']),
        'dots' => ('yes' === $settings['dots']),
        'autoplay' => ('yes' === $settings['autoplay']),
        'autoplay_speed' => absint($settings['autoplay_speed']),
        'animation_speed' => absint($settings['animation_speed']),
        'pause_on_hover' => ('yes' === $settings['pause_on_hover']),
    ];

    $responsive_settings = [
        'display_columns' => $settings['display_columns'],
        'scroll_columns' => $settings['scroll_columns'],
        'gutter' => $settings['gutter'],
        'tablet_width' => $settings['tablet_width'],
        'tablet_display_columns' => $settings['tablet_display_columns'],
        'tablet_scroll_columns' => $settings['tablet_scroll_columns'],
        'tablet_gutter' => $settings['tablet_gutter'],
        'mobile_width' => $settings['mobile_width'],
        'mobile_display_columns' => $settings['mobile_display_columns'],
        'mobile_scroll_columns' => $settings['mobile_scroll_columns'],
        'mobile_gutter' => $settings['mobile_gutter'],

    ];

    $carousel_settings = array_merge($carousel_settings, $responsive_settings);

    $data_attr = ' data-settings= ' . wp_json_encode($carousel_settings);

}

?>

<div<?php echo $data_attr; ?><?php echo $dir; ?>
        id="lae-<?php echo $settings['timeline_type']; ?>-timeline-<?php echo $settings['slider_id']; ?>"
        class="lae-<?php echo $settings['timeline_type']; ?>-timeline lae-container <?php echo $settings['timeline_class']; ?>">

    <?php foreach ($settings['timeline_items'] as $item): ?>

        <?php $args['item'] = $item; ?>

        <?php lae_get_template_part("addons/timeline/content", $args); ?>

    <?php endforeach; ?>

</div>