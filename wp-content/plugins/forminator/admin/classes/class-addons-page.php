<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Addons_page
 *
 * @since 1.15
 */
class Forminator_Admin_Addons_page {

	/**
	 * Plugin instance
	 *
	 * @since  1.11
	 * @access private
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Geolocation Project ID
	 */
	const GEOLOCATION_PID = 4276231;

	/**
	 * Return the plugin instance
	 *
	 * @return Forminator_Admin_Addons_page|null
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Addons action ajax
	 *
	 * @param $action
	 */
	public function addons_action_ajax( $action ) {
		$pid        = intval( Forminator_Core::sanitize_text_field( 'pid' ) );
		$is_network = 1 === intval( Forminator_Core::sanitize_text_field( 'is_network' ) ) && is_super_admin();

		switch ( $action ) {
			case 'addons-install':
				if ( $pid ) {
					if ( WPMUDEV_Dashboard::$upgrader->user_can_install( $pid ) ) {
						$installed = WPMUDEV_Dashboard::$upgrader->is_project_installed( $pid );
						if ( ! $installed ) {
							$success = WPMUDEV_Dashboard::$upgrader->install( $pid );
							if ( $success ) {
								$html_addons = $this->addons_html( $pid );
								wp_send_json_success(
									array(
										'message' => /* translators: %s: Add-on name */ sprintf( esc_html__( '%s add-on was successfully installed', 'forminator' ), $this->get_addon_value( $pid, 'name' ) ),
										'html'    => $html_addons,
									)
								);
							}
						}
					}
				}
				$err = WPMUDEV_Dashboard::$upgrader->get_error();
				wp_send_json_error(
					array(
						'error' => $err,
					)
				);
				break;
			case 'addons-activate':
				if ( $pid ) {
					$local = WPMUDEV_Dashboard::$site->get_cached_projects( $pid );
					if ( empty( $local ) ) {
						$errors['error'] = array(
							'message' => esc_html__( 'Not installed', 'forminator' ),
						);
						wp_send_json_error( $errors );
					}

					$result = activate_plugin( $local['filename'], '', $is_network );
					if ( is_wp_error( $result ) ) {
						$errors['error'] = array(
							'file'    => $pid,
							'code'    => $result->get_error_code(),
							'message' => $result->get_error_message(),
						);
						wp_send_json_error( $errors );
					} else {
						WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
						$html_addons = $this->addons_html( $pid );
						wp_send_json_success(
							array(
								'message' => /* translators: %s: Add-on name */ sprintf( esc_html__( '%s add-on was successfully activated', 'forminator' ), $this->get_addon_value( $pid, 'name' ) ),
								'html'    => $html_addons,
							)
						);
					}
				}
				break;
			case 'addons-deactivate':
				if ( $pid ) {
					$local = WPMUDEV_Dashboard::$site->get_cached_projects( $pid );
					if ( empty( $local ) ) {
						$errors['error'] = array(
							'message' => esc_html__( 'Not installed', 'forminator' ),
						);
						wp_send_json_error( $errors );
					}

					// Check that it's a valid plugin.
					$valid = validate_plugin( $local['filename'] );
					if ( is_wp_error( $valid ) ) {
						$errors['error'] = array(
							'file'    => $pid,
							'code'    => $valid->get_error_code(),
							'message' => $valid->get_error_message(),
						);
						wp_send_json_error( $errors );
					}

					deactivate_plugins( $local['filename'], false, $is_network );
					// there is no return so we always call it a success.
					WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
					$html_addons = $this->addons_html( $pid );
					wp_send_json_success(
						array(
							'message' => /* translators: %s: Add-on name */ sprintf( esc_html__( '%s add-on was successfully deactivated', 'forminator' ), $this->get_addon_value( $pid, 'name' ) ),
							'html'    => $html_addons,
						)
					);
				}
				break;
			case 'addons-delete':
				if ( $pid ) {
					if ( WPMUDEV_Dashboard::$upgrader->delete_plugin( $pid ) ) {
						$html_addons = $this->addons_html( $pid );
						wp_send_json_success(
							array(
								'message' => /* translators: %s: Add-on name */ sprintf( esc_html__( '%s add-on was successfully deleted', 'forminator' ), $this->get_addon_value( $pid, 'name' ) ),
								'html'    => $html_addons,
							)
						);
					} else {
						$err = WPMUDEV_Dashboard::$upgrader->get_error();
						wp_send_json_error( $err );
					}
				}
				break;
			case 'addons-update':
				if ( $pid ) {
					$success = WPMUDEV_Dashboard::$upgrader->upgrade( $pid );

					if ( ! $success ) {
						$error           = WPMUDEV_Dashboard::$upgrader->get_error();
						$errors['error'] = array(
							'message' => $error['message'],
						);
						wp_send_json_error( $errors );
					}

					$html_addons = $this->addons_html( $pid );
					wp_send_json_success(
						array(
							'message' => /* translators: %s: Add-on name */ sprintf( esc_html__( '%s add-on was successfully updated', 'forminator' ), $this->get_addon_value( $pid, 'name' ) ),
							'html'    => $html_addons,
						)
					);
				}
				break;
			default:
				wp_send_json_error(
					array(
						'message' => sprintf(
						/* translators: %s: Action */
							esc_html__( 'Unknown action: %s', 'forminator' ),
							esc_html( $action )
						),
					)
				);
				break;
		}
	}

