<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Custom_Form_Admin
 *
 * @property Forminator_Custom_Forms module
 * @since 1.0
 */
class Forminator_Custom_Form_Admin extends Forminator_Admin_Module {

	/**
	 * module objects
	 *
	 * @var array
	 */
	public $module;

	/**
	 * Init module admin
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->module       = Forminator_Custom_Forms::get_instance();
		$this->page         = 'forminator-cform';
		$this->page_edit    = 'forminator-cform-wizard';
		$this->page_entries = 'forminator-cform-view';
		$this->dir          = dirname( __FILE__ );
	}

	/**
	 * Add module pages to Admin
	 *
	 * @since 1.0
	 */
	public function add_menu_pages() {
		new Forminator_CForm_Page( $this->page, 'custom-form/list', esc_html__( 'Forms', 'forminator' ), esc_html__( 'Forms', 'forminator' ), 'forminator' );
		new Forminator_CForm_New_Page( $this->page_edit, 'custom-form/wizard', esc_html__( 'Edit Form', 'forminator' ), esc_html__( 'New Custom Form', 'forminator' ), 'forminator' );
		new Forminator_CForm_View_Page( $this->page_entries, 'custom-form/entries', esc_html__( 'Submissions:', 'forminator' ), esc_html__( 'View Custom Form', 'forminator' ), 'forminator' );
	}

