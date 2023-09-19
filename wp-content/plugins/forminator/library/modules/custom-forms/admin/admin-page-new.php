<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_CForm_New_Page
 *
 * @since 1.0
 */
class Forminator_CForm_New_Page extends Forminator_Admin_Page {

	/**
	 * Get wizard title
	 *
	 * @since 1.0
	 * @return mixed
	 */
	public function getWizardTitle() {
		$id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
		if ( $id ) {
			return esc_html__( 'Edit Form', 'forminator' );
		} else {
			return esc_html__( 'New Form', 'forminator' );
		}
	}

	/**
	 * Add page screen hooks
	 *
	 * @since 1.0
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {
		// Load admin scripts.
		wp_register_script(
			'forminator-admin',
			forminator_plugin_url() . 'build/form-scripts.js',
			array(
				'jquery',
				'wp-color-picker',
				'react',
				'react-dom',
			),
			FORMINATOR_VERSION,
			true
		);
		forminator_common_admin_enqueue_scripts( true );

		// for preview.
		$style_src     = forminator_plugin_url() . 'assets/css/intlTelInput.min.css';
		$style_version = '4.0.3';

		$script_src     = forminator_plugin_url() . 'assets/js/library/intlTelInput.min.js';
		$script_src_cleave     = forminator_plugin_url() . 'assets/js/library/cleave.min.js';
		$script_src_cleave_phone     = forminator_plugin_url() . 'assets/js/library/cleave-phone.i18n.js';
		$script_version = FORMINATOR_VERSION;
		wp_enqueue_style( 'intlTelInput-forminator-css', $style_src, array(), $style_version ); // intlTelInput.
		wp_enqueue_script( 'forminator-intlTelInput', $script_src, array( 'jquery' ), $script_version, false ); // intlTelInput.
		wp_enqueue_script( 'forminator-cleave', $script_src_cleave, array( 'jquery' ), $script_version, false ); // intlTelInput.
		wp_enqueue_script( 'forminator-cleave-phone', $script_src_cleave_phone, array( 'jquery' ), $script_version, false ); // intlTelInput.

		wp_enqueue_script(
			'forminator-field-datepicker-range',
			forminator_plugin_url() . 'assets/js/library/daterangepicker.min.js',
			array( 'moment' ),
			'3.0.3',
			true
		);
		wp_enqueue_script(
			'forminator-inputmask',
			forminator_plugin_url() . 'assets/js/library/inputmask.min.js',
			array( 'jquery' ),
			FORMINATOR_VERSION,
			true
		); // inputmask.
		wp_enqueue_script(
			'forminator-jquery-inputmask',
			forminator_plugin_url() . 'assets/js/library/jquery.inputmask.min.js',
			array( 'jquery' ),
			FORMINATOR_VERSION,
			true
		); // jquery inputmask.
		wp_enqueue_script(
			'forminator-inputmask-binding',
			forminator_plugin_url() . 'assets/js/library/inputmask.binding.js',
			array( 'jquery' ),
			FORMINATOR_VERSION,
			true
		); // inputmask binding.
	}
}
