<?php
/**
 * Elementor Dynamic Tag: ACF Repeater Image
 *
 * @package REPEFOEL ElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_ACF_Image extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-acf-image';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'Image Field', 'addoncraft-repeater-for-elementor-acf' );
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
		return [ \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY ];
	}

	/**
	 * Get all repeater image subfields from ACF.
	 *
	 * @return array
	 */
	public function get_all_image_fields() {
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

				if ( isset( $field['type'] ) && in_array( $field['type'], ['image' ] )  ) {
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
			'REPEFOEL_image_select_field',
			[
				'label'   => esc_html__( 'ACF Field', 'addoncraft-repeater-for-elementor-acf' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'groups'  => $this->get_all_image_fields(),
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

		$acf_image_name = $this->get_settings( 'REPEFOEL_image_select_field' );
		$field_value = get_field( $acf_image_name ) ?? '';

		if ( empty( $acf_image_name ) || empty($field_value) ) {
			return [
				'id'  => $this->get_attachment_placeholder_img(),
				'url' => esc_url_raw( home_url( '/wp-content/plugins/elementor/assets/images/placeholder.png' ) ),
			];
		}

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

	/**
	 * Supported field types.
	 *
	 * @return array
	 */
	public function get_supported_fields() {
		return [ 'image' ];
	}
}
