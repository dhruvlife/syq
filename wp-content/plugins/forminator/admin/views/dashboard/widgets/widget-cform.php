<?php
$module_slug        = 'form';
$title              = esc_html__( 'Forms', 'forminator' );
$description        = esc_html__( 'Create any type of form from one of our pre-made templates, or build your own from scratch.', 'forminator' );
$icon               = 'sui-icon-clipboard-notes';
$preview_title      = esc_html__( 'Preview Custom Form', 'forminator' );
$delete_title       = esc_html__( 'Delete Form', 'forminator' );
$delete_description = esc_html__( 'Are you sure you wish to permanently delete this form?', 'forminator' );
$view_all           = esc_html__( 'View all forms', 'forminator' );
$total              = forminator_cforms_total();

require forminator_plugin_dir() . 'admin/views/common/dashboard/widget.php';
