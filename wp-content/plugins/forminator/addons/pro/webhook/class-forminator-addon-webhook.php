<?php

require_once dirname( __FILE__ ) . '/class-forminator-addon-webhook-exception.php';
require_once dirname( __FILE__ ) . '/lib/class-forminator-addon-webhook-wp-api.php';

/**
 * Class Forminator_Addon_Webhook
 * Webhook Addon Main Class
 *
 *
 */
final class Forminator_Addon_Webhook extends Forminator_Addon_Abstract {

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	protected $_slug                   = 'webhook';
	protected $_version                = FORMINATOR_ADDON_WEBHOOK_VERSION;
	protected $_min_forminator_version = '1.1';
	protected $_short_title            = 'Webhook';
	protected $_title                  = 'Webhook';
	protected $_url                    = 'https://wpmudev.com';
	protected $_full_path              = __FILE__;
	protected $_documentation          = 'https://wpmudev.com/docs/wpmu-dev-plugins/forminator/#webhook';

	protected $_form_settings = 'Forminator_Addon_Webhook_Form_Settings';
	protected $_form_hooks    = 'Forminator_Addon_Webhook_Form_Hooks';

	protected $_poll_settings = 'Forminator_Addon_Webhook_Poll_Settings';
	protected $_poll_hooks    = 'Forminator_Addon_Webhook_Poll_Hooks';

	protected $_quiz_settings = 'Forminator_Addon_Webhook_Quiz_Settings';
	protected $_quiz_hooks    = 'Forminator_Addon_Webhook_Quiz_Hooks';
	protected $_position      = 0;

	/**
	 * Forminator_Addon_Webhook constructor.
	 *
	 *
	 */
	public function __construct() {
		// late init to allow translation.
		$this->_description = esc_html__( 'Get awesome by your form.', 'forminator' );
		$this->_promotion = sprintf(
		/* translators: 1: Zapier link 2. Closing a tag 3. Integrately link 4. Tray.io link 5. Make.com link 6. Workato link 7. Additional text */
			esc_html__( 'Connect Forminator with automation tools through webhook. You can use this to send submissions to automation apps like %1$sZapier%2$s, %3$sIntegrately%2$s, %4$sTray.io%2$s, %5$sMake%2$s, %6$sWorkato%2$s, and other automation tools that support webhooks.', 'forminator' ),
			'<a href="https://zapier.com/" target="_blank">',
			'</a>',
			'<a href="https://integrately.com/" target="_blank">',
			'<a href="https://tray.io/" target="_blank">',
			'<a href="https://www.make.com/" target="_blank">',
			'<a href="https://www.workato.com/" target="_blank">'
		);

		$this->_activation_error_message   = esc_html__( 'Sorry but we failed to activate Webhook Integration, don\'t hesitate to contact us', 'forminator' );
		$this->_deactivation_error_message = esc_html__( 'Sorry but we failed to deactivate Webhook Integration, please try again', 'forminator' );

		$this->_update_settings_error_message = esc_html__(
			'Sorry, we failed to update settings, please check your form and try again',
			'forminator'
		);

		$this->_icon      = forminator_addon_webhook_assets_url() . 'icons/webhook.png';
		$this->_icon_x2   = forminator_addon_webhook_assets_url() . 'icons/webhook@2x.png';
		$this->_image     = forminator_addon_webhook_assets_url() . 'img/webhook.png';
		$this->_image_x2  = forminator_addon_webhook_assets_url() . 'img/webhook@2x.png';
		$this->_banner    = forminator_addon_webhook_assets_url() . 'img/banner.png';
		$this->_banner_x2 = forminator_addon_webhook_assets_url() . 'img/banner@2x.png';
	}

	/**
	 * Get Instance
	 *
	 *
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Setting apier Addon
	 *
	 *
	 * @return array
	 */
	public function settings_wizards() {
		return array(
			array(
				'callback'     => array( $this, 'setup_connect' ),
				'is_completed' => array( $this, 'is_connected' ),
			),
		);
	}

