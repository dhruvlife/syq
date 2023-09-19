<?php
if ( empty( $args['reports'] ) ) {
	return;
}
$reports     = $args['reports'];
$report_data = Forminator_Admin_Report_Page::get_instance()->forminator_report_array( $reports, $args['form_id'] );
?>
<div class="sui-tab-content">
    <?php
    $chart_data = array(
		'form_id'         => $args['form_id'],
		'last_entry_time' => $reports['last_entry_time'],
		'start_date'      => $reports['start_date'],
		'end_date'        => $reports['end_date'],
		'reports'         => $report_data,
	);
    $this->template( 'common/reports/chart-content', $chart_data );
    ?>
    <div class="sui-row forminator-reports-box">
        <div class="sui-col-md-6">
			<?php
			// Views box
			Forminator_Admin_Addons_page::get_instance()->render_template(
				'admin/views/common/reports/single-report',
				array(
					'title'       => esc_html__( 'Views', 'forminator' ),
					'description' => esc_html__( 'Views for the selected period.', 'forminator' ),
					'data_class'  => 'views',
					'icon'        => 'sui-icon-eye',
					'attrs'       => $report_data['views'],
				)
			);

			// Conversion rate box
			Forminator_Admin_Addons_page::get_instance()->render_template(
				'admin/views/common/reports/single-report',
				array(
					'title'       => esc_html__( 'Conversion Rate', 'forminator' ),
					'description' => esc_html__( 'Conversion rate for the selected period.', 'forminator' ),
					'icon'        => 'sui-icon forminator-icon-conversion',
					'data_class'  => 'conversion',
					'attrs'       => $report_data['conversion'],
				)
			);

			// Payment box
			if ( 'forminator_forms' === $args['form_type'] ) {
				Forminator_Admin_Addons_page::get_instance()->render_template(
					'admin/views/common/reports/single-report',
					array(
						'title'            => esc_html__( 'Payments', 'forminator' ),
						'description'      => esc_html__( 'Payments collected over the selected period.', 'forminator' ),
						'icon'             => 'sui-icon forminator-icon-payment',
						'data_class'       => 'payment',
						'attrs'            => $report_data['payment'],
						'has_payment'      => Forminator_Admin_Report_Page::has_payments( $args['form_id'] ),
						'has_live_payment' => Forminator_Admin_Report_Page::has_live_payments( $args['form_id'] ),
					)
				);
			}
			// Submission box
			if ( isset( $report_data['leads'] ) && ! empty( $report_data['leads'] ) ) {
				Forminator_Admin_Addons_page::get_instance()->render_template(
					'admin/views/common/reports/single-report',
					array(
						'title'       => esc_html__( 'Leads', 'forminator' ),
						'description' => esc_html__( 'Leads generated within the selected period.', 'forminator' ),
						'icon'        => 'sui-icon-clipboard-notes',
						'data_class'  => 'leads',
						'attrs'       => $report_data['leads'],
					)
				);
			}
            ?>
        </div>
        <div class="sui-col-md-6">
			<?php
			// Submission box
			Forminator_Admin_Addons_page::get_instance()->render_template(
				'admin/views/common/reports/single-report',
				array(
					'title'       => esc_html__( 'Submissions', 'forminator' ),
					'description' => esc_html__( 'Submissions for the selected period.', 'forminator' ),
					'icon'        => 'sui-icon-clipboard-notes',
					'data_class'  => 'entries',
					'title_text'  => esc_html__( 'View submissions', 'forminator' ),
					'title_link'  => esc_url( admin_url( 'admin.php?page=forminator-entries&form_type=' . forminator_get_prefix( $args['form_type'] ) . '&form_id=' . $args['form_id'] ) ),
					'attrs'       => $report_data['entries'],
				)
			);

			// Integration box
			Forminator_Admin_Addons_page::get_instance()->render_template(
				'admin/views/common/reports/integration-report',
				array(
					'form_id'      => $args['form_id'],
					'integrations' => ! empty( $report_data['integration'] ) ? $report_data['integration'] : array(),
                    'app_link'     => Forminator_Admin_Report_Page::get_instance()->get_app_link_module_id( $args['form_id'], $args['form_type'] ),
				)
			);
			?>
        </div>
    </div>
</div>
