<?php
/**
 * Elementor Dynamic Tag: ACF Repeater
 *
 * @package REPEFOEL ElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class REPEFOEL_ACF_Text extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get tag unique name.
	 */
	public function get_name() {
		return 'REPEFOEL-acf-text';
	}

	/**
	 * Get tag title.
	 */
	public function get_title() {
		return esc_html__( 'Text Field', 'addoncraft-repeater-for-elementor-acf' );
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

	public function get_all_text_fields() {
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

				if ( isset( $field['type'] ) && in_array( $field['type'], ['text', 'number', 'email', 'textarea', 'wysiwyg' ] )  ) {
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
	 * Register repeater control.
	 */
	protected function register_controls() {

        $this->add_control(
            'REPEFOEL_acf_field_key',
            [
                'label' => __( 'ACF Field', 'addoncraft-repeater-for-elementor-acf' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'groups'  => $this->get_all_text_fields(),
				'default' => '',
            ]
        );

		$this->add_control(
			'REPEFOEL_strip_tags',
			[
				'label' => esc_html__( 'Strip Content (Only Text)', 'addoncraft-repeater-for-elementor-acf' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'addoncraft-repeater-for-elementor-acf' ),
				'label_off' => esc_html__( 'Hide', 'addoncraft-repeater-for-elementor-acf' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);        
	}

    protected function allow_all_html_tags() {
        $allowed_tags_clean = wp_kses_allowed_html('post');
        $allowed_tags_clean['style'] = array();
        $allowed_tags_clean['script'] = array();
        $svg_args = array(
            'svg'   => array(
                'class'           => true,
                'aria-hidden'     => true,
                'aria-labelledby' => true,
                'role'            => true,
                'xmlns'           => true,
                'width'           => true,
                'height'          => true,
                'viewbox'         => true // <= Must be lower case!
            ),
            'g'     => array( 'fill' => true ),
            'title' => array( 'title' => true ),
            'path'  => array( 
                'd'               => true, 
                'fill'            => true  
            )
        );

        return array_merge( $allowed_tags_clean, $svg_args );         
    }	

	/**
	 * Render output.
	 */
	public function render() {

		$acf_strip_tags = $this->get_settings( 'REPEFOEL_strip_tags' ) == 'yes' ? 'y' : '';

		
		$field = $this->get_settings( 'REPEFOEL_acf_field_key' );

        if ( ! $field ) return;

		$value = ($acf_strip_tags == 'y') ? wp_strip_all_tags( get_field( $field ) ) : wp_kses_post( get_field( $field ) );

        if ( $value ) {
            echo wp_kses( $value, $this->allow_all_html_tags());
        }

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
			'number',
			'email',
			'password',
		];
	}
}
