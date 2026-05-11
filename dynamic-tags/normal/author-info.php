<?php
/**
 * Elementor Dynamic Tag: ACF Repeater
 *
 * @package REPEFOEL ElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_Author_Info extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-author-info';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'Author Info', 'addoncraft-repeater-for-elementor-acf' );
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
	 * Register repeater control.
	 */
	protected function register_controls() {

        $this->add_control(
            'REPEFOEL_author_option',
            [
                'label' => __( 'ACF Field', 'addoncraft-repeater-for-elementor-acf' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options'  => [
					'display_name' => 'Display Name',
					'user_login' => 'Username',
					'nickname' => 'Nickname',
					'first_name' => 'First Name',
					'last_name' => 'Last Name',
					'user_description' => 'Author Details',
					'user_email' => 'Email'
				],
				'default' => '',
            ]
        );
    }

	/**
	 * Render output.
	 */
	public function render() {
		
		$REPEFOEL_author = $this->get_settings( 'REPEFOEL_author_option' );

		if ( empty($REPEFOEL_author) ) {
			return;
		}

		echo esc_html( get_the_author_meta( $REPEFOEL_author ) );
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
