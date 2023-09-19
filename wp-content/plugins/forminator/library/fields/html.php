<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Html
 *
 * @since 1.0
 */
class Forminator_Html extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'html';

	/**
	 * @var string
	 */
	public $type = 'html';

	/**
	 * @var int
	 */
	public $position = 17;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var string
	 */
	public $icon = 'sui-icon-code';

	/**
	 * Forminator_Html constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = esc_html__( 'HTML', 'forminator' );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'field_label' => esc_html__( 'HTML', 'forminator' ),
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		// Unsupported Autofill.
		$autofill_settings = array();

		return $autofill_settings;
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param Forminator_Render_Form $views_obj Forminator_Render_Form object.
	 *
	 * @return mixed
	 */
	public function markup( $field, $views_obj ) {
		$settings = $views_obj->model->settings;

		$html    = '';
		$label   = esc_html( self::get_property( 'field_label', $field ) );
		$id      = self::get_property( 'element_id', $field );
		$form_id = false;

		$html .= '<div class="forminator-field forminator-merge-tags" data-field="' . $id . '">';

		if ( $label ) {

			$html .= sprintf(
				'<label class="forminator-label">%s</label>',
				$label
			);
		}

			// Check if form_id exist.
		if ( isset( $settings['form_id'] ) ) {
			$form_id = $settings['form_id'];
		}

			$html .= forminator_replace_variables(
				wp_kses_post( self::get_property( 'variations', $field ) ),
				$form_id
			);

		$html .= '</div>';

		return $html;
	}
}
