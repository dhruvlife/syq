<?php
// defaults.
$vars = array(
	'error_message'     => '',
	'name'              => '',
	'name_error'        => '',
	'multi_id'          => '',
	'webhook_url'       => '',
	'webhook_url_error' => '',
);
/** @var array $template_vars */
foreach ( $template_vars as $key => $val ) {
	$vars[ $key ] = $val;
}
?>

<div class="forminator-integration-popup__header">

	<h3 id="forminator-integration-popup__title" class="sui-box-title sui-lg" style="overflow: initial; white-space: normal; text-overflow: initial;">
		<?php esc_html_e( 'Set Up Webhook', 'forminator' ); ?>
	</h3>

	<p id="forminator-integration-popup__description" class="sui-description">
		<?php esc_html_e( 'Give your webhook integration a name and add the webhook URL.', 'forminator' ); ?>
	</p>

	<?php if ( ! empty( $vars['error_message'] ) ) : ?>
		<div
			role="alert"
			class="sui-notice sui-notice-red sui-active"
			style="display: block; text-align: left;"
			aria-live="assertive"
		>

			<div class="sui-notice-content">

				<div class="sui-notice-message">

					<span class="sui-notice-icon sui-icon-info" aria-hidden="true"></span>

					<p><?php echo esc_html( $vars['error_message'] ); ?></p>

				</div>

			</div>

		</div>
	<?php endif; ?>

</div>

<form enctype="multipart/form-data">

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['name_error'] ) ? 'sui-form-field-error' : '' ); ?>">
		<label class="sui-label"><?php esc_html_e( 'Friendly Name', 'forminator' ); ?></label>
		<div class="sui-control-with-icon">
			<input type="text"
				name="name"
				placeholder="<?php esc_attr_e( 'Enter a friendly name E.g. Zapier to Gmail', 'forminator' ); ?>"
				value="<?php echo esc_attr( $vars['name'] ); ?>"
				class="sui-form-control"
			/>
			<i class="sui-icon-web-globe-world" aria-hidden="true"></i>
		</div>
		<?php if ( ! empty( $vars['name_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['name_error'] ); ?></span>
		<?php endif; ?>
	</div>

	<div class="sui-form-field <?php echo esc_attr( ! empty( $vars['webhook_url_error'] ) ? 'sui-form-field-error' : '' ); ?>" style="margin-bottom: 0;">
		<label class="sui-label"><?php esc_html_e( 'Webhook URL', 'forminator' ); ?></label>
		<div class="sui-control-with-icon">
			<input
					type="text"
					name="webhook_url"
					placeholder="<?php esc_attr_e( 'Enter your webhook URL', 'forminator' ); ?>"
					value="<?php echo esc_attr( $vars['webhook_url'] ); ?>"
					class="sui-form-control"/>
			<i class="sui-icon-link" aria-hidden="true"></i>
		</div>
		<?php if ( ! empty( $vars['webhook_url_error'] ) ) : ?>
			<span class="sui-error-message"><?php echo esc_html( $vars['webhook_url_error'] ); ?></span>
		<?php endif; ?>

		<?php if ( forminator_is_show_addons_documentation_link() ) : ?>
			<div class="sui-description">
				<?php
				printf(
					/* translators: 1: article anchor start, 2: article anchor end. */
					esc_html__( 'Check %1$sour documentation%2$s for more information on using webhook URLs for your preferred automation tools.', 'forminator' ),
					'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#webhook" target="_blank">',
					'</a>'
				);
				?>
			</div>
		<?php endif; ?>
	</div>

	<input type="hidden" name="multi_id" value="<?php echo esc_attr( $vars['multi_id'] ); ?>">

</form>
