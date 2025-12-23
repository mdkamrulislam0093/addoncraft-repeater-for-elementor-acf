<?php
/**
 * Elementor Dynamic Tag: ACF Repeater URL
 *
 * @package REPEFOELElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_ACF_Repeater_URL extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-repeater-url';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'ACF Repeater (URL)', 'addoncraft-repeater-for-elementor-acf' );
	}

	/**
	 * Get tag group.
	 */
	public function get_group() {
		return 'REPEFOEL-dynamic-tag';
	}

	/**
	 * Get all repeater fields from ACF.
	 *
	 * @return array
	 */
	public function get_all_repeater() {
		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return [];
		}

		$field_groups        = acf_get_field_groups();
		$all_repeater_fields = [];

		if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
			return [];
		}

		$i = 0;


		foreach ( $field_groups as $group ) {
			if ( empty( $group['ID'] ) ) {
				continue;
			}

			$fields = acf_get_fields( $group['ID'] );

			if ( empty( $fields ) || ! is_array( $fields ) ) {
				continue;
			}

			foreach ( $fields as $field ) {
				if ( isset( $field['type'] ) && 'repeater' === $field['type'] ) {
					$title      = isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '';
					$sub_fields = isset( $field['sub_fields'] ) ? (array) $field['sub_fields'] : [];

					$all_repeater_fields[ $i ]['label'] = $title;

					if ( ! empty( $sub_fields ) ) {
						foreach ( $sub_fields as $sub_field ) {
							if ( isset( $sub_field['type'], $sub_field['name'], $sub_field['label'] ) && 'url' == $sub_field['type'] ) {
								$all_repeater_fields[ $i ]['options'][ sanitize_key( $sub_field['name'] ) ] = sanitize_text_field( $sub_field['label'] );
							}
						}
					}

					$i++;
				}
			}
		}

		return $all_repeater_fields;
	}

	/**
	 * Get categories.
	 */
	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::URL_CATEGORY ];
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->add_control(
			'REPEFOEL_repeater_select_field',
			[
				'label'   => esc_html__( 'Choose a repeater field', 'addoncraft-repeater-for-elementor-acf' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'groups'  => $this->get_all_repeater(),
				'default' => '',
			]
		);
	}

	/**
	 * Render output.
	 */
	public function render() {
		$acf_repeater_name = $this->get_settings( 'REPEFOEL_repeater_select_field' );

		if ( empty( $acf_repeater_name ) ) {
			return;
		}

		$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );

		if ( ! empty( $document ) ) {
			
			$document_name = $document->get_name() ?? '';
			
			if ( $document_name == 'REPEFOEL_repeater' ) {
				$repeat_field  = $document->get_settings( 'REPEFOEL_repeater_field' );
				$preview_post  = $document->get_settings( 'REPEFOEL_preview_post' );

				if ( ! empty( $repeat_field ) && ! empty( $preview_post ) && function_exists( 'get_field' ) ) {

					$get_data = get_field( $repeat_field, absint( $preview_post ) );

					if ( ! empty( $get_data ) && is_array( $get_data ) && ! empty( $get_data[0] ) ) {
						$value = isset( $get_data[0][ $acf_repeater_name ] ) ? $get_data[0][ $acf_repeater_name ] : '';

						if ( ! empty( $value ) ) {
							echo wp_kses_post( $value ); // Safe output.
							return;
						}
					} 
				}

				echo esc_url( get_permalink( get_the_ID() ) );
				return;

			} else {
				printf( '#repefoel_url_%1$s', wp_kses_post( $acf_repeater_name ) );
			}

		}		

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