	/**
	 * Activate Webhook
	 *
	 *
	 *
	 * @param     $submitted_data
	 * @param int $form_id
	 *
	 * @return array
	 */
	public function setup_connect( $submitted_data, $form_id = 0 ) {
		$settings_values = $this->get_settings_values();

		$template         = forminator_addon_webhook_dir() . 'views/settings/setup-connect.php';
		$template_success = forminator_addon_webhook_dir() . 'views/settings/setup-connect-success.php';

		$template_params = array(
			'is_connected'  => $this->is_connected(),
			'error_message' => '',
		);

		$has_errors   = false;
		$show_success = false;
		$is_submit    = ! empty( $submitted_data );

		foreach ( $template_params as $key => $value ) {
			if ( isset( $submitted_data[ $key ] ) ) {
				$template_params[ $key ] = $submitted_data[ $key ];
			} elseif ( isset( $settings_values[ $key ] ) ) {
				$template_params[ $key ] = $settings_values[ $key ];
			}
		}

		if ( $is_submit ) {
			$connect = isset( $submitted_data['connect'] ) ? $submitted_data['connect'] : '';

			try {
				if ( empty( $connect ) ) {
					throw new Forminator_Addon_Webhook_Exception( esc_html__( 'Please Connect Webhook', 'forminator' ) );
				}

				if ( ! forminator_addon_is_active( $this->_slug ) ) {
					$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
					if ( ! $activated ) {
						throw new Forminator_Addon_Webhook_Exception( Forminator_Addon_Loader::get_instance()->get_last_error_message() );
					}
				}
				// no form_id its on global settings.
				if ( empty( $form_id ) ) {
					$show_success = true;
				}
			} catch ( Forminator_Addon_Webhook_Exception $e ) {
				$template_params['error_message'] = $e->getMessage();
				$has_errors                       = true;
			}
		}

		if ( $show_success ) {
			$template = $template_success;
		}

		$buttons = array();

		if ( $show_success ) {
			$buttons['close'] = array(
				'markup' => self::get_button_markup( esc_html__( 'Close', 'forminator' ), 'sui-button-ghost forminator-addon-close forminator-integration-popup__close' ),
			);
		} else {
			if ( $this->is_connected() ) {
				$buttons['disconnect'] = array(
					'markup' => self::get_button_markup( esc_html__( 'Disconnect', 'forminator' ), 'sui-button-ghost forminator-addon-disconnect forminator-integration-popup__close' ),
				);
			} else {
				$buttons['submit'] = array(
					'markup' => self::get_button_markup( esc_html__( 'Activate', 'forminator' ), 'forminator-addon-connect forminator-integration-popup__close' ),
				);
			}
		}

		return array(
			'html'       => self::get_template( $template, $template_params ),
			'buttons'    => $buttons,
			'redirect'   => false,
			'has_errors' => $has_errors,
		);
	}