	/**
	 * Render addons content
	 *
	 * @param $name
	 * @param $pid
	 * @param $addons
	 */
	public function addons_render( $name, $pid, $addons = array() ) {

		$file_name = $name . '.php';

		$file_path = forminator_plugin_dir() . 'admin/views/addons/' . $file_name;

		$path = false;
		if ( file_exists( $file_path ) ) {
			$path = $file_path;
		}

		if ( $path ) {

			if ( empty( $addons ) ) {
				$addons = $this->get_addons( $pid );
			}

			/**
			 * Output some content before the template is loaded, or modify the
			 * variables passed to the file.
			 *
			 * @var  array $data The
			 */
			$new_data = apply_filters( 'forminator_before-' . $name, $addons );
			if ( isset( $new_data ) && is_array( $new_data ) ) {
				$addons = $new_data;
			}

			require $path;

			/**
			 * Output code or do stuff after the template was loaded.
			 */
			do_action( 'forminator_after-' . $name );
		} else {
			printf(
				'<div class="error"><p>%s</p></div>',
				sprintf(
				/* translators: %s: name of the file */
					esc_html__( 'Error: The file %s does not exist. Please re-install the plugin.', 'forminator' ),
					'"' . esc_html( $name ) . '"'
				)
			);
		}
	}

	/**
	 * Get addon
	 *
	 * @param $pid
	 *
	 * @return array|false|object
	 */
	public function get_addons( $pid ) {
		$addon = array();
		if ( $pid ) {
			$dash  = WPMUDEV_Dashboard::instance();
			$addon = $dash::$site->get_project_info( $pid );
		}

		return $addon;
	}

	/**
	 * Get addon value
	 *
	 * @param $pid
	 * @param $key
	 *
	 * @return string
	 */
	public function get_addon_value( $pid, $key ) {
		$value = '';
		$addon = $this->get_addons( $pid );
		if ( ! empty( $addon ) ) {
			$value = isset( $addon->{$key} ) ? $addon->{$key} : '';
		}

		return $value;
	}

	/**
	 * Get addon slug based on PID.
	 *
	 * @param $pid
	 *
	 * @return string
	 */
	public static function get_addon_slug( $pid ) {
		switch ( $pid ) {
			case 3953609:
				$addon_slug = 'stripe';
				break;
			case 4262971:
				$addon_slug = 'pdf';
				break;
			case self::GEOLOCATION_PID:
				$addon_slug = 'geolocation';
				break;
			default:
				$addon_slug = '';
				break;
		}

		return $addon_slug;
	}

	/**
	 * Get addons html
	 *
	 * @param $pid
	 *
	 * @return string
	 */
	public function addons_html( $pid ) {
		ob_start();
		$this->addons_render( 'addons-list', $pid );

		return ob_get_clean();
	}

