<?php
/**
 * Elementor Dynamic Tag: ACF Repeater Image
 *
 * @package REPEFOEL ElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_Author_Profile extends \Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-author-profile';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'Author Profile Image', 'addoncraft-repeater-for-elementor-acf' );
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
	 * Return image data.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_value( array $options = [] ) {

		$profile_url = get_avatar_url( get_current_user_id() ) ?? '';
		return [
			'id'  => 0,
			'url' => $profile_url,
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