	/**
	 * Pass module defaults to JS
	 *
	 * @since 1.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_js_defaults( $data ) {
		$model = null;
		if ( $this->is_admin_wizard() ) {
			$data['application'] = 'builder';
			$settings            = array();

			$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
			if ( $id ) {
				$data['formNonce'] = wp_create_nonce( 'forminator_save_builder_fields' );
				$model             = Forminator_Base_Form_Model::get_model( $id );
			}

			$wrappers = array();
			if ( is_object( $model ) ) {
				$wrappers = $model->get_fields_grouped();
				$settings = $model->get_form_settings();
				$behavior = $model->get_behavior_array();
			}

			if ( isset( $model->settings['form-type'] ) && 'registration' === $model->settings['form-type'] ) {
				$notifications = self::get_registration_form_notifications( $model );
			} else {
				$notifications = self::get_form_notifications( $model );
			}

			$form_id     = isset( $model->id ) ? $model->id : 0;
			$form_name   = isset( $model->name ) ? $model->name : '';
			$form_status = isset( $model->status ) ? $model->status : 'draft';

			$notifications       = apply_filters( 'forminator_form_notifications', $notifications, $model, $data, $this );

			$pdf_array = self::get_pdf_data( $form_id );
			$pdf_array = apply_filters( 'forminator_form_pdfs', $pdf_array, $model, $data, $this );

			$data['currentForm'] = array(
				'wrappers'              => $wrappers,
				'settings'              => array_merge(
					array(
						'pagination-header' => 'nav',
						'paginationData'    => array(
							'pagination-header-design' => 'show',
							'pagination-header'        => 'nav',
						),
					),
					$settings,
					array(
						'form_id'     => $form_id,
						'form_name'   => $form_name,
						'form_status' => $form_status,
					)
				),
				'notifications'         => $notifications,
				'pdfs'                  => $pdf_array,
				'behaviorArray'         => isset( $behavior ) ? $behavior : array(),
				'integrationConditions' => ! empty( $model->integration_conditions ) ? $model->integration_conditions : array(),
			);
		}

		$data['modules']['custom_form'] = array(
			'templates'     => $this->module->get_templates(),
			'new_form_url'  => menu_page_url( $this->page_edit, false ),
			'form_list_url' => menu_page_url( $this->page, false ),
			'preview_nonce' => wp_create_nonce( 'forminator_popup_preview_form' ),
		);

		$presets_page = admin_url( 'admin.php?page=forminator-settings&section=appearance-presets' );

		$data['modules']['ApplyPreset'] = array(
			'title'       => esc_html__( 'Choose Preset', 'forminator' ),
			'description' => esc_html__( 'Select an appearance preset from the list below to apply the appearance to the selected form(s)', 'forminator' ),
			'presetUrl'   => $presets_page,
			'notice'      => esc_html__( 'The current appearance configurations will be overwritten for the selected form(s).', 'forminator' ),
			'button'      => esc_html__( 'Apply Preset', 'forminator' ),
			'nonce'       => wp_create_nonce( 'forminator_apply_preset' ),
			'selectbox'   => Forminator_Settings_Page::get_preset_selectbox(),
			'presets'     => Forminator_Settings_Page::get_preset_names(),
		);

		return apply_filters( 'forminator_form_admin_data', $data, $model, $this );
	}

	/**
	 * Localize module
	 *
	 * @since 1.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function add_l10n_strings( $data ) {
		$data['custom_form'] = array(
			'popup_label' => esc_html__( 'Choose Form Type', 'forminator' ),
		);

		$data['builder'] = array(
			'save' => esc_html__( 'Save', 'forminator' ),
		);

		$data['product'] = array(
			'add_variations' => esc_html__( 'Add some variations of your product.', 'forminator' ),
			'use_list'       => esc_html__( 'Display in list?', 'forminator' ),
			'add_variation'  => esc_html__( 'Add Variation', 'forminator' ),
			'image'          => esc_html__( 'Image', 'forminator' ),
			'name'           => esc_html__( 'Name', 'forminator' ),
			'price'          => esc_html__( 'Price', 'forminator' ),
		);

		$data['appearance'] = array(
			'customize_typography'    => esc_html__( 'Customize typography', 'forminator' ),
			'custom_font_family'      => esc_html__( 'Enter custom font family name', 'forminator' ),
			'custom_font_placeholder' => esc_html__( 'E.g. \'Arial\', sans-serif', 'forminator' ),
			'custom_font_description' => esc_html__( 'Type the font family name, as you would in CSS', 'forminator' ),
			'font_family'             => esc_html__( 'Font family', 'forminator' ),
			'font_size'               => esc_html__( 'Font size', 'forminator' ),
			'font_weight'             => esc_html__( 'Font weight', 'forminator' ),
			'select_font'             => esc_html__( 'Select font', 'forminator' ),
			'custom_font'             => esc_html__( 'Custom user font', 'forminator' ),
			'minutes'                 => esc_html__( 'minute(s)', 'forminator' ),
			'hours'                   => esc_html__( 'hour(s)', 'forminator' ),
			'days'                    => esc_html__( 'day(s)', 'forminator' ),
			'weeks'                   => esc_html__( 'week(s)', 'forminator' ),
			'months'                  => esc_html__( 'month(s)', 'forminator' ),
			'years'                   => esc_html__( 'year(s)', 'forminator' ),
		);

		$data['tab_appearance'] = array(
			'basic_selectors'      => esc_html__( 'Basic selectors', 'forminator' ),
			'advanced_selectors'   => esc_html__( 'Advanced selectors', 'forminator' ),
			'pagination_selectors' => esc_html__( 'Pagination selectors', 'forminator' ),
		);

		return $data;
	}

	/**
	 * Return template
	 *
	 * @since 1.0
	 * @return Forminator_Template|false
	 */
	private function get_template() {
		$id = Forminator_Core::sanitize_text_field( 'template', 'blank' );

		if ( empty( $this->module->templates ) ) {
			return;
		}

		foreach ( $this->module->templates as $template ) {
			if ( $template->options['id'] === $id ) {
				return $template;
			}
		}

		return false;
	}