	/**
	 * Override on is_connected
	 *
	 *
	 * @since 1.1 Disable auto activate
	 *
	 * @return bool
	 */
	public function is_connected() {
		try {
			// check if its active.
			if ( ! $this->is_active() ) {
				throw new Forminator_Addon_Webhook_Exception( esc_html__( 'Webhook is not active', 'forminator' ) );
			}
			$is_connected = true;
		} catch ( Forminator_Addon_Webhook_Exception $e ) {
			$is_connected = false;
		}

		/**
		 * Filter connected status ofwebhook
		 *
		 * @since 1.1
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters_deprecated( 'forminator_addon_zapier_is_connected', array( $is_connected ), '1.18.0', 'forminator_addon_webhook_is_connected' );
		$is_connected = apply_filters( 'forminator_addon_webhook_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check ifwebhook is connected with current form
	 *
	 * @since 1.0 Webhook Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		try {
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Webhook_Exception( esc_html__( 'Webhook is not connected', 'forminator' ) );
			}

			$form_settings_instance = $this->get_addon_settings( $form_id, 'form' );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Webhook_Form_Settings ) {
				throw new Forminator_Addon_Webhook_Exception( esc_html__( 'Invalid Form Settings of Webhook', 'forminator' ) );
			}

			// Mark as active when there is at least one active connection.
			if ( false === $form_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Webhook_Exception( esc_html__( 'No active Webhook connection found in this form', 'forminator' ) );
			}

			$is_form_connected = true;

		} catch ( Forminator_Addon_Webhook_Exception $e ) {
			$is_form_connected = false;
			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
		}

		/**
		 * Filter connected status ofwebhook with the form
		 *
		 * @since 1.1
		 *
		 * @param bool                                       $is_form_connected
		 * @param int                                        $form_id                Current Form ID.
		 * @param Forminator_Addon_Webhook_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable.
		 *
		 */
		$is_form_connected = apply_filters_deprecated( 'forminator_addon_zapier_is_form_connected', array( $is_form_connected, $form_id, $form_settings_instance ), '1.18.0', 'forminator_addon_webhook_is_form_connected' );
		$is_form_connected = apply_filters( 'forminator_addon_webhook_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;
	}

	/**
	 * Get Webhook API
	 *
	 *
	 *
	 * @param string $endpoint
	 *
	 * @return Forminator_Addon_Webhook_Wp_Api|null
	 * @throws Forminator_Addon_Webhook_Wp_Api_Exception
	 */
	public function get_api( $endpoint ) {
		return Forminator_Addon_Webhook_Wp_Api::get_instance( $endpoint );
	}

	/**
	 * Flag show full log on entries
	 *
	 *
	 * @return bool
	 */
	public static function is_show_full_log() {
		if ( defined( 'FORMINATOR_ADDON_WEBHOOK_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_WEBHOOK_SHOW_FULL_LOG ) {
			return true;
		}

		return false;
	}

	/**
	 * Allow multiple connection on one form
	 *
	 *
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return true;
	}

	/**
	 * Flag for check if and addon connected to a poll(poll settings such as list id completed)
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.6.1
	 *
	 * @param $poll_id
	 *
	 * @return boolean
	 */
	public function is_poll_connected( $poll_id ) {
		try {
			$poll_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Webhook_Exception( 'Webhook is not connected' );
			}

			$poll_settings_instance = $this->get_addon_settings( $poll_id, 'poll' );
			if ( ! $poll_settings_instance instanceof Forminator_Addon_Webhook_Poll_Settings ) {
				throw new Forminator_Addon_Webhook_Exception( 'Webhook Poll Settings of Trello' );
			}

			// Mark as active when there is at least one active connection.
			if ( false === $poll_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Webhook_Exception( 'No active Poll connection found in this poll' );
			}

			$is_poll_connected = true;

		} catch ( Forminator_Addon_Webhook_Exception $e ) {

			$is_poll_connected = false;
		}

		/**
		 * Filter connected status Webhook with the poll
		 *
		 * @since 1.6.1
		 *
		 * @param bool                                       $is_poll_connected
		 * @param int                                        $poll_id                Current Poll ID.
		 * @param Forminator_Addon_Trello_Poll_Settings|null $poll_settings_instance Instance of poll settings, or null when unavailable.
		 *
		 */
		$is_poll_connected = apply_filters_deprecated( 'forminator_addon_zapier_is_poll_connected', array( $is_poll_connected, $poll_id, $poll_settings_instance ), '1.18.0', 'forminator_addon_webhook_is_poll_connected' );
		$is_poll_connected = apply_filters( 'forminator_addon_webhook_is_poll_connected', $is_poll_connected, $poll_id, $poll_settings_instance );

		return $is_poll_connected;
	}

	/**
	 * Allow multiple connection on one poll
	 *
	 * @since 1.6.1
	 * @return bool
	 */
	public function is_allow_multi_on_poll() {
		return true;
	}

	/**
	 * Flag for check if and addon connected to a quiz(quiz settings such as list id completed)
	 *
	 * Please apply necessary WordPress hook on the inheritance class
	 *
	 * @since   1.6.2
	 *
	 * @param $quiz_id
	 *
	 * @return boolean
	 */
	public function is_quiz_connected( $quiz_id ) {
		try {
			$quiz_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Webhook_Exception( 'Webhook is not connected' );
			}

			$quiz_settings_instance = $this->get_addon_settings( $quiz_id, 'quiz' );
			if ( ! $quiz_settings_instance instanceof Forminator_Addon_Webhook_Quiz_Settings ) {
				throw new Forminator_Addon_Webhook_Exception( 'Webhook Quiz Settings of Trello' );
			}

			// Mark as active when there is at least one active connection.
			if ( false === $quiz_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Webhook_Exception( 'No active Webhook connection found in this quiz' );
			}

			$is_quiz_connected = true;

		} catch ( Forminator_Addon_Webhook_Exception $e ) {

			$is_quiz_connected = false;
		}

		/**
		 * Filter connected status Webhook with the quiz
		 *
		 * @since 1.6.2
		 *
		 * @param bool                                       $is_quiz_connected
		 * @param int                                        $quiz_id                Current Quiz ID.
		 * @param Forminator_Addon_Trello_Quiz_Settings|null $quiz_settings_instance Instance of quiz settings, or null when unavailable.
		 *
		 */
		$is_quiz_connected = apply_filters_deprecated( 'forminator_addon_zapier_is_quiz_connected', array( $is_quiz_connected, $quiz_id, $quiz_settings_instance ), '1.18.0', 'forminator_addon_webhook_is_quiz_connected' );
		$is_quiz_connected = apply_filters( 'forminator_addon_webhook_is_quiz_connected', $is_quiz_connected, $quiz_id, $quiz_settings_instance );

		return $is_quiz_connected;
	}

	/**
	 * Allow multiple connection on one quiz
	 *
	 * @since 1.6.2
	 * @return bool
	 */
	public function is_allow_multi_on_quiz() {
		return true;
	}
}
