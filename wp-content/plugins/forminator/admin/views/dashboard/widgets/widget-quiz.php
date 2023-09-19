<?php
$module_slug        = 'quiz';
$title              = esc_html__( 'Quizzes', 'forminator' );
$description        = esc_html__( 'Create fun or challenging quizzes for your visitors to take and share on social media.', 'forminator' );
$icon               = 'sui-icon-academy';
$preview_title      = esc_html__( 'Preview Quiz', 'forminator' );
$delete_title       = esc_html__( 'Delete Quiz', 'forminator' );
$delete_description = esc_html__( 'Are you sure you wish to permanently delete this quiz?', 'forminator' );
$view_all           = esc_html__( 'View all quizzes', 'forminator' );
$total              = forminator_quizzes_total();

require forminator_plugin_dir() . 'admin/views/common/dashboard/widget.php';
