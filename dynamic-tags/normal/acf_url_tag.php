<?php
/**
 * Elementor Dynamic Tag: ACF Repeater URL
 *
 * @package REPEFOELElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_ACF_URL extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-acf-url';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'ACF URL Field', 'addoncraft-repeater-for-elementor-acf' );
	}

	/**
	 * Get tag group.
	 */
	public function get_group() {
		return 'REPEFOEL-free_dynamic-tag';
	}


	/**
	 * Get categories.
	 */
	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::URL_CATEGORY ];
	}

	/**
	 * Get all repeater fields from ACF.
	 *
	 * @return array
	 */
	public function get_all_url_fields() {
		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return [];
		}

		$field_groups        = acf_get_field_groups();
		$repefoel_all__fields = [];

		

		if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
			return [];
		}

		$i = 0;
		foreach ( $field_groups as $group ) {
			if ( empty( $group['key'] ) ) {
				continue;
			}

			$repefoel_all__fields[$i]['label'] = $group['title'];

			$fields = acf_get_fields( $group['key'] );

			if ( empty( $fields ) || ! is_array( $fields ) ) {
				continue;
			}

			foreach ( $fields as $field ) {

				if ( isset( $field['type'] ) && in_array( $field['type'], ['url'] ) ) {
					$title = isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';
					$name = isset( $field['name'] ) ? sanitize_text_field( $field['name'] ) : '';
					$repefoel_all__fields[$i]['options'][$name] = $title;
				}
			}
			$i++;
		}

		return $repefoel_all__fields;
	}


	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->add_control(
			'REPEFOEL_url_select_field',
			[
				'label'   => esc_html__( 'ACF Field', 'addoncraft-repeater-for-elementor-acf' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'groups'  => $this->get_all_url_fields(),
				'default' => '',
			]
		);
	}

	/**
	 * Render output.
	 */
	public function render() {
		$acf_url_name = $this->get_settings( 'REPEFOEL_url_select_field' );

		if ( empty( $acf_url_name ) ) {
			echo esc_url( get_permalink( get_the_ID() ) );
			return;
		}
		
		$acf_value = get_field( $acf_url_name ) ?? '';

		if ( empty($acf_value) ) {
			echo esc_url( get_permalink( get_the_ID() ) );
			return;
		}	

		echo wp_kses_post($acf_value);
		return;

	}

	/**
	 * Supported field types.
	 *
	 * @return array
	 */
	public function get_supported_fields() {
		return [
			'email',
			'file',
			'page_link',
			'post_object',
			'relationship',
			'taxonomy',
			'url',
		];
	}
}
