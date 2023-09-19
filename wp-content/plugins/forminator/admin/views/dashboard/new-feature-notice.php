<?php
$user      = wp_get_current_user();
$banner_1x = forminator_plugin_url() . 'assets/images/geolocation-header.png';
$banner_2x = forminator_plugin_url() . 'assets/images/geolocation-header@2x.png';
?>

<div class="sui-modal sui-modal-md">

	<div
		role="dialog"
		id="forminator-new-feature"
		class="sui-modal-content"
		aria-live="polite"
		aria-modal="true"
		aria-labelledby="forminator-new-feature__title"
	>

		<div class="sui-box forminator-feature-modal" data-prop="forminator_dismiss_feature_1260" data-nonce="<?php echo esc_attr( wp_create_nonce( 'forminator_dismiss_notification' ) ); ?>">

			<div class="sui-box-header sui-flatten sui-content-center">

				<figure class="sui-box-banner" aria-hidden="true">
					<img
						src="<?php echo esc_url( $banner_1x ); ?>"
						srcset="<?php echo esc_url( $banner_1x ); ?> 1x, <?php echo esc_url( $banner_2x ); ?> 2x"
						alt=""
					/>
				</figure>

				<button class="sui-button-icon sui-button-white sui-button-float--right forminator-dismiss-new-feature" data-modal-close>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog.', 'forminator' ); ?></span>
				</button>

				<h3 class="sui-box-title sui-lg" style="overflow: initial; white-space: initial; text-overflow: initial;"><?php esc_html_e( 'New: Forminator Geolocation Add-on', 'forminator' ); ?>
                    <?php if ( ! FORMINATOR_PRO ) { ?>
                        <span class="sui-tag sui-tag-pro">
                            <?php esc_html_e( 'PRO', 'forminator' ); ?>
                        </span>
                    <?php } ?>
                </h3>

				<p class="sui-description">
					<?php
					printf(
					/* Translators: 1. User name 2. Opening <strong> tag, 2. closing <strong> tag, 3. Opening <a> tag with forminator setting data section url, 2. closing <a> tag  */
						esc_html__( 'Hey %s! We\'re excited to introduce the Forminator Geolocation Add-on, a powerful tool that lets you effortlessly gather geolocation data from your form submissions. Plus, with the integration of Google Place API, you can provide address auto-completion for a smoother user experience.', 'forminator' ),
						esc_html( ucfirst( $user->display_name ) )
					);
					?>
				</p>
				<div class="sui-modal-list">
					<h3><?php esc_html_e( 'Geolocation Add-on Features:', 'forminator' ); ?></h3>
					<ol>
						<li><span class="sui-icon-check sui-sm" aria-hidden="true"></span>
							<span><?php esc_html_e( 'Collect users’ geolocation on form submissions.', 'forminator' ); ?></span>
                        </li>
						<li><span class="sui-icon-check sui-sm" aria-hidden="true"></span>
							<span><?php esc_html_e( 'Let users enter accurate addresses with Google Map address auto-suggestion and auto-completion.', 'forminator' ); ?></span>
                        </li>
						<li><span class="sui-icon-check sui-sm" aria-hidden="true"></span>
							<span><?php esc_html_e( 'Display a Google Map on your forms to allow easy address selection.', 'forminator' ); ?></span>
                        </li>
					</ol>
				</div>
			</div>

			<div class="sui-box-footer sui-flatten sui-content-center">

				<button class="sui-button sui-button-blue forminator-dismiss-new-feature" data-modal-close data-link="<?php echo esc_url( menu_page_url( 'forminator-addons', false ) ); ?>">
					<?php esc_html_e( 'Get the add-on', 'forminator' ); ?>
                </button>

			</div>

		</div>

	</div>

</div>

<script type="text/javascript">
	jQuery( '#forminator-new-feature .forminator-dismiss-new-feature' ).on( 'click', function( e ) {
		e.preventDefault();

		var $self   = jQuery(this);
		var $notice = jQuery( e.currentTarget ).closest( '.forminator-feature-modal' );
		var ajaxUrl = '<?php echo esc_url( forminator_ajax_url() ); ?>';

		jQuery.post(
			ajaxUrl,
			{
				action: 'forminator_dismiss_notification',
				prop: $notice.data('prop'),
				_ajax_nonce: $notice.data('nonce')
			}
		).always( function() {
			$notice.hide();
			var link = $self.data('link');
			if ( link ) {
				location.href = link;
			}
		});
	});
</script>