	/**
	 * Return Form notifications
	 *
	 * @since 1.1
	 *
	 * @param Forminator_Form_Model|null $form
	 *
	 * @return mixed
	 */
	public static function get_form_notifications( $form ) {
		if ( ! isset( $form ) || ! isset( $form->notifications ) ) {
			return array(
				array(
					'slug'             => 'notification-1234-4567',
					'label'            => esc_html__( 'Admin Email', 'forminator' ),
					'email-recipients' => 'default',
					'recipients'       => get_option( 'admin_email' ),
					'email-subject'    => esc_html__( 'New Form Entry #{submission_id} for {form_name}', 'forminator' ),
					'email-editor'     => sprintf( '%1$s <br/> {all_fields} <br/>---<br/> %2$s',
						esc_html__( 'You have a new website form submission:', 'forminator' ),
						esc_html__( 'This message was sent from {site_url}.', 'forminator' )
					),
					'email-attachment' => 'true',
					'type'			   => 'default',
				),
			);
		}

		return $form->notifications;
	}

	/**
	 * Get Registration Form notifications
	 *
	 * @since 1.11
	 *
	 * @param Forminator_Form_Model|null $form
	 * @param Forminator_Template|null   $template
	 *
	 * @return mixed
	 */
	public static function get_registration_form_notifications( $form, $template = null ) {
		if ( ! isset( $form ) || ! isset( $form->notifications ) ) {
			$msg_footer = esc_html__( 'This message was sent from {site_url}', 'forminator' );
			// For admin.
			$message = sprintf( '%1$s <br/><br/> {all_fields} <br/><br/> %2$s<br/>',
				esc_html__( 'New user registration on your site {site_url}:', 'forminator' ),
				esc_html__( 'Click {submission_url} to view the submission.', 'forminator' )
			);
			$message .= '<br/>---<br/>';
			$message .= $msg_footer;

			$message_method_email = $message;

			$message_method_manual = sprintf( '%1$s <br/><br/> {all_fields} <br/><br/> %2$s',
				esc_html__( 'New user registration on your site {site_url}:', 'forminator' ),
				esc_html__( 'The account is still not activated and needs your approval. To activate this account, click the link below.', 'forminator' )
			);
			$message_method_manual .= '<br/>{account_approval_link} <br/><br/>';
			$message_method_manual .= esc_html__( 'Click {submission_url} to view the submission on your website\'s dashboard.', 'forminator' ) . '<br/><br/>';
			$message_method_manual .= $msg_footer;

			$notifications[] = array(
				'slug'                        => 'notification-1111-1111',
				'label'                       => esc_html__( 'Admin Email', 'forminator' ),
				'email-recipients'            => 'default',
				'recipients'                  => get_option( 'admin_email' ),
				'email-subject'               => esc_html__( 'New User Registration on {site_url}', 'forminator' ),
				'email-editor'                => $message,

				'email-subject-method-email'  => esc_html__( 'New User Registration on {site_url}', 'forminator' ),
				'email-editor-method-email'   => $message_method_email,
				'email-subject-method-manual' => esc_html__( 'New User Registration on {site_url} needs approval.', 'forminator' ),
				'email-editor-method-manual'  => $message_method_manual,
				'type'  					  => 'registration',
			);
			if ( ! is_null( $template ) ) {
				$email = self::get_registration_form_customer_email_slug( $template );
			} else {
				$email = self::get_registration_form_customer_email_slug( $form );
			}
			//For customer
			$message  = sprintf( '%1$s <br/><br/> {all_fields} <br/><br/>',
				esc_html__( 'Your new account on our {site_title} site is ready to go. Here are your details:', 'forminator' )
			);
			$message .= sprintf( esc_html__( 'Login to your new account %1$shere%2$s.', 'forminator' ), '<a href="' . wp_login_url() . '">', '</a>' );
			$message .= '<br/><br/>---<br/>';
			$message .= $msg_footer;

			$message_method_email  = esc_html__( 'Dear {username} ', 'forminator' ) . '<br/><br/>';
			$message_method_email .= esc_html__( 'Thank you for signing up on our website. You are one step away from activating your account. ', 'forminator' );
			$message_method_email .= esc_html__( 'We have sent you another email containing a confirmation link. Please click on that link to activate your account.', 'forminator' ) . '<br/><br/>';
			$message_method_email .= $msg_footer;

			$message_method_manual  = esc_html__( 'Your new account on {site_title} is under review.', 'forminator' ) . '<br/>';
			$message_method_manual .= esc_html__( 'You\'ll receive another email once the site admin approves your account. You should be able to login into your account after that.', 'forminator' );
			$message_method_manual .= '<br/><br/>---<br/>';
			$message_method_manual .= $msg_footer;

			$notifications[] = array(
				'slug'                        => 'notification-1111-1112',
				'label'                       => esc_html__( 'User Confirmation Email', 'forminator' ),
				'email-recipients'            => 'default',
				'recipients'                  => $email,
				'email-subject'               => esc_html__( 'Your new account on {site_title}', 'forminator' ),
				'email-editor'                => $message,

				'email-subject-method-email'  => esc_html__( 'Activate your account on {site_url}', 'forminator' ),
				'email-editor-method-email'   => $message_method_email,
				'email-subject-method-manual' => esc_html__( 'Your new account on {site_title} is under review.', 'forminator' ),
				'email-editor-method-manual'  => $message_method_manual,
			);

			return $notifications;
		}

		return $form->notifications;
	}

