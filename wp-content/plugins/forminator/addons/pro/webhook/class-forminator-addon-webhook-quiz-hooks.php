<?php

/**
 * Class Forminator_Addon_Webhook_Quiz_Hooks
 *
 * @since 1.6.2
 */
class Forminator_Addon_Webhook_Quiz_Hooks extends Forminator_Addon_Quiz_Hooks_Abstract {

	/**
	 * Addon instance are auto available form abstract
	 * Its added here for development purpose,
	 * Auto-complete will resolve addon directly to `Webhook` instance instead of the abstract
	 * And its public properties can be exposed
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Webhook
	 */
	protected $addon;

	/**
	 * Quiz Settings Instance
	 *
	 * @since 1.6.2
	 * @var Forminator_Addon_Webhook_Quiz_Settings | null
	 */
	protected $quiz_settings_instance;

	/**
	 * Forminator_Addon_Webhook_Quiz_Hooks constructor.
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $quiz_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $quiz_id ) {
		parent::__construct( $addon, $quiz_id );
		$this->_submit_quiz_error_message = esc_html__( 'Webhook failed to process submitted data. Please check your form and try again', 'forminator' );
	}

	/**
	 * Save status of request sent and received for each connected zap(s)
	 *
	 * @since 1.6.2
	 *
	 * @param array $submitted_data
	 * @param array $current_entry_fields
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data, $current_entry_fields = array() ) {

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filterwebhook submitted form data to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $submitted_data
		 * @param int                                   $quiz_id                current Form ID.
		 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Addon Quiz Settings instance.
		 */
		$submitted_data = apply_filters_deprecated(
			'forminator_addon_zapier_quiz_submitted_data',
			array( $submitted_data, $quiz_id, $quiz_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_quiz_submitted_data'
		);
		$submitted_data = apply_filters(
			'forminator_addon_webhook_quiz_submitted_data',
			$submitted_data,
			$quiz_id,
			$quiz_settings_instance
		);

		forminator_addon_maybe_log( __METHOD__, $submitted_data );

		$addon_setting_values = $quiz_settings_instance->get_quiz_settings_values();
		$quiz_settings        = $quiz_settings_instance->get_quiz_settings();

		$data = array();

		/**
		 * Fires before sending data to Webhook URL(s)
		 *
		 * @since 1.6.2
		 *
		 * @param int                                   $quiz_id                current Quiz ID.
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Addon Quiz Settings instance.
		 */
		do_action_deprecated( 'forminator_addon_zapier_quiz_before_post_to_webhook', array( $quiz_id, $submitted_data, $quiz_settings_instance ), '1.18.0', 'forminator_addon_webhook_quiz_before_post_to_webhook' );
		do_action( 'forminator_addon_webhook_quiz_before_post_to_webhook', $quiz_id, $submitted_data, $quiz_settings_instance );

		foreach ( $addon_setting_values as $key => $addon_setting_value ) {
			// save it on entry field, with name `status-$MULTI_ID`, and value is the return result on sending data towebhook.
			$data[] = array(
				'name'  => 'status-' . $key,
				'value' => $this->get_status_on_send_data( $key, $submitted_data, $addon_setting_value, $quiz_settings, $current_entry_fields ),
			);
		}

		$entry_fields = $data;
		/**
		 * Filterwebhook entry fields to be saved to entry model
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $entry_fields
		 * @param int                                   $quiz_id                current Quiz ID.
		 * @param array                                 $submitted_data
		 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Quiz Settings instance.
		 */
		$data = apply_filters_deprecated(
			'forminator_addon_zapier_quiz_entry_fields',
			array( $entry_fields, $quiz_id, $submitted_data, $quiz_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_quiz_entry_fields'
		);
		$data = apply_filters(
			'forminator_addon_webhook_quiz_entry_fields',
			$data,
			$quiz_id,
			$submitted_data,
			$quiz_settings_instance
		);

		return $data;

	}

	/**
	 * Get status on sending data towebhook
	 *
	 * @since 1.6.2
	 *
	 * @param $connection_id
	 * @param $submitted_data
	 * @param $connection_settings
	 * @param $quiz_settings
	 * @param $current_entry_fields
	 *
	 * @return array `is_sent` true means its success send data towebhook, false otherwise
	 */
	private function get_status_on_send_data( $connection_id, $submitted_data, $connection_settings, $quiz_settings, $current_entry_fields ) {
		// initialize as null.
		$webhook_api = null;

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		// check required fields.
		try {
			if ( ! isset( $connection_settings['webhook_url'] ) ) {
				throw new Forminator_Addon_Webhook_Exception( esc_html__( 'Webhook URL is not properly set up', 'forminator' ) );
			}

			$endpoint = $connection_settings['webhook_url'];
			/**
			 * Filter Endpoint Webhook URL to send
			 *
			 * @since 1.6.2
			 *
			 * @param string $endpoint
			 * @param int    $quiz_id             current Form ID.
			 * @param array  $connection_settings current connection setting, it contains `name` and `webhook_url`.
			 */
			$endpoint = apply_filters_deprecated(
				'forminator_addon_zapier_quiz_endpoint',
				array( $endpoint, $quiz_id, $connection_settings ),
				'1.18.0',
				'forminator_addon_webhook_quiz_endpoint'
			);
			$endpoint = apply_filters(
				'forminator_addon_webhook_quiz_endpoint',
				$endpoint,
				$quiz_id,
				$connection_settings
			);

			$webhook_api = $this->addon->get_api( $endpoint );

			$args = $this->build_post_data( $current_entry_fields, $submitted_data );
			$args = $quiz_settings_instance::replace_dashes_in_keys( $args, $endpoint );

			/**
			 * Filter arguments to passed on to Webhook API
			 *
			 * @since 1.6.2
			 *
			 * @param array                                 $args
			 * @param int                                   $quiz_id                Current Quiz id.
			 * @param string                                $connection_id          ID of current connection.
			 * @param array                                 $submitted_data
			 * @param array                                 $connection_settings    current connection setting, contains `name` and `webhook_url`.
			 * @param array                                 $quiz_settings          Displayed Quiz settings.
			 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Quiz Settings instance.
			 */
			$args = apply_filters_deprecated(
				'forminator_addon_zapier_quiz_post_to_webhook_args',
				array( $args, $quiz_id, $connection_id, $submitted_data, $connection_settings, $quiz_settings, $quiz_settings_instance ),
				'1.18.0',
				'forminator_addon_webhook_quiz_post_to_webhook_args'
			);
			$args = apply_filters(
				'forminator_addon_webhook_quiz_post_to_webhook_args',
				$args,
				$quiz_id,
				$connection_id,
				$submitted_data,
				$connection_settings,
				$quiz_settings,
				$quiz_settings_instance
			);

			// replace '-' to '_' in keys because some integrations don't support dashes like tray.io and workato.
			// don't do it for zapier for backward compatibility.
			$args = $quiz_settings_instance::replace_dashes_in_keys( $args, $endpoint );

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
	 * Build sample data form current fields
	 *
	 * @since 1.6.2
	 *
	 * @param array $quiz_entry_fields
	 * @param array $submitted_data
	 *
	 * @return array
	 */
	private function build_post_data( $quiz_entry_fields, $submitted_data ) {
		$sample = array();

		$sample['quiz-name'] = forminator_get_name_from_model( $this->quiz );

		$answers         = array();
		$correct_answers = 0;
		$total_answers   = 0;
		$nowrong_result  = '';
		$questions       = $this->quiz->questions;

		if ( is_array( $quiz_entry_fields ) && isset( $quiz_entry_fields[0] ) ) {
			$quiz_entry = $quiz_entry_fields[0];
			if ( isset( $quiz_entry['name'] ) && isset( $quiz_entry['value'] ) && 'entry' === $quiz_entry['name'] ) {
				if ( is_array( $quiz_entry['value'] ) ) {

					// KNOWLEDGE.
					if ( 'knowledge' === $this->quiz->quiz_type ) {
						foreach ( $quiz_entry['value'] as $key => $data ) {
							$question_id = ( ( isset( $questions[ $key ] ) && isset( $questions[ $key ]['slug'] ) ) ? $questions[ $key ]['slug'] : uniqid() );
							// bit cleanup.
							$question_id = str_replace( 'question-', '', $question_id );

							$question   = isset( $data['question'] ) ? $data['question'] : '';
							$answer     = isset( $data['answers'] ) ? $data['answers'] : '';
							$is_correct = isset( $data['isCorrect'] ) ? $data['isCorrect'] : false;

							$answers[ $question_id ] = array(
								'question'   => $question,
								'answer'     => $answer,
								'is_correct' => $is_correct,
							);
							if ( $is_correct ) {
								$correct_answers ++;
							}
							$total_answers ++;
						}
					} elseif ( 'nowrong' === $this->quiz->quiz_type ) {
						if ( isset( $quiz_entry['value'][0] )
							&& is_array( $quiz_entry['value'][0] )
							&& isset( $quiz_entry['value'][0]['value'] )
							&& is_array( $quiz_entry['value'][0]['value'] ) ) {

							$quiz_entry = $quiz_entry['value'][0]['value'];

							$nowrong_result = ( isset( $quiz_entry['result'] ) && isset( $quiz_entry['result']['title'] ) ) ? $quiz_entry['result']['title'] : '';

							$entry_questions = ( isset( $quiz_entry['answers'] ) && is_array( $quiz_entry['answers'] ) ) ? $quiz_entry['answers'] : array();

							foreach ( $entry_questions as $key => $entry_question ) {
								$question_id = ( ( isset( $questions[ $key ] ) && isset( $questions[ $key ]['slug'] ) ) ? $questions[ $key ]['slug'] : uniqid() );
								// bit cleanup.
								$question_id = str_replace( 'question-', '', $question_id );

								$question = isset( $entry_question['question'] ) ? $entry_question['question'] : '';
								$answer   = isset( $entry_question['answer'] ) ? $entry_question['answer'] : '';

								$answers[ $question_id ] = array(
									'question' => $question,
									'answer'   => $answer,
								);
							}
						}
					}
				}
			}
		}

		$sample['answers'] = $answers;
		$result            = array();

		if ( 'knowledge' === $this->quiz->quiz_type ) {
			$result['correct'] = $correct_answers;
			$result['answers'] = $total_answers;

		} elseif ( 'nowrong' === $this->quiz->quiz_type ) {
			$result['result'] = $nowrong_result;
		}

		$sample['result'] = $result;

		$quiz_settings = $this->quiz_settings_instance->get_quiz_settings();

		// Use the following code block only when Leads is active.
		if ( isset( $quiz_settings['hasLeads'] ) && $quiz_settings['hasLeads'] ) {
			$addons_fields       = $this->quiz_settings_instance->get_form_fields();
			$quiz_submitted_data = get_addons_lead_form_entry_data( $quiz_settings, $submitted_data, $addons_fields );

			if ( ! empty( $quiz_submitted_data ) ) {
				foreach ( $quiz_submitted_data as $s => $quiz_submitted ) {
					$sample[ $s ] = $quiz_submitted;
				}
			}
		}

		return $sample;
	}

	/**
	 * Webhook will add a column on the title/header row
	 * its called `Webhook Info` which can be translated on forminator lang
	 *
	 * @since 1.6.2
	 * @return array
	 */
	public function on_export_render_title_row() {

		$export_headers = array(
			'info' => esc_html__( 'Webhook Info', 'forminator' ),
		);

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 * Filterwebhook headers on export file
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $export_headers         headers to be displayed on export file.
		 * @param int                                   $quiz_id                current Form ID.
		 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Quiz Settings instance.
		 */
		$export_headers = apply_filters_deprecated(
			'forminator_addon_zapier_quiz_export_headers',
			array( $export_headers, $quiz_id, $quiz_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_quiz_export_headers'
		);
		$export_headers = apply_filters(
			'forminator_addon_webhook_quiz_export_headers',
			$export_headers,
			$quiz_id,
			$quiz_settings_instance
		);

		return $export_headers;
	}

	/**
	 * Webhook will add a column that give user information whether sending data towebhook successfully or not
	 * It will only add one column even its multiple connection, every connection will be separated by comma
	 *
	 * @since 1.6.2
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_export_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 *
		 * Filterwebhook metadata that previously saved on db to be processed
		 *
		 * @since 1.1
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $quiz_id                current Quiz ID.
		 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Quiz Settings instance.
		 */
		$addon_meta_data = apply_filters_deprecated(
			'forminator_addon_zapier_quiz_metadata',
			array( $addon_meta_data, $quiz_id, $quiz_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_quiz_metadata'
		);
		$addon_meta_data = apply_filters(
			'forminator_addon_webhook_quiz_metadata',
			$addon_meta_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$export_columns = array(
			'info' => $this->get_from_addon_meta_data( $addon_meta_data, 'description', '' ),
		);

		/**
		 * Filterwebhook columns to be displayed on export submissions
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $export_columns         column to be exported.
		 * @param int                                   $quiz_id                current Quiz ID.
		 * @param Forminator_Form_Entry_Model           $entry_model            Form Entry Model.
		 * @param array                                 $addon_meta_data        meta data saved by addon on entry fields.
		 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Quiz Settings instance.
		 */
		$export_columns = apply_filters_deprecated(
			'forminator_addon_zapier_quiz_export_columns',
			array( $export_columns, $quiz_id, $entry_model, $addon_meta_data, $quiz_settings_instance ),
			'1.18.0',
			'forminator_addon_webhook_quiz_export_columns'
		);
		$export_columns = apply_filters(
			'forminator_addon_webhook_quiz_export_columns',
			$export_columns,
			$quiz_id,
			$entry_model,
			$addon_meta_data,
			$quiz_settings_instance
		);

		return $export_columns;
	}

	/**
	 * It wil add new row on entry table of submission page, with couple of subentries
	 * subentries included are defined in @see Forminator_Addon_Webhook_Quiz_Hooks::get_additional_entry_item()
	 *
	 * @since 1.6.1
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param                             $addon_meta_data
	 *
	 * @return array
	 */
	public function on_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {

		$quiz_id                = $this->quiz_id;
		$quiz_settings_instance = $this->quiz_settings_instance;

		/**
		 *
		 * Filter Webhook metadata that previously saved on db to be processed
		 *
		 * @since 1.6.2
		 *
		 * @param array                                 $addon_meta_data
		 * @param int                                   $quiz_id                current Quiz ID.
		 * @param Forminator_Addon_Webhook_Quiz_Settings $quiz_settings_instance Webhook Addon Quiz Settings instance.
		 */
		$addon_meta_data = apply_filters_deprecated(
			'forminator_addon_quiz_zapier_metadata',
			array( $addon_meta_data, $quiz_id, $quiz_settings_instance ),
			'1.18.0',
			'forminator_addon_quiz_webhook_metadata'
		);
		$addon_meta_data = apply_filters(
			'forminator_addon_quiz_webhook_metadata',
			$addon_meta_data,
			$quiz_id,
			$quiz_settings_instance
		);

		$addon_meta_datas = $addon_meta_data;
		if ( ! isset( $addon_meta_data[0] ) || ! is_array( $addon_meta_data[0] ) ) {
			return array();
		}

		return $this->on_render_entry_multi_connection( $addon_meta_datas );

	}

	/**
	 * Loop through addon meta data on multiple Webhook setup(s)
	 *
	 * @since 1.6.1
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
	 * - Integration Name : its defined by user when they adding Webhook integration on their quiz
	 * - Sent To Webhook : will be Yes/No value, that indicates whether sending data to Webhook API was successful
	 * - Info : Text that are generated by addon when building and sending data to Webhook @see Forminator_Addon_Webhook_Quiz_Hooks::add_entry_fields()
	 * - Below subentries will be added if full log enabled, @see Forminator_Addon_Webhook::is_show_full_log() @see FORMINATOR_ADDON_WEBHOOK_SHOW_FULL_LOG
	 *      - API URL : URL that wes requested when sending data to Webhook
	 *      - Data sent to Webhook : encoded body request that was sent
	 *      - Data received from Webhook : json encoded body response that was received
	 *
	 * @param $addon_meta_data
	 *
	 * @since 1.6.1
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
}
