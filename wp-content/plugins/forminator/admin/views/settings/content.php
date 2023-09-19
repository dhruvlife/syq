<?php
$section = Forminator_Core::sanitize_text_field( 'section', 'dashboard' );

/**
 * Forminator Settings Sections filter
 *
 * @param array $sections Settings Sections
 */
$sections = apply_filters(
	'forminator_settings_sections',
	array(
		'dashboard'          => __( 'General', 'forminator' ),
		'accessibility'      => __( 'Accessibility', 'forminator' ),
		'appearance-presets' => __( 'Appearance Presets', 'forminator' ),
		'data'               => __( 'Data', 'forminator' ),
		'captcha'            => __( 'CAPTCHA', 'forminator' ),
		'import'             => __( 'Import', 'forminator' ),
		'submissions'        => __( 'Submissions', 'forminator' ),
		'payments'           => __( 'Payments', 'forminator' ),
	)
);
?>
<div class="sui-row-with-sidenav">

	<div class="sui-sidenav">

		<ul class="sui-vertical-tabs sui-sidenav-hide-md">
			<?php
			foreach ( $sections as $section_key => $section_title ) {
				?>
				<li class="sui-vertical-tab <?php echo $section_key === $section ? 'current' : ''; ?>">
					<a href="#" data-nav="<?php echo esc_attr( $section_key ); ?>"><?php echo esc_html( $section_title ); ?></a>
				</li>
				<?php
			}
			?>
		</ul>

		<div class="sui-sidenav-settings">

			<div class="sui-form-field sui-sidenav-hide-lg">

				<label class="sui-label"><?php esc_html_e( 'Navigate', 'forminator' ); ?></label>

				<select id="forminator-sidenav" class="sui-select sui-mobile-nav">
					<?php
					foreach ( $sections as $section_key => $section_title ) {
						?>
							<option value="<?php echo esc_attr( $section_key ); ?>"><?php echo esc_html( $section_title ); ?></option>
						<?php
					}
					?>
				</select>

			</div>

		</div>

	</div>

	<?php $this->template( 'settings/tab-general' ); ?>
	<?php $this->template( 'settings/tab-recaptcha' ); ?>
	<?php $this->template( 'settings/tab-appearance-presets' ); ?>
	<?php $this->template( 'settings/tab-data' ); ?>
	<?php $this->template( 'settings/tab-submissions' ); ?>
	<?php $this->template( 'settings/tab-payments' ); ?>
	<?php $this->template( 'settings/tab-accessibility' ); ?>
	<?php $this->template( 'settings/tab-import' ); ?>

	<?php
		/**
		 * Forminator Settings Content action
		 */
		do_action( 'forminator_settings_content' );
	?>

</div>