	/**
	 * Get customer email as field slug
	 *
	 * @since 1.11
	 *
	 * @param Forminator_Form_Model|Forminator_Template $form
	 * @param string                                    $default
	 *
	 * @return string
	 */
	public static function get_registration_form_customer_email_slug( $form, $default = '{email-1}' ) {
		if ( isset( $form->settings['registration-email-field'] ) && ! empty( $form->settings['registration-email-field'] ) ) {
			$email = $form->settings['registration-email-field'];
			if ( false === strpos( $email, '{' ) ) {
				$email = '{' . $email . '}';
			}

			return $email;
		}

		return $default;
	}

	/**
	 * Form default data
	 *
	 * @param $name
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function get_default_settings( $name, $settings = array() ) {
		$default_settings = array_merge(
			array(
				'formName'             => $name,
				'pagination-header'    => 'nav',
				'version'              => FORMINATOR_VERSION,
				'form-border-style'    => 'solid',
				'form-padding'         => '',
				'form-border'          => '',
				'fields-style'         => 'open',
				'field-image-size'     => 'custom',
				'validation'           => 'on_submit',
				'akismet-protection'   => true,
				'form-style'           => 'default',
				'enable-ajax'          => 'true',
				'autoclose'            => 'true',
				'submission-indicator' => 'show',
				'indicator-label'      => esc_html__( 'Submitting...', 'forminator' ),
				'paginationData'       => array(
					'pagination-header-design' => 'show',
					'pagination-header'        => 'nav',
				),
			),
			$settings
		);

		/**
		 * Filter default settings for forms
		 *
		 * @param array $default_settings Default settings.
		 */
		$default_settings = apply_filters( 'forminator_form_default_settings', $default_settings );

