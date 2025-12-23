<?php
/**
 * Elementor Dynamic Tag: ACF Repeater Image
 *
 * @package REPEFOEL ElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_ACF_Repeater_Image extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-repeater-image';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'ACF Repeater (Image)', 'addoncraft-repeater-for-elementor-acf' );
	}

	/**
	 * Get tag group.
	 */
	public function get_group() {
		return 'REPEFOEL-dynamic-tag';
	}

	/**
	 * Get all repeater image subfields from ACF.
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
							if ( isset( $sub_field['type'], $sub_field['name'], $sub_field['label'] ) && 'image' === $sub_field['type'] ) {
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
		return [ \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ];
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

	public function get_attachment_placeholder_img() {

		$attachments = get_posts(array(
		    'post_type' => 'attachment',
		    'posts_per_page' => 1,
		    'post_mime_type' => array('image/jpeg', 'image/png', 'image/jpg'),
		    'fields' => 'ids'
		));	

		$attachment_id = 0;

		if ($attachments) {
		    $attachment_id = $attachments[0];
		}

		return $attachment_id;
	}

	/**
	 * Return image data.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_value( array $options = [] ) {

		$acf_repeater_name = $this->get_settings( 'REPEFOEL_repeater_select_field' );

		if ( empty( $acf_repeater_name ) ) {
			return [
				'id'  => $this->get_attachment_placeholder_img(),
				'url' => esc_url_raw( home_url( '/wp-content/plugins/elementor/assets/images/placeholder.png' ) ),
			];
		}

		$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );

		if ( !empty( $document ) ) {

			$document_name = $document->get_name() ?? '';

			if ( $document_name == 'REPEFOEL_repeater' ) {

				$image_data = [
					'id'  => $this->get_attachment_placeholder_img(),
					'url' => esc_url_raw( home_url( '/wp-content/plugins/elementor/assets/images/placeholder.png' ) ),
				];

				$repeat_field  = $document->get_settings( 'REPEFOEL_repeater_field' );
				$preview_post  = $document->get_settings( 'REPEFOEL_preview_post' );

				if ( ! empty( $repeat_field ) && ! empty( $preview_post ) && function_exists( 'get_field' ) ) {

					$get_data = get_field( $repeat_field, absint( $preview_post ) );
					
					if ( ! empty( $get_data ) && is_array( $get_data ) && ! empty( $get_data[0] ) ) {
						$field_value = $get_data[0][ $acf_repeater_name ] ?? '';

						// Case 1: Array with URL.
						if ( is_array( $field_value ) && isset( $field_value['url'] ) ) {
							$image_data = [
								'id'  => isset( $field_value['id'] ) ? absint( $field_value['id'] ) : null,
								'url' => esc_url_raw( $field_value['url'] ),
							];

						// Case 2: Numeric attachment ID.
						} elseif ( is_numeric( $field_value ) ) {
							$image_data = [
								'id'  => absint( $field_value ),
								'url' => esc_url_raw( wp_get_attachment_url( $field_value ) ),
							];

						// Case 3: Direct URL string.
						} elseif ( is_string( $field_value ) ) {
							$image_data['id'] = absint( attachment_url_to_postid($field_value) ) ?? 0;
							$image_data['url'] = esc_url_raw( $field_value );
						}

						return $image_data;
					} 
				}

				return $image_data;
			} else {

				return [
					'id'  => null,
					'url' => esc_url_raw( '#' . sprintf( '{repefoel_repeat_img_%1$s}', sanitize_key( $acf_repeater_name ) ) ),
				];
			}

		}

		// Fallback.
		return [
			'id'  => $this->get_attachment_placeholder_img(),
			'url' => esc_url_raw( home_url( '/wp-content/plugins/elementor/assets/images/placeholder.png' ) ),
		];
	}

	/**
	 * Supported field types.
	 *
	 * @return array
	 */
	public function get_supported_fields() {
		return [ 'image' ];
	}
}
