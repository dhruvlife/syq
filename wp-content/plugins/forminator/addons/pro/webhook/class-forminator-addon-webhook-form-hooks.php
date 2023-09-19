<?php

/**
 * Class Forminator_Addon_Webhook_Form_Hooks
 *
 */
class Forminator_Addon_Webhook_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Autocomplete will resolve addon directly to `Webhook` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @var Forminator_Addon_Webhook
	 */
	protected $addon;

	/**
	 * Form Settings Instance
	 *
	 * @var Forminator_Addon_Webhook_Form_Settings | null
	 */
	protected $form_settings_instance;

	/**
	 * Forminator_Addon_Webhook_Form_Hooks constructor.
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		parent::__construct( $addon, $form_id );
		$this->_submit_form_error_message = esc_html__( 'Webhook failed to process submitted data. Please check your form and try again', 'forminator' );
	}


	/**
	 * Save status of request sent and received for each connected zap(s)
	 *
	 * @since 1.7 Add $form_entry_fields
	 *
	 * @param array $submitted_data
	 * @param array $form_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $form_entry_fields = array() ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filterwebhook submitted form data to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                 $submitted_data
		 * @param int                                   $form_id                current Form ID.
		 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Addon Form Settings instance.
		 */
		$submitted_data = apply_filters_deprecated(
			'forminator_addon_zapier_form_submitted_data',
			array( $submitted_data, $form_id, $form_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_form_submitted_data'
		);
		$submitted_data = apply_filters(
			'forminator_addon_webhook_form_submitted_data',
			$submitted_data,
			$form_id,
			$form_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $form_settings_instance->get_form_settings_values();
		$form_settings        = $form_settings_instance->get_form_settings();

		$data = array();

		/**
		 * Fires before sending data to Webhook URL(s)
		 *
		 * @since 1.1
		 *
		 * @param int                                   $form_id                current Form ID.
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Addon Form Settings instance.
		 */
		do_action_deprecated( 'forminator_addon_zapier_before_post_to_webhook', array( $form_id, $submitted_data, $form_settings_instance ), '1.18.0', 'forminator_addon_webhook_before_post_to_webhook' );
		do_action( 'forminator_addon_webhook_before_post_to_webhook', $form_id, $submitted_data, $form_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data to webhook.
			$data[] = array(
				'name'  => 'status-' . $key,
				'value' => $this->get_status_on_send_data( $key, $submitted_data, $addon_setting_value, $form_settings, $form_entry_fields ),
			);
		}

		$entry_fields = $data;
		/**
		 * Filterwebhook entry fields to be saved to entry model
		 *
		 * @since 1.1
		 *
		 * @param array                                 $entry_fields
		 * @param int                                   $form_id                current Form ID.
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Form Settings instance.
		 */
		$data = apply_filters_deprecated(
			'forminator_addon_zapier_entry_fields',
			array( $entry_fields, $form_id, $submitted_data, $form_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_entry_fields'
		);
		$data = apply_filters(
			'forminator_addon_webhook_entry_fields',
			$data,
			$form_id,
			$submitted_data,
			$form_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on sending data towebhook
	 *
	 * @since 1.7 Add $form_entry_fields arg
	 *
	 * @param       $connection_id
	 * @param       $submitted_data
	 * @param       $connection_settings
	 * @param       $form_settings
	 * @param array $form_entry_fields
	 *
	 * @return array `is_sent` true means its success send data towebhook, false otherwise
	 */
	private function get_status_on_send_data( $connection_id, $submitted_data, $connection_settings, $form_settings, $form_entry_fields ) {
		// initialize as null.
		$webhook_api = null;

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		//check required fields
		try {
			if ( ! isset( $connection_settings['webhook_url'] ) ) {
				throw new Forminator_Addon_Webhook_Exception( esc_html__( 'Webhook URL is not properly set up', 'forminator' ) );
			}

			$endpoint = $connection_settings['webhook_url'];
			/**
			 * Filter Endpoint Webhook URL to send
			 *
			 * @since 1.1
			 *
			 * @param string $endpoint
			 * @param int    $form_id             current Form ID.
			 * @param array  $connection_settings current connection setting, it contains `name` and `webhook_url`.
			 */
			$endpoint = apply_filters_deprecated(
				'forminator_addon_zapier_endpoint',
				array( $endpoint, $form_id, $connection_settings ),
				'1.18.0',
				'forminator_addon_webhook_endpoint'
			);
			$endpoint = apply_filters(
				'forminator_addon_webhook_endpoint',
				$endpoint,
				$form_id,
				$connection_settings
			);

			$webhook_api = $this->addon->get_api( $endpoint );

			$args = $submitted_data;

			$args['form-title'] = $form_settings['formName'];
			$args['entry-time'] = current_time( 'Y-m-d H:i:s' );

			/**
			 * Filter arguments to passed on to Webhook API
			 *
			 * @since 1.1
			 *
			 * @param array                                 $args
			 * @param int                                   $form_id                Current Form id.
			 * @param string                                $connection_id          ID of current connection.
			 * @param array                                 $submitted_data
			 * @param array                                 $connection_settings    current connection setting, contains `name` and `webhook_url`.
			 * @param array                                 $form_settings          Displayed Form settings.
			 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Form Settings instance.
			 */
			$args = apply_filters_deprecated(
				'forminator_addon_zapier_post_to_webhook_args',
				array( $args, $form_id, $connection_id, $submitted_data, $connection_settings, $form_settings, $form_settings_instance ),
				'1.18.0',
				'forminator_addon_webhook_post_to_webhook_args'
			);
			$args = apply_filters(
				'forminator_addon_webhook_post_to_webhook_args',
				$args,
				$form_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$form_settings,
				$form_settings_instance
			);

			// replace '-' to '_' in keys because some integrations don't support dashes like tray.io and workato.
			// don't do it for zapier for backward compatibility.
			$args = $form_settings_instance::replace_dashes_in_keys( $args, $endpoint );

			$webhook_api->post_( $args );

			forminator_addon_maybe_log( __METHOD__, 'Success Send Data' );

			return array(
				'is_sent'         => true,
				'connection_name' => $connection_settings['name'],
				'description'     => esc_html__( 'Successfully send data to Webhook', 'forminator' ),
				'data_sent'       => $webhook_api->get_last_data_sent(),
				'data_received'   => $webhook_api->get_last_data_received(),
				'url_request'     => $webhook_api->get_last_url_request(),
			);

		} catch ( Forminator_Addon_Webhook_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, 'Failed to Send to Webhook' );

			return array(
				'is_sent'         => false,
				'description'     => $e->getMessage(),
				'connection_name' => $connection_settings['name'],
				'data_sent'       => ( ( $webhook_api instanceof Forminator_Addon_Webhook_Wp_Api ) ? $webhook_api->get_last_data_sent() : array() ),
				'data_received'   => ( ( $webhook_api instanceof Forminator_Addon_Webhook_Wp_Api ) ? $webhook_api->get_last_data_received() : array() ),
				'url_request'     => ( ( $webhook_api instanceof Forminator_Addon_Webhook_Wp_Api ) ? $webhook_api->get_last_url_request() : '' ),
			);
		}
	}

	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Webhook_Form_Hooks::get_additional_entry_item()
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 *
		 * Filterwebhook metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $form_id                current Form ID.
		 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Form Settings instance.
		 */
		$addon_meta_data = apply_filters_deprecated(
			'forminator_addon_zapier_metadata',
			array( $addon_meta_data, $form_id, $form_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_metadata'
		);
		$addon_meta_data = apply_filters(
			'forminator_addon_webhook_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$addon_meta_datas = $addon_meta_data;
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return array();
		}

		$addon_meta_data = $addon_meta_data[0];

		// make sure its `status`, because we only add this.
		// when its `status` then its single connection (backward compat on dev).
		// when its status-$MULTI_ID its multiple connection its default behavior.
		if ( 'status' !== $addon_meta_data['name'] ) {
			if ( stripos( $addon_meta_data['name'], 'status-' ) === 0 ) {
				return $this->on_render_entry_multi_connection( $addon_meta_datas );
			}

			return array();
		}

		$additional_entry_item = $this->get_additional_entry_item( $addon_meta_data );
		if ( empty( $additional_entry_item ) ) {
			return array();
		}

		return array( $additional_entry_item );

	}

	/**
	 * Loop through addon meta data on multiple zap(s)
	 *
	 * @param $addon_meta_datas
	 *
	 * @return array
	 */
	private function on_render_entry_multi_connection( $addon_meta_datas ) {
		$additional_entry_item = array();
		foreach ( $addon_meta_datas as $addon_meta_data ) {
			$additional_entry_item[] = $this->get_additional_entry_item( $addon_meta_data );
		}

		return $additional_entry_item;

	}

	/**
	 * Format additional entry item as label and value arrays
	 *
	 * - Integration Name : its defined by user when they addingwebhook integration on their form
	 * - Sent To Webhook : will be Yes/No value, that indicates whether sending data towebhook was successful
	 * - Info : Text that are generated by addon when building and sending data towebhook @see Forminator_Addon_Webhook_Form_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Webhook::is_show_full_log() @see FORMINATOR_ADDON_WEBHOOK_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data towebhook
	 *      - Data sent to Webhook : json encoded body request that was sent
	 *      - Data received from Webhook : json encoded body response that was received
	 *
	 * @param $addon_meta_data
	 *
	 * @return array
	 */
	private function get_additional_entry_item( $addon_meta_data ) {

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return array();
		}
		$status                = $addon_meta_data['value'];
		$additional_entry_item = array(
			'label' => esc_html__( 'Webhook Integration', 'forminator' ),
			'value' => '',
		);

		$sub_entries = array();
		if ( isset( $status['connection_name'] ) ) {
			$sub_entries[] = array(
				'label' => esc_html__( 'Integration Name', 'forminator' ),
				'value' => $status['connection_name'],
			);
		}

		if ( isset( $status['is_sent'] ) ) {
			$is_sent       = true === $status['is_sent'] ? esc_html__( 'Yes', 'forminator' ) : esc_html__( 'No', 'forminator' );
			$sub_entries[] = array(
				'label' => esc_html__( 'Sent To Webhook', 'forminator' ),
				'value' => $is_sent,
			);
		}

		if ( isset( $status['description'] ) ) {
			$sub_entries[] = array(
				'label' => esc_html__( 'Info', 'forminator' ),
				'value' => $status['description'],
			);
		}

		if ( Forminator_Addon_Webhook::is_show_full_log() ) {
			// too long to be added on entry data enable this with `define('FORMINATOR_ADDON_WEBHOOK_SHOW_FULL_LOG', true)`.
			if ( isset( $status['url_request'] ) ) {
				$sub_entries[] = array(
					'label' => esc_html__( 'API URL', 'forminator' ),
					'value' => $status['url_request'],
				);
			}

			if ( isset( $status['data_sent'] ) ) {
				$sub_entries[] = array(
					'label' => esc_html__( 'Data sent to Webhook', 'forminator' ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_sent'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}

			if ( isset( $status['data_received'] ) ) {
				$sub_entries[] = array(
					'label' => esc_html__( 'Data received from Webhook', 'forminator' ),
					'value' => '<pre class="sui-code-snippet">' . wp_json_encode( $status['data_received'], JSON_PRETTY_PRINT ) . '</pre>',
				);
			}
		}

		$additional_entry_item['sub_entries'] = $sub_entries;

		// return single array.
		return $additional_entry_item;
	}

	/**
	 * Webhook will add a column on the title/header row
	 * its called `Webhook Info` which can be translated on forminator lang
	 *
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => esc_html__( 'Webhook Info', 'forminator' ),
		);

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 * Filterwebhook headers on export file
		 *
		 * @since 1.1
		 *
		 * @param array                                 $export_headers         headers to be displayed on export file.
		 * @param int                                   $form_id                current Form ID.
		 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Form Settings instance.
		 */
		$export_headers = apply_filters_deprecated(
			'forminator_addon_zapier_export_headers',
			array( $export_headers, $form_id, $form_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_export_headers'
		);
		$export_headers = apply_filters(
			'forminator_addon_webhook_export_headers',
			$export_headers,
			$form_id,
			$form_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Webhook will add a column that give user information whether sending data towebhook successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$form_id                = $this->form_id;
		$form_settings_instance = $this->form_settings_instance;

		/**
		 *
		 * Filterwebhook metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $form_id                current Form ID.
		 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Form Settings instance.
		 */
		$addon_meta_data = apply_filters_deprecated(
			'forminator_addon_zapier_metadata',
			array( $addon_meta_data, $form_id, $form_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_metadata'
		);
		$addon_meta_data = apply_filters(
			'forminator_addon_webhook_metadata',
			$addon_meta_data,
			$form_id,
			$form_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filterwebhook columns to be displayed on export submissions
		 *
		 * @since 1.1
		 *
		 * @param array                                 $export_columns         column to be exported.
		 * @param int                                   $form_id                current Form ID.
		 * @param Forminator_Form_Entry_Model           $entry_model            Form Entry Model.
		 * @param array                                 $addon_meta_data        meta data saved by addon on entry fields.
		 * @param Forminator_Addon_Webhook_Form_Settings $form_settings_instance Webhook Form Settings instance.
		 */
		$export_columns = apply_filters_deprecated(
			'forminator_addon_zapier_export_columns',
			array( $export_columns, $form_id, $entry_model, $addon_meta_data, $form_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_export_columns'
		);
		$export_columns = apply_filters(
			'forminator_addon_webhook_export_columns',
			$export_columns,
			$form_id,
			$entry_model,
			$addon_meta_data,
			$form_settings_instance
		);

		return $export_columns;
	}

	/**
	 * Get Addon meta data, will be recursive if meta data is multiple because of multiple connection added
	 *
	 * @param        $addon_meta_data
	 * @param        $key
	 * @param string $default
	 *
	 * @return string
	 */
	private function get_from_addon_meta_data( $addon_meta_data, $key, $default = '' ) {
		$addon_meta_datas = $addon_meta_data;
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return $default;
		}

		$addon_meta_data = $addon_meta_data[0];

		// make sure its `status`, because we only add this.
		if ( 'status' !== $addon_meta_data['name'] ) {
			if ( stripos( $addon_meta_data['name'], 'status-' ) === 0 ) {
				$meta_data = array();
				foreach ( $addon_meta_datas as $addon_meta_data ) {
					// make it like single value so it will be processed like single meta data.
					$addon_meta_data['name'] = 'status';

					// add it on an array for next recursive process.
					$meta_data[] = $this->get_from_addon_meta_data( array( $addon_meta_data ), $key, $default );
				}

				return implode( ', ', $meta_data );
			}

			return $default;

		}

		if ( ! isset( $addon_meta_data['value'] ) || ! is_array( $addon_meta_data['value'] ) ) {
			return $default;
		}
		$status = $addon_meta_data['value'];
		if ( isset( $status[ $key ] ) ) {
			$connection_name = '';
			if ( 'connection_name' !== $key ) {
				if ( isset( $status['connection_name'] ) ) {
					$connection_name = '[' . $status['connection_name'] . '] ';
				}
			}

			return $connection_name . $status[ $key ];
		}

		return $default;
	}
}
