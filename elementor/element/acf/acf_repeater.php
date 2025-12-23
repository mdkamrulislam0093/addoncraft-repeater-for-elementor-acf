<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class REPEFOEL_ACF_Repeater extends \Elementor\Widget_Base {
    public function get_name() {
        return 'REPEFOEL_widget_repeater';
    }

    public function get_title() {
        return __('Repeater', 'addoncraft-repeater-for-elementor-acf');
    }

    public function get_icon() {
        return 'REPEFOEL-icon';
    }
    
    public function get_categories() {
        return [ 'REPEFOEL_category_advanced' ];
    }

    /**
     * Get all Elementor templates of type REPEFOEL_repeater
     */
    public function get_all_repeater_template() {
        $templates = get_posts([
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_elementor_template_type',
                    'value' => 'REPEFOEL_repeater',
                ]
            ],
        ]);
        

        $options = [];

        if (empty($templates)) {
            $options[''] = esc_html__('No templates found. Create templates in Elementor Templates section.', 'addoncraft-repeater-for-elementor-acf');
        } else {
            $options[''] = esc_html__('Select Template', 'addoncraft-repeater-for-elementor-acf');
            foreach ($templates as $template) {
                $options[$template->ID] = esc_html($template->post_title);
            }
        }

        return $options;
    }

    /**
     * Get only the repeater field names and labels
     * Cached to improve performance
     */
    public function get_only_repeater_name() {
        // Check if ACF function exists
        if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
            return [];
        }

        $all_repeater_fields = wp_cache_get('repefoel_acf_repeater_fields');

        if ( $all_repeater_fields === false ) {
            $all_repeater_fields = [];
            
            $field_groups = acf_get_field_groups();

            if ( !empty($field_groups) ) {
                foreach ($field_groups as $group) {
                    $fields = acf_get_fields($group['ID']);
                    if (!empty($fields)) {
                        foreach ($fields as $field) {
                            if ($field['type'] === 'repeater') {
                                $all_repeater_fields[$field['name']] = esc_html($field['label']);
                            }
                        }
                    }
                }
            }
            wp_cache_set('repefoel_acf_repeater_fields', $all_repeater_fields, '', 3600 );
        }

        return $all_repeater_fields;
    }

    public function get_style_depends() {
        return ['repefoel-rp-style'];
    }

    /**
     * Register controls
     */
    protected function register_controls() {
        $this->start_controls_section(
          'content_section',
          [
            'label' => 'Content',
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
          ]
        );

        $this->add_control(
            'repefoel_template_select',
            [
                'label' => esc_html__( 'ACF Repeater', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => 'repeater_template_select',
                'options' => $this->get_all_repeater_template(),
            ]
        );

        $this->add_control(
            'REPEFOEL_repeater_field',
            [
                'label' => __('Repeater Field', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_only_repeater_name(),
                'default' => '',
            ]
        );

        $this->add_control(
            'REPEFOEL_apply_preview',
            [
                'label' => esc_html__( 'Refresh Repeater', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::BUTTON,
                'separator' => 'before',
                'button_type' => 'success',
                'show_label' => false,
                'text' => esc_html__( 'Refresh Repeater', 'addoncraft-repeater-for-elementor-acf' ),
                'event' => 'REPEFOEL:apply_preview'
            ]
        );

        $this->end_controls_section();


        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout', 'addoncraft-repeater-for-elementor-acf'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => __('1', 'addoncraft-repeater-for-elementor-acf'),
                    '2' => __('2', 'addoncraft-repeater-for-elementor-acf'),
                    '3' => __('3', 'addoncraft-repeater-for-elementor-acf'),
                    '4' => __('4', 'addoncraft-repeater-for-elementor-acf'),
                    '5' => __('5', 'addoncraft-repeater-for-elementor-acf'),
                    '6' => __('6', 'addoncraft-repeater-for-elementor-acf'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .repefoel-rp-row' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_control(
            'grid_gap',
            [
                'label' => __('Gap Between Items', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .repefoel-rp-row' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'repeater_section_query',
            [
                'label' => __( 'Query', 'addoncraft-repeater-for-elementor-acf' ),
            ]
        );

        // Query Source
        $this->add_control(
            'repeater_query_source',
            [
                'label'   => __( 'ACF Repeater Source', 'addoncraft-repeater-for-elementor-acf' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'current',
                'options' => [
                    'current'   => __( 'Current', 'addoncraft-repeater-for-elementor-acf' ),
                    'manual'    => __( 'Manual', 'addoncraft-repeater-for-elementor-acf' ),
                ],
            ]
        );


        // Manual Selection
        $this->add_control(
            'repeater_manual_posts',
            [
                'label'       => __( 'Select Posts', 'addoncraft-repeater-for-elementor-acf' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $this->get_all_posts(),
                'multiple'    => false,
                'label_block' => true,
                'condition'   => [
                    'repeater_query_source' => 'manual',
                ],
            ]
        );

        $this->end_controls_section();

    }

    private function get_all_posts() {
        $posts = get_posts(
            [
                'post_type'      => 'any',
                'posts_per_page' => -1,
            ]
        );

        $options = [];

        foreach ( $posts as $post ) {
            $options[ $post->ID ] = $post->post_title;
        }

        return $options;
    }


    /**
     * Render Elementor template
     */
    protected function render_template($template_id) {
        if ( ! $template_id || !class_exists( '\Elementor\Plugin' ) ) {
            return '';
        }

        return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($template_id, true);
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
     * Render the widget output
     */
    protected function render() {

        $settings = $this->get_settings_for_display();
        $template_id = isset( $settings['repefoel_template_select'] ) ? (int) $settings['repefoel_template_select'] : 0;
        $repeater_field = isset( $settings['REPEFOEL_repeater_field'] ) ? sanitize_text_field( $settings['REPEFOEL_repeater_field'] ) : '';
        $repeater_query_source = isset( $settings['repeater_query_source'] ) ? sanitize_text_field( $settings['repeater_query_source'] ) : '';


        if ( empty($repeater_query_source) ) {
            printf(
                '<div class="elementor-alert elementor-alert-info">%s</div>',
                esc_html__( 'Please select query source in the widget settings.', 'addoncraft-repeater-for-elementor-acf' )
            );
            return;
        }

        if (empty($template_id)) {
            printf(
                '<div class="elementor-alert elementor-alert-info">%s</div>',
                esc_html__( 'Please select a template in the widget settings.', 'addoncraft-repeater-for-elementor-acf' )
            );
            return;
        }

        if (empty($repeater_field)) {
            printf(
                '<div class="elementor-alert elementor-alert-info">%s</div>',
                esc_html__( 'Please select a repeater field in the widget settings.', 'addoncraft-repeater-for-elementor-acf' )
            );
            return;
        }
        

        $query_post_id = 0;

        if ( $repeater_query_source == 'current' ) {
            $query_post_id = get_the_ID();

        } elseif ( $repeater_query_source == 'manual' ) {
            $repeater_manual_posts = isset( $settings['repeater_manual_posts'] ) ? sanitize_text_field( $settings['repeater_manual_posts'] ) : 0;
            $query_post_id = $repeater_manual_posts;

        }

        if ( empty($query_post_id) ) {
            $query_post_id = get_the_ID();
        }

        $template_content = $this->render_template($template_id);
        $repeater_val = get_field($repeater_field, $query_post_id);

        if ( empty($repeater_val) || ! is_array( $repeater_val ) ) {
            return;
        }

        // $allowed_tags = wp_kses_allowed_html( 'post' );
        // $allowed_tags['style'] = [];

        ob_start();
        ?>

        <div class="repefoel_loop_repeater_main">
            <div class="repefoel-rp-row">
                <?php
                    foreach ($repeater_val as $repeat) :
                        if ( is_array( $repeat ) ) :
                ?>

                <div class="repefoel-rp-item">
                    <div class="repefoel-rp-inner">
                        <?php 
                            echo wp_kses( $this->parse_template_content($template_content, $repeat), $this->allow_all_html_tags() );
                        ?>
                    </div>
                </div>
                <?php 
                    endif;
                endforeach; ?>
            </div>
        </div>
        <?php 


        echo wp_kses(ob_get_clean(), $this->allow_all_html_tags());
    }

  protected function parse_template_content($template, $repeat) {
    if ( empty( $template ) || empty( $repeat ) || ! is_array( $repeat ) ) {
        return $template;
    }

    /*
    * Preg Match for Text Field
    */
    preg_match_all('/\{REPEFOEL\|([^}]+)\}/', $template, $matches);

    if ( !empty( $matches[1] ) ) {
        foreach ($matches[1] as $field_group) {
            $extract_field = explode("|", $field_group);
            $field_name = $extract_field[0] ?? '';
            $strip_tags = $extract_field[1] ?? '';


            $clean_field_name = sanitize_text_field( $field_name );
            $value = isset($repeat[$clean_field_name]) ? $repeat[$clean_field_name] : '';

            if ( is_array( $value ) ) {
                $value = ''; // Don't output arrays
            } else {
                $value = ($strip_tags == 'y') ? wp_strip_all_tags($value) : wp_kses_post( $value );
            }

            $template = str_replace('{REPEFOEL|' . $field_name . '|'. $strip_tags .'}', $value, $template);
        }
    }

    /*
    * Preg Match for Image Field
    */
    preg_match_all('/src="(#repefoel_repeat_img_)([^"]*)"/', $template, $img_matches);
    if ( !empty( $img_matches[2] ) ) {
        foreach ( $img_matches[2] as $field_name) {
            $clean_field_name = sanitize_text_field( $field_name );
            $image_id = '';

            if ( isset($repeat[$clean_field_name] ) ) {
                $image_data = $repeat[ $clean_field_name ];
    
                if ( is_array( $image_data ) && isset( $image_data['id'] ) ) {
                    $image_id = (int) $image_data['id'];
                } elseif ( is_numeric( $image_data ) ) {
                    $image_id = (int) $image_data;
                } elseif ( is_string( $image_data ) ) {
                    $image_id = attachment_url_to_postid( esc_url_raw( $image_data ) );
                }

            }

            $image_url = $image_id ? esc_url(wp_get_attachment_url($image_id)) : '';

           $template = str_replace('#repefoel_repeat_img_' . $field_name, $image_url, $template);
        }
    }

    /*
    * Preg Match for URL Field
    */
    preg_match_all('/href="(#repefoel_url_)([^"]*)"/', $template, $url_matches);

    if ( !empty( $url_matches[2] ) ) {
        foreach ( $url_matches[2] as $field_name) {
            $clean_field_name = sanitize_text_field( $field_name );
            $url = ! empty( $repeat[ $clean_field_name ] ) ? esc_url( $repeat[ $clean_field_name ] ) : '#';
            $template = str_replace( '#repefoel_url_' . $field_name, $url, $template );
        }
    }

    return $template;
  }
    
    protected function content_template() {}    
}