	/**
	 * Renders a view file with static call.
	 *
	 * @since 1.0
	 * @since 4.2.0 Moved from Opt_In to this class.
	 *
	 * @param string     $file Path to the view file.
	 * @param array      $params Array whose keys will be variable names when within the view file.
	 * @param bool|false $return Whether to echo or return the contents.
	 * @return string
	 */
	public function render_template( $file, $params = array(), $return = false ) {

		// Assign $file to a variable which is unlikely to be used by users of the method.
		extract( $params, EXTR_OVERWRITE ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		if ( $return ) {
			ob_start();
		}

		$file_name = $file . '.php';

		$file_path = forminator_plugin_dir() . $file_name;
		if ( file_exists( $file_path ) ) {
			include $file_path;
		}

		if ( $return ) {
			return ob_get_clean();
		}

		if ( ! empty( $params ) ) {
			foreach ( $params as $param ) {
				unset( $param );
			}
		}
	}

	/**
	 * Get addon by id
	 *
	 * @param $pid
	 *
	 * @return false|object|stdClass
	 */
	public static function forminator_addon_by_pid( $pid ) {
		$res = array();
		if ( class_exists( 'WPMUDEV_Dashboard' ) ) {
			$res = WPMUDEV_Dashboard::$site->get_project_info( $pid, true );
		} else {
			$addons = self::forminator_get_static_addons();
			foreach ( $addons as $addon ) {
				if ( $pid === $addon->pid ) {
					$res = $addon;
				}
			}
		}

		return $res;
	}

	/**
	 * Get static addons
	 *
	 * @return stdClass[]
	 */
	public static function forminator_get_static_addons() {
		// Stripe Addon
		$stripe_addon                    = new stdClass();
		$stripe_addon->pid               = 3953609;
		$stripe_addon->name              = esc_html__( 'Forminator Stripe Subscriptions Add-on', 'forminator' );
		$stripe_addon->info              = esc_html__( 'The Stripe subscription add-on lets you collect recurring/subscription payments with Forminator Pro on your WordPress sites.', 'forminator' );
		$stripe_addon->version_latest    = '1.0';
		$stripe_addon->version_installed = '1.0';
		$stripe_addon->is_network_admin  = is_network_admin();
		$stripe_addon->is_hidden         = false;
		$stripe_addon->features          = array(
			esc_html__( 'Create and manage one-time and recurring Stripe payments in Forminator Pro.', 'forminator' ),
			esc_html__( 'Set up products in Forminator within minutes.', 'forminator' ),
			esc_html__( 'Offer users a trial period for your product before they start paying.', 'forminator' ),
			esc_html__( 'Use conditional logic to process payments based on form input field values.', 'forminator' ),
		);
		$stripe_addon->url               = (object) array(
			'thumbnail' => esc_url( forminator_plugin_url() . 'assets/images/forminator-stripe-logo.png' ),
		);
		$stripe_addon->changelog         = array(
			array(
				'time'    => '1628782583',
				'version' => '1.0',
				'log'     => '<p>- First public release</p>',
			),
		);
		$stripe_addon->pro_url = 'https://wpmudev.com/project/forminator-pro/?utm_source=forminator&utm_medium=plugin&utm_campaign=forminator_stripe-addon';

		// PDF Addon
		$pdf_addon                    = new stdClass();
		$pdf_addon->pid               = 4262971;
		$pdf_addon->name              = esc_html__( 'PDF Generator Add-on', 'forminator' );
		$pdf_addon->info              = esc_html__( 'Generate and send PDF files (e.g. forms entries, receipts, invoices, quotations) to users after form submission.', 'forminator' );
		$pdf_addon->version_latest    = '1.0';
		$pdf_addon->version_installed = '1.0';
		$pdf_addon->is_network_admin  = is_network_admin();
		$pdf_addon->is_hidden         = false;
		$pdf_addon->features          = array(
			esc_html__( 'No limit on the number of PDFs you can generate for your forms.', 'forminator' ),
			esc_html__( 'Generate PDF files in seconds with our easy-to-use pre-designed templates.', 'forminator' ),
			esc_html__( 'Send customized email notifications to admins and visitors with PDF attachments.', 'forminator' ),
			esc_html__( 'Download the PDFs of the form submissions on the Submissions page.', 'forminator' ),
			sprintf(
				'%s <span class="sui-tag sui-tag-blue sui-tag-sm sui-tag-uppercase">%s</span>',
				esc_html__( 'Generate payment receipts, invoices, and quotations.', 'forminator' ),
				esc_html__( 'Coming soon', 'forminator' )
			)
		);
		$pdf_addon->url               = (object) array(
			'thumbnail' => esc_url( forminator_plugin_url() . 'assets/images/pdf-logo@2x.png' ),
		);
		$pdf_addon->changelog         = array(
			array(
				'time'    => '1691653012',
				'version' => '1.0',
				'log'     => '<p>- First public release</p>',
			),
		);
		$pdf_addon->pro_url = 'https://wpmudev.com/project/forminator-pro/?utm_source=forminator&utm_medium=plugin&utm_campaign=forminator_pdf-addon';

		// Geolocation Addon.
		$geo_addon                    = new stdClass();
		$geo_addon->pid               = self::GEOLOCATION_PID;
		$geo_addon->name              = esc_html__( 'Geolocation Add-on', 'forminator' );
		$geo_addon->info              = esc_html__( 'Collect your form submitter’s location information, and provide address auto-completion using Google Maps API.', 'forminator' );
		$geo_addon->version_latest    = '1.0';
		$geo_addon->version_installed = '1.0';
		$geo_addon->is_network_admin  = is_network_admin();
		$geo_addon->is_hidden         = false;
		$geo_addon->features          = array(
			esc_html__( 'Collect and store your users\' geolocation information.', 'forminator' ),
			esc_html__( 'Add address auto-completion to your forms\' address field(s).', 'forminator' ),
			esc_html__( 'See your users\' geolocation on Google Maps.', 'forminator' ),
		);
		$geo_addon->url               = (object) array(
			'thumbnail' => esc_url( forminator_plugin_url() . 'assets/images/geolocation-logo@2x.png' ),
		);
		$geo_addon->changelog         = array(
			array(
				'time'    => '1688169600',
				'version' => '1.0',
				'log'     => '<p>- First public release</p>',
			),
		);

		$geo_addon->pro_url = 'https://wpmudev.com/project/forminator-pro/?utm_source=forminator&utm_medium=plugin&utm_campaign=forminator_geolocation-addon';

		return array(
			$geo_addon,
			$stripe_addon,
			$pdf_addon,
		);
	}

	/**
	 * Upsell modal info.
	 * Used in Addons page and upsell modal in React pages.
	 *
	 * @param string $addon_slug Addon slug which should not be included in description.
	 * @return stdClass[]
	 */
	public static function get_upsell_modal_info( $addon_slug = '' ) {
		// Generic Info
		$upsell_info                      = array();
		$upsell_info['button_text']       = esc_html__( 'Find Out more', 'forminator' );
		$upsell_info['after_button_text'] = esc_html__( 'Already a member?', 'forminator' );
		$upsell_info['login_text']        = esc_html__( 'Connect site', 'forminator' );
		$upsell_info['features_title']    = esc_html__( 'Get the following Forminator Pro features', 'forminator' );
		$upsell_info['features']          = array(
			'stripe'      => array(
				'title' => esc_html__( 'Subscription and recurring payments', 'forminator' ),
				'desc'  => esc_html__( 'Accept One-time or recurring payments directly on your WordPress site.', 'forminator' ),
			),
			array(
				'title' => esc_html__( 'E-Signature integration', 'forminator' ),
				'desc'  => esc_html__( 'Let users electronically and directly sign your form with our E-signature fields.', 'forminator' ),
			),
			'geolocation' => array(
				'title' => esc_html__( 'Geolocation Add-on', 'forminator' ),
				'desc'  => esc_html__( 'Get address auto-completion and see locations of your users with Geolocation.', 'forminator' ),
			),
			'pdf'         => array(
				'title' => esc_html__( 'PDF Generator Add-on', 'forminator' ),
				'desc'  => esc_html__( 'Generate and send PDF files of form entries to users after form submission.', 'forminator' ),
			),
			array(
				'title' => esc_html__( '24/7 live WordPress support', 'forminator' ),
				'desc'  => esc_html__( 'Get help with any WordPress issue from the best support team in the business.', 'forminator' ),
			),
		);

		if ( $addon_slug ) {
			unset( $upsell_info['features'][ $addon_slug ] );
		}

		// Stripe Addon
		$upsell_info['stripe_title']        = esc_html__( 'Activate Stripe Subscription Add-on', 'forminator' );
		$upsell_info['stripe_desc']         = esc_html__( 'With Forminator Stripe Subscription Add-on you can collect recurring/subscription payments with Forminator Pro on your WordPress sites.', 'forminator' );
		$upsell_info['stripe_header_image'] = esc_url( forminator_plugin_url() . 'assets/images/stripe-header.png' );

		// PDF Addon
		$upsell_info['pdf_title']        = esc_html__( 'Activate PDF Generator Add-on', 'forminator' );
		$upsell_info['pdf_desc']         = esc_html__( 'With Forminator PDF Generator Add-on, you can easily generate and send PDF files (e.g., form entries, receipts, invoices, quotations) to users after form submission.', 'forminator' );
		$upsell_info['pdf_header_image'] = esc_url( forminator_plugin_url() . 'assets/images/pdf-header.png' );

		// Geolocation Addon.
		$upsell_info['geolocation_title'] = esc_html__( 'Activate Geolocation Add-on', 'forminator' );
		$upsell_info['geolocation_desc']  = esc_html__( 'With Geolocation Add-on, you can collect your form submitter’s Geolocation information, and provide address auto-completion using Google Place API.', 'forminator' );

		$upsell_info['geolocation_header_image'] = esc_url( forminator_plugin_url() . 'assets/images/geolocation-logo@2x.png' );

		return $upsell_info;
	}

}