		return $default_settings;
	}

	/**
	 * Create quiz module
	 *
	 * @since 1.14
	 *
	 * @return no return
	 */
	public function create_module() {
		if ( ! $this->is_admin_wizard() || self::is_edit() ) {
			return;
		}

		// Load settings from template.
		$template = $this->get_template();

		$name   = Forminator_Core::sanitize_text_field( 'name' );
		$status = Forminator_Form_Model::STATUS_DRAFT;
		$id     = self::create( $name, $status, $template );

		$wizard_url = admin_url( 'admin.php?page=forminator-cform-wizard&id=' . $id );

		wp_safe_redirect( $wizard_url );
	}

	/**
	 * Create form
	 *
	 * @param string $name Name.
	 * @param string $status Status.
	 * @param object $template Template.
	 * @return int post ID
	 */
	public static function create( $name, $status, $template = null ) {
		// Create new form model.
		$model = new Forminator_Form_Model();

		if ( isset( $model->notifications ) ) {
			unset( $model->notifications );
		}

		// Setup notifications.
		if ( $template && isset( $template->settings['form-type'] )
				&& in_array( $template->settings['form-type'], array( 'registration', 'login' ), true ) ) {
			$notifications = 'registration' === $template->settings['form-type']
				? self::get_registration_form_notifications( $model, $template )
				: array();
		} else {
			$notifications = self::get_form_notifications( $model );
		}

		// If template, load from file.
		if ( $template ) {
			$settings = self::get_default_settings( $name, $template->settings );

			// Setup template fields.
			foreach ( $template->fields as $row ) {
				foreach ( $row['fields'] as $f ) {
					$field          = new Forminator_Form_Field_Model();
					$field->form_id = $row['wrapper_id'];
					$field->slug    = $f['element_id'];
					unset( $f['element_id'] );
					$field->parent_group = ! empty( $row['parent_group'] ) ? $row['parent_group'] : '';
					$field->import( $f );
					$model->add_field( $field );
				}
			}
		} else {
			$settings = self::get_default_settings( $name, array() );
		}

		$model->name          = $name;
		$model->notifications = $notifications;

		$model->settings = self::validate_settings( $settings );
		$model->status   = $status;

		$behaviors        = $model->get_behavior_array();
		$model->behaviors = $behaviors;

		// Save data.
		$id = $model->save();

		return $id;
	}

	/**
	 * Update form
	 *
	 * @param string $id Module ID.
	 * @param string $title Name.
	 * @param string $status Status.
	 * @param object $template Template.
	 * @return int post ID
	 */
	public static function update( $id, $title, $status, $template ) {
		if ( is_null( $id ) || $id <= 0 ) {
			$form_model = new Forminator_Form_Model();
			$action     = 'create';

			if ( empty( $status ) ) {
				$status = Forminator_Form_Model::STATUS_PUBLISH;
			}
		} else {
			$form_model = Forminator_Base_Form_Model::get_model( $id );
			$action     = 'update';

			if ( ! is_object( $form_model ) ) {
				return new WP_Error( 'forminator_model_not_exist', esc_html__( 'Form model doesn\'t exist', 'forminator' ) );
			}

			if ( empty( $status ) ) {
				$status = $form_model->status;
			}

			//we need to empty fields cause we will send new data
			$form_model->clear_fields();
		}

		$fields = isset( $template->fields ) ? $template->fields : array();
		foreach ( $fields as $row ) {
			foreach ( $row['fields'] as $f ) {
				$field          = new Forminator_Form_Field_Model();
				$field->form_id = $row['wrapper_id'];
				$field->slug    = $f['element_id'];
				unset( $f['element_id'] );
				$field->parent_group = ! empty( $row['parent_group'] ) ? $row['parent_group'] : '';
				$field->import( $f );
				$form_model->add_field( $field );
			}
		}

		$settings = self::validate_settings( $template->settings );

		$notifications = array();
		if ( isset( $template->notifications ) ) {
			$notifications = $template->notifications;

			$count = 0;
			foreach ( $template->notifications as $notification ) {
				if ( isset( $notification['email-editor'] ) ) {
					$notifications[ $count ]['email-editor'] = wp_kses_post( $template->notifications[ $count ]['email-editor'] );
				}
				if ( isset( $notification['email-editor-method-email'] ) ) {
					$notifications[ $count ]['email-editor-method-email'] = wp_kses_post( $template->notifications[ $count ]['email-editor-method-email'] );
				}
				if ( isset( $notification['email-editor-method-manual'] ) ) {
					$notifications[ $count ]['email-editor-method-manual'] = wp_kses_post( $template->notifications[ $count ]['email-editor-method-manual'] );
				}

				$count++;
			}
		}

		// Handle quiz questions
		$form_model->notifications = $notifications;

		$form_model->name     = sanitize_title( $title );
		$settings['formName'] = sanitize_text_field( $title );

		$form_model->settings = $settings;

		$form_model->integration_conditions = ! empty( $template->integration_conditions ) ? $template->integration_conditions : array();

		$form_model->behaviors = ! empty( $template->behaviors ) ? $template->behaviors : array();

		// don't update leads post_status.
		if ( 'leads' !== $form_model->status && 'pdf_form' !== $form_model->status ) {
			$form_model->status = $status;
		}

		// Save data
		$id = $form_model->save();

		try {
			/**
			 * Action called after form saved to database
			 *
			 * @since 1.11
			 *
			 * @param int    $id - form id.
			 * @param string $title - form title.
			 * @param string $status - form status.
			 * @param array  $fields - form fields.
			 * @param array  $settings - form settings.
			 */
			do_action( 'forminator_custom_form_action_' . $action, $id, $title, $status, $fields, $settings );
		} catch ( Exception $e ) {
			return new WP_Error( 'forminator_stripe_error', $e->getMessage() );
		}

		// add privacy settings to global option.
		$override_privacy = false;
		if ( isset( $settings['enable-submissions-retention'] ) ) {
			$override_privacy = filter_var( $settings['enable-submissions-retention'], FILTER_VALIDATE_BOOLEAN );
		}
		$retention_number = null;
		$retention_unit   = null;
		if ( $override_privacy ) {
			$retention_number = 0;
			$retention_unit   = 'days';
			if ( isset( $settings['submissions-retention-number'] ) ) {
				$retention_number = (int) $settings['submissions-retention-number'];
			}
			if ( isset( $settings['submissions-retention-unit'] ) ) {
				$retention_unit = $settings['submissions-retention-unit'];
			}
		}

		forminator_update_form_submissions_retention( $id, $retention_number, $retention_unit );

		// Add function here for draft retentions
		self::add_draft_retention_settings( $id, $settings );

		Forminator_Render_Form::regenerate_css_file( $id );
		// Purge count forms cache
		wp_cache_delete( 'forminator_form_total_entries', 'forminator_form_total_entries' );
		wp_cache_delete( 'forminator_form_total_entries_publish', 'forminator_form_total_entries_publish' );
		wp_cache_delete( 'forminator_form_total_entries_draft', 'forminator_form_total_entries_draft' );

		return $id;
	}

	/**
	 * Add draft retention settings to global retention options
	 *
	 * @param	string 	$form_id Module ID.
	 * @param 	array 	$settings Form settings.
	 */
	public static function add_draft_retention_settings( $form_id, $settings ) {
		if ( ! isset( $settings['use_save_and_continue'] ) || ! filter_var( $settings['use_save_and_continue'], FILTER_VALIDATE_BOOLEAN ) ) {
			return;
		}

		$retention_number = null;
		$retention_unit   = null;
		if ( ! empty( $settings['sc_draft_retention'] ) ) {
			$retention_number = (int) $settings['sc_draft_retention'];
			$retention_unit	  = 'days';
		}

		forminator_update_form_submissions_retention( $form_id, $retention_number, $retention_unit, true );
	}

	/**
	 * Get PDF data
	 *
	 * @param $form_id
	 *
	 * @return array
	 */
	public function get_pdf_data( $form_id ) {
		$pdf_data = array();
		$pdfs     = Forminator_API::get_forms( null, 1, 999, 'pdf_form', $form_id );
		if ( ! empty( $pdfs ) ) {
			foreach ( $pdfs as $key => $pdf ) {
				$pdf_data[ $key ]['pdfId']       = esc_html( $pdf->id );
				$pdf_data[ $key ]['pdfFilename'] = esc_html( $pdf->name );
				$pdf_data[ $key ]['pdfTemplate'] = esc_html( $pdf->settings['pdf_template'] );
			}
		}

		return $pdf_data;
	}
}