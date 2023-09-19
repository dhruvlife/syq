<?php
$banner_1x = forminator_plugin_url() . 'assets/images/promote-free-plan.png';
$banner_2x = forminator_plugin_url() . 'assets/images/promote-free-plan@2x.png';
?>

<div class="sui-modal sui-modal-lg">

	<div
		role="dialog"
		id="forminator-promote-popup"
		class="sui-modal-content"
		aria-live="polite"
		aria-modal="true"
		aria-labelledby="forminator-promote-popup__title"
	>

		<div class="sui-box forminator-promote-modal">

			<div class="sui-box-header sui-flatten sui-content-center">

				<figure class="sui-box-banner" aria-hidden="true" style="margin-bottom: 30px;">
					<img
						src="<?php echo esc_url( $banner_1x ); ?>"
						srcset="<?php echo esc_url( $banner_1x ); ?> 1x, <?php echo esc_url( $banner_2x ); ?> 2x"
						alt=""
					/>
				</figure>

				<button class="sui-button-icon sui-button-white sui-button-float--right" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog.', 'forminator' ); ?></span>
				</button>

				<h3 class="sui-box-title sui-lg" id="forminator-promote-popup__title" style="overflow: initial; white-space: initial; text-overflow: initial;"><?php esc_html_e( 'Claim your free WPMU DEV gift', 'forminator' ); ?></h3>

				<p class="sui-description"><?php esc_html_e( 'Over 50K web developers use WPMU DEV for fast and convenient site management. Here’s what you get completely free:', 'forminator' ); ?></p>

			</div>

			<div class="sui-box-body" style="padding: 20px 60px 0">

				<ul>

					<li>
						<span class="sui-icon-check sui-md" aria-hidden="true" style="padding-right: 10px;"></span>
						<strong><?php esc_html_e( 'The Hub - effortlessly manage unlimited sites from one dashboard', 'forminator' ); ?></strong>
						<hr style="margin: 10px 0">
					</li>

					<li>
						<span class="sui-icon-check sui-md" aria-hidden="true" style="padding-right: 10px;"></span>
						<strong><?php esc_html_e( 'Uptime monitor - instant downtime alerts and helpful site analytics', 'forminator' ); ?></strong>
						<hr style="margin: 10px 0">
					</li>

					<li>
						<span class="sui-icon-check sui-md" aria-hidden="true" style="padding-right: 10px;"></span>
						<strong><?php esc_html_e( 'White label reports - custom website health reports for clients', 'forminator' ); ?></strong>
						<hr style="margin: 10px 0">
					</li>

					<li>
						<span class="sui-icon-check sui-md" aria-hidden="true" style="padding-right: 10px;"></span>
						<strong><?php esc_html_e( 'Client billing - a full payment solution for your business', 'forminator' ); ?></strong>
						<hr style="margin: 10px 0">
					</li>

					<li>
						<span class="sui-icon-check sui-md" aria-hidden="true" style="padding-right: 10px;"></span>
						<strong><?php esc_html_e( 'Auto updates - schedule safe updates for all your plugins and themes', 'forminator' ); ?></strong>
						<hr style="margin: 10px 0">
					</li>

					<li>
						<span class="sui-icon-check sui-md" aria-hidden="true" style="padding-right: 10px;"></span>
						<strong><?php esc_html_e( 'Secure site backups - including 1GB free WPMU DEV storage', 'forminator' ); ?></strong>
					</li>

				</ul>

			</div>

			<div class="sui-box-body sui-content-center" style="padding-top: 0">
				<strong><?php esc_html_e( 'Plus many more membership perks and benefits!', 'forminator' ); ?></strong>
			</div>

			<div class="sui-box-footer sui-flatten sui-content-center" style="flex-direction: column; padding-bottom: 50px;">

				<a href="https://wpmudev.com/register/?free_hub&utm_source=forminator&utm_medium=plugin&utm_campaign=forminator_wp_admin_free_plan_2" target="_blank" class="sui-button sui-button-blue" style="margin-bottom: 10px;">
					<?php esc_html_e( 'Try the free plan', 'forminator' ); ?>
				</a>

				<div><p class="sui-description"><?php esc_html_e( 'No credit card required.', 'forminator' ); ?></p></div>
			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
	// Find out more.
	jQuery( '#forminator-promote-popup-open' ).on( 'click', function( e ) {
		SUI.openModal(
			'forminator-promote-popup',
			'wpbody-content'
		);
	});

	// Remind me later.
	jQuery( '#forminator-promote-remind-later' ).on( 'click', function( e ) {
		e.preventDefault();

		var ajaxUrl = '<?php echo esc_url( forminator_ajax_url() ); ?>';
		var $notice = jQuery( '[data-notice-slug="forminator_promote_free_plan"]' );

		jQuery.post(
			ajaxUrl,
			{
				action: 'forminator_promote_remind_later',
				_ajax_nonce: jQuery( this ).data('nonce')
			}
		).always( function() {
			$notice.hide();
		});
	});
</script>
