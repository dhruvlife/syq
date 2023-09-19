<?php
$module_slug        = 'poll';
$title              = esc_html__( 'Polls', 'forminator' );
$description        = esc_html__( 'Create interactive polls to collect users\' opinions, with lots of dynamic options and settings.', 'forminator' );
$icon               = 'sui-icon-graph-bar';
$preview_title      = esc_html__( 'Preview Poll', 'forminator' );
$delete_title       = esc_html__( 'Delete Poll', 'forminator' );
$delete_description = esc_html__( 'Are you sure you wish to permanently delete this poll?', 'forminator' );
$view_all           = esc_html__( 'View all polls', 'forminator' );
$total              = forminator_polls_total();

require forminator_plugin_dir() . 'admin/views/common/dashboard/widget.php';
