<?php
/**
 * Elementor Dynamic Tag: ACF Repeater
 *
 * @package REPEFOEL ElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_SITE_TITLE extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-site-title';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'Site Title', 'addoncraft-repeater-for-elementor-acf' );
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
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}


	/**
	 * Render output.
	 */
	public function render() {
		echo esc_html( get_bloginfo( 'name' ) ?? '' );
		return;
	}

	/**
	 * Supported field types.
	 *
	 * @return array
	 */
	public function get_supported_fields() {
		return [
			'text',
			'textarea',
		];
	}
}
