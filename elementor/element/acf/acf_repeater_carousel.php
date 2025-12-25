<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class REPEFOEL_ACF_Repeater_Carousel extends \Elementor\Widget_Base {
    public function get_name() {
        return 'REPEFOEL_widget_repeater_carousel';
    }

    public function get_title() {
        return __('Repeater Slider', 'addoncraft-repeater-for-elementor-acf');
    }

    public function get_icon() {
        return 'REPEFOEL-icon';
    }
    
    public function get_categories() {
        return [ 'REPEFOEL_category_advanced' ];
    }

    /**
     * Get style dependencies
     *
     * @return array Style dependencies
     */        
    public function get_style_depends() {
        return [ 'swiper', 'repefoel-rp-style' ];
    }

    /**
     * Get script dependencies
     *
     * @return array Script dependencies
     */
    public function get_script_depends() {
        return [ 'swiper' ];
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

        $this->register_settings_controls();

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

    /**
     * Register settings controls
     */
    private function register_settings_controls() {

        $this->start_controls_section(
            'repeater_slider_settings_section',
            [
                'label' => esc_html__( 'Slider Settings', 'addoncraft-repeater-for-elementor-acf' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $slides_to_show = range( 1, 10 );
        $slides_to_show = array_combine( $slides_to_show, $slides_to_show );

        $this->add_responsive_control(
            'repeater_slider_slides_to_show',
            [
                'label' => esc_html__( 'Slides to Show', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'Default', 'addoncraft-repeater-for-elementor-acf' ),
                ] + $slides_to_show,
                'default' => 3,
                'frontend_available' => true,
                'render_type' => 'template',
            ]
        );

        $this->add_responsive_control(
            'repeater_slider_slider_space_between',
            [
                'label' => esc_html__( 'Space Between', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 15,
                ],
            ]
        );

        $this->add_control(
            'repeater_slider_autoplay',
            [
                'label' => __('Autoplay', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'addoncraft-repeater-for-elementor-acf'),
                'label_off' => __('No', 'addoncraft-repeater-for-elementor-acf'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'repeater_slider_pause_on_hover',
            [
                'label' => esc_html__( 'Pause on Hover', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
                'label_off' => esc_html__( 'No', 'addoncraft-repeater-for-elementor-acf' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'repeater_slider_autoplay' => 'yes',
                ],
                'render_type' => 'none',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'repeater_slider_pause_on_interaction',
            [
                'label' => esc_html__( 'Pause on Interaction', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
                'label_off' => esc_html__( 'No', 'addoncraft-repeater-for-elementor-acf' ),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'repeater_slider_autoplay' => 'yes',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'repeater_slider_autoplay_speed',
            [
                'label' => __('Autoplay Speed (ms)', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3000,
                'condition' => [
                    'repeater_slider_autoplay' => 'yes',
                ],
            ]
        );


        $this->add_control(
            'repeater_slider_infinite_loop',
            [
                'label' => __('Infinite Loop', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'addoncraft-repeater-for-elementor-acf'),
                'label_off' => __('No', 'addoncraft-repeater-for-elementor-acf'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'repeater_slider_navigation',
            [
                'label' => esc_html__( 'Navigation', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'both',
                'options' => [
                    'both' => esc_html__( 'Arrows and Dots', 'addoncraft-repeater-for-elementor-acf' ),
                    'arrows' => esc_html__( 'Arrows', 'addoncraft-repeater-for-elementor-acf' ),
                    'dots' => esc_html__( 'Dots', 'addoncraft-repeater-for-elementor-acf' ),
                    'none' => esc_html__( 'None', 'addoncraft-repeater-for-elementor-acf' ),
                ],
                'frontend_available' => true,
            ]
        );

        $this->end_controls_section();

        $this->register_layout_style_controls();
        $this->register_navigation_style_controls();
        $this->register_pagination_style_controls();
    }

    /**
     * Register Content style controls
     */
    public function register_layout_style_controls() {

        $this->start_controls_section(
            'acf_repeater__content_style',
            [
                'label' => esc_html__('Inner Layout', 'addoncraft-repeater-for-elementor-acf'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_responsive_control(
            'acf_repeater_container_padding',
            [
                'label' => esc_html__( 'Padding', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'selectors' => [
                    '{{WRAPPER}} .repefoel_acf_repeater_rs_sliders' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }


    /**
     * Register Navigation style controls
     */
    private function register_navigation_style_controls() {
        $this->start_controls_section(
            'acf_repeater_slider_navigation_style',
            [
                'label' => esc_html__('Navigation', 'addoncraft-repeater-for-elementor-acf'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,  
            ]
        );



        $this->start_controls_tabs(
            'acf_repeater_rs_navigation_orientation_tabs'
        );

            $this->start_controls_tab(
                'acf_repeater_rs_navigation_left_orient',
                [
                    'label' => esc_html__( 'Previous', 'addoncraft-repeater-for-elementor-acf' ),
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigation_left_x_position',
                [
                    'label' => esc_html__( 'Horizontal', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'atc_ss_x_left' => [
                            'title' => __('Left', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-h-align-left',
                        ],
                        'atc_ss_x_right' => [
                            'title' => __('Right', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-h-align-right',
                        ],
                        
                    ],
                    'default' => 'atc_ss_x_left',
                    'toggle' => true,
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigaiton_left_x_position_offset',
                [
                    'label' => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'range' => [
                        'px' => [
                            'min' => -500,
                            'max' => 500,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => -100,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 30,
                    ]
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigation_position_horizontal_hr',
                [
                    'type' => \Elementor\Controls_Manager::DIVIDER,
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigation_left_y_position',
                [
                    'label' => esc_html__( 'Vertical', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'atc_ss_y_top' => [
                            'title' => __('Top', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-v-align-top',
                        ],
                        'atc_ss_y_center' => [
                            'title' => __('Center', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-v-align-middle',
                        ],
                        'atc_ss_y_bottom' => [
                            'title' => __('Bottom', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-v-align-bottom',
                        ],
                    ],
                    'default' => 'atc_ss_y_center',
                    'toggle' => true,
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigaiton_left_y_position_offset',
                [
                    'label' => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'range' => [
                        'px' => [
                            'min' => -500,
                            'max' => 500,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => -100,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 0,
                    ],
                ]
            );



            $this->end_controls_tab();

            $this->start_controls_tab(
                'acf_repeater_rs_navigation_right_orient',
                [
                    'label' => esc_html__( 'Next', 'addoncraft-repeater-for-elementor-acf' ),
                ]
            );


            $this->add_control(
                'acf_repeater_rs_navigation_right_x_position',
                [
                    'label' => esc_html__( 'Horizontal', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'atc_ss_x_left' => [
                            'title' => __('Left', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-h-align-left',
                        ],
                        'atc_ss_x_right' => [
                            'title' => __('Right', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-h-align-right',
                        ],
                        
                    ],
                    'default' => 'atc_ss_x_right',
                    'toggle' => true,
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigaiton_right_x_position_offset',
                [
                    'label' => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'range' => [
                        'px' => [
                            'min' => -500,
                            'max' => 500,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => -100,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => -30,
                    ]
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigation_position_left_horizontal_hr',
                [
                    'type' => \Elementor\Controls_Manager::DIVIDER,
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigation_right_y_position',
                [
                    'label' => esc_html__( 'Vertical', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'atc_ss_y_top' => [
                            'title' => __('Top', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-v-align-top',
                        ],
                        'atc_ss_y_center' => [
                            'title' => __('Center', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-v-align-middle',
                        ],
                        'atc_ss_y_bottom' => [
                            'title' => __('Bottom', 'addoncraft-repeater-for-elementor-acf'),
                            'icon' => 'eicon-v-align-bottom',
                        ],
                    ],
                    'default' => 'atc_ss_y_center',
                    'toggle' => true,
                ]
            );

            $this->add_control(
                'acf_repeater_rs_navigaiton_right_y_position_offset',
                [
                    'label' => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ 'px', '%' ],
                    'range' => [
                        'px' => [
                            'min' => -500,
                            'max' => 500,
                            'step' => 1,
                        ],
                        '%' => [
                            'min' => -100,
                            'max' => 100,
                        ],
                    ],
                    'default' => [
                        'unit' => 'px',
                        'size' => 0,
                    ],
                ]
            );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'acf_repeater_rs_navigation_position_hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'acf_repeater_rs_navigation_background',
                'types' => [ 'classic' ],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev, {{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next',
            ]
        );

        $this->add_control(
            'acf_repeater_rs_navigation_color',
            [
                'label' => esc_html__( 'Color', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'acf_repeater_rs_navigaiton_size',
            [
                'label' => esc_html__( 'Size', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'devices' => [ 'desktop', 'tablet', 'mobile' ],
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev' => 'width: {{SIZE}}px; height: {{SIZE}}px; font-size: calc({{SIZE}}px / 1.5);',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next' => 'width: {{SIZE}}px; height: {{SIZE}}px; font-size: calc({{SIZE}}px / 1.5);',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next:after, {{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev:after' => 'font-size: calc({{SIZE}}px / 1.5);',
                ],
            ]
        );

        $this->add_responsive_control(
            'acf_repeater_rs_navigaiton_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register Pagination style controls
     */
    private function register_pagination_style_controls() {
        $this->start_controls_section(
            'acf_repeater_slider_pagination_style',
            [
                'label' => esc_html__('Pagination', 'addoncraft-repeater-for-elementor-acf'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,        
            ]
        );



        $this->start_controls_tabs(
            'acf_repeater_rs_pagination_background_tabs'
        );

        $this->start_controls_tab(
            'acf_repeater_rs_pagination_background_normal',
            [
                'label' => esc_html__( 'Normal', 'addoncraft-repeater-for-elementor-acf' ),
            ]
        );

        $this->add_control(
            'acf_repeater_rs_pagination_normal_background',
            [
                'label' => esc_html__( 'Color', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'acf_repeater_rs_pagination_background_active',
            [
                'label' => esc_html__( 'Active', 'addoncraft-repeater-for-elementor-acf' ),
            ]
        );

        $this->add_control(
            'acf_repeater_rs_pagination_active_background',
            [
                'label' => esc_html__( 'Color', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet:hover' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_control(
            'acf_repeater_rs_pagination_size',
            [
                'label' => esc_html__( 'Size', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'devices' => [ 'desktop', 'tablet', 'mobile' ],
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet' => 'height: {{SIZE}}px; width: {{SIZE}}px;',
                ],
            ]
        );

        $this->add_control(
            'acf_repeater_rs_pagination_gap',
            [
                'label' => esc_html__( 'Gap', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SLIDER,            
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet' => 'margin: 0 {{SIZE}}px;',
                ],
            ]
        );


        $this->add_control(
            'acf_repeater_rs_pagination_space',
            [
                'label' => esc_html__( 'Offset Y', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],                
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination' => 'bottom: {{SIZE}}px;',
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
        
        $slider_id = 'repefoel-sliders-' . $this->get_id();
        $data_attrs = $this->get_slider_data_attributes($settings);
        $addition_options = $this->additional_options_slider($settings);
        $slider_settings = $this->get_slider_settings($settings, $slider_id);

        ob_start();
        ?>

        <div class="repefoel_loop_repeater_main" id="<?php echo esc_attr($slider_id); ?>">

                <div class="repefoel_sliders-wrapper" data-nav="<?php echo esc_attr( $settings['repeater_slider_navigation'] ); ?>">
                    <div class="repefoel_acf_repeater_rs_sliders swiper" <?php echo wp_kses_post($data_attrs); ?> data-swiper_settings='<?php echo json_encode( $slider_settings, JSON_HEX_APOS | JSON_HEX_QUOT ); ?>'>
                        <div class="swiper-wrapper">
                            <?php
                                foreach ($repeater_val as $repeat) :
                                    if ( is_array( $repeat ) ) :
                            ?>
                                <div class="swiper-slide">
                                    <div class="repefoel-slide-wrap">

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
                    <div class="acf_repeater_rs_swiper_product_sliders-nav">
                        <?php $this->render_slider_controls($addition_options); ?>                    
                    </div>
                </div>

        </div>
        <?php 
        $this->render_slider_script(); 


        echo ob_get_clean();
    }

    /**
     * Addtional options For Slider
     */
    private function additional_options_slider ($settings) {
        return [

            'show_navigation' => $settings['repeater_slider_navigation'],
            'navigation_left_x_position' => $settings['acf_repeater_rs_navigation_left_x_position'],
            'navigation_left_y_position' => $settings['acf_repeater_rs_navigation_left_y_position'],
            'navigation_left_x_offset' => $settings['acf_repeater_rs_navigaiton_left_x_position_offset'],
            'navigation_left_y_offset' => $settings['acf_repeater_rs_navigaiton_left_y_position_offset'],
            'navigation_right_x_position' => $settings['acf_repeater_rs_navigation_right_x_position'],
            'navigation_right_y_position' => $settings['acf_repeater_rs_navigation_right_y_position'],
            'navigation_right_x_offset' => $settings['acf_repeater_rs_navigaiton_right_x_position_offset'],
            'navigation_right_y_offset' => $settings['acf_repeater_rs_navigaiton_right_y_position_offset'],

        ];
    }

    private function render_slider_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                if (typeof Swiper === 'undefined') {
                    console.log('Swiper library not loaded');
                    return;
                }

                let current_slider = $("#repefoel-sliders-<?php echo esc_js($this->get_id()); ?>").find('.swiper');

                if ( current_slider.length ) {
                    try {
                      const opts = JSON.parse(current_slider.attr('data-swiper_settings') || '{}');
                      console.log(opts);
                      new Swiper('#repefoel-sliders-<?php echo esc_js($this->get_id()); ?> .swiper', opts);
                    } catch (e) {
                      console.error('Invalid data-swiper JSON', e);
                    }
                }

            });


        </script>

        <?php 
    }

    /**
     * Render slider controls
     */
    private function render_slider_controls($addition_options) {

         $show_navigation = isset($addition_options['show_navigation']) ? $addition_options['show_navigation'] : '';


        if ( in_array($show_navigation, ['arrows', 'both']) ):

            $nav_left_pos = [ $addition_options['navigation_left_x_position'], $addition_options['navigation_left_y_position'] ];

            $nav_right_pos = [ $addition_options['navigation_right_x_position'], $addition_options['navigation_right_y_position'] ];

            $nav_left_pos_x_offset_size = $addition_options['navigation_left_x_offset']['size'] ?? 0;
            $nav_left_pos_x_offset_unit = $addition_options['navigation_left_x_offset']['unit'] ?? 'px';
            $nav_left_pos_y_offset_size = $addition_options['navigation_left_y_offset']['size'] ?? 0;
            $nav_left_pos_y_offset_unit = $addition_options['navigation_left_y_offset']['unit'] ?? 'px';
            
            $nav_right_pos_x_offset_size = $addition_options['navigation_right_x_offset']['size'] ?? 0;
            $nav_right_pos_x_offset_unit = $addition_options['navigation_right_x_offset']['unit'] ?? 'px';
            $nav_right_pos_y_offset_size = $addition_options['navigation_right_y_offset']['size'] ?? 0;
            $nav_right_pos_y_offset_unit = $addition_options['navigation_right_y_offset']['unit'] ?? 'px';

            $nav_left_classes = array_filter($nav_left_pos);
            $nav_right_classes = array_filter($nav_right_pos);

        ?> 
            <div class="swiper-button-prev <?php echo esc_attr( implode(' ', $nav_left_classes) ); ?>" style=" transform: translate(<?php echo $nav_left_pos_x_offset_size . $nav_left_pos_x_offset_unit; ?>, <?php echo $nav_left_pos_y_offset_size . $nav_left_pos_y_offset_unit; ?>);"></div>

            <div class="swiper-button-next <?php echo esc_attr( implode(' ', $nav_right_classes) ); ?>" style="transform: translate(<?php echo esc_attr( $nav_right_pos_x_offset_size . $nav_right_pos_x_offset_unit ); ?>, <?php echo esc_attr( $nav_right_pos_y_offset_size . $nav_right_pos_y_offset_unit ); ?>);"></div>

        <?php endif;
        
        if ( in_array($addition_options['show_navigation'], ['dots', 'both']) ): ?>
            <div class="swiper-pagination"></div>
        <?php endif;
    }    

    /**
     * Get sanitized slider settings for Swiper configuration
     *
     * @param array $settings Raw settings array from user input
     * @return array Sanitized settings array for Swiper initialization
     * @since 1.0.0
     */

    private function get_slider_settings($settings, $slider_id){

        // Validate input parameter
        if ( ! is_array( $settings ) ) {
            $settings = array();
        }

        // Slider Slider Responsive
        $default_slide = 1;
        $tablet_slide = 2;
        $desktop_slide = 3;

        if ( !empty($settings['repeater_slider_slides_to_show']) ) {
            $desktop_slide = $settings['repeater_slider_slides_to_show'];
            $default_slide = $settings['repeater_slider_slides_to_show'];
        }

        if ( !empty($settings['repeater_slider_slides_to_show_tablet']) ) {
            $tablet_slide = $settings['repeater_slider_slides_to_show_tablet'];
            $default_slide = $settings['repeater_slider_slides_to_show_tablet'];
        }

        if ( !empty($settings['repeater_slider_slides_to_show_mobile']) ) {
            $default_slide = $settings['repeater_slider_slides_to_show_mobile'];
        } 


        // Slider Slider Responsive
        $default_space = 10;
        $tablet_space = 20;
        $desktop_space = 30;

        if ( !empty($settings['repeater_slider_slider_space_between']) ) {
            $desktop_space = $settings['repeater_slider_slider_space_between']['size'];
            $default_space = $settings['repeater_slider_slider_space_between']['size'];
        }

        if ( !empty($settings['repeater_slider_slider_space_between_tablet']) ) {
            $tablet_space = $settings['repeater_slider_slider_space_between_tablet']['size'];
            $default_space = $settings['repeater_slider_slider_space_between_tablet']['size'];
        }

        if ( !empty($settings['repeater_slider_slider_space_between_mobile']) ) {
            $default_space = $settings['repeater_slider_slider_space_between_mobile']['size'];
        } 



        // Default settings with proper sanitization
        $settings_arr = array(
            'slidesPerView' => $this->sanitize_slides_per_view( $default_slide ?? 3 ),
            'spaceBetween'  => absint( $default_space ?? 30 ),
            'loop'          => $settings['repeater_slider_infinite_loop'] ?? false,
            'autoHeight'    => false,
            'keyboard'      => array(
                'enabled'        => true,
                'onlyInViewport' => true,
            ),
            'breakpoints'   => array(
                '768' => array(
                    'slidesPerView' => absint( $tablet_slide ),
                    'spaceBetween' => absint( $tablet_space ),
                ),
                '1024' => array(
                    'slidesPerView' => absint( $desktop_slide ),
                    'spaceBetween' => absint( $desktop_space ),
                ),
            )
        );


        // Handle autoplay settings
        if ( $settings['repeater_slider_autoplay'] ?? false ) {
            $settings_arr['autoplay'] = array(
                'delay'                => $this->sanitize_autoplay_delay( $settings['repeater_slider_autoplay_speed'] ?? 2500 ),
                'disableOnInteraction' => $settings['repeater_slider_pause_on_interaction'] ?? false,
                'pauseOnMouseEnter'    => $settings['repeater_slider_pause_on_hover'] ?? false,
            );
        }

        // Handle navigation settings
        $navigation_type = sanitize_text_field( $settings['repeater_slider_navigation'] ?? 'none' );
        $this->configure_navigation( $settings_arr, $navigation_type, $slider_id );

        return apply_filters( 'repeater_slider_settings', $settings_arr, $settings, $this->get_id() );
    }

    /**
    * Sanitize slides per view value
    */
    private function sanitize_slides_per_view( $value ) {
        if ( 'auto' === $value ) {
            return 'auto';
        }
        
        $int_value = absint( $value );
        return max( 1, min( 10, $int_value ) ); // Limit between 1-10 slides
    }


    /**
    * Sanitize autoplay delay value
    */
    private function sanitize_autoplay_delay( $value ) {
        $delay = absint( $value );
        return max( 500, min( 10000, $delay ) ); // Limit between 0.5-10 seconds
    }

    
    /**
    * 
    * Configure navigation based on type
    */

    private function configure_navigation( &$settings_arr, $navigation_type, $slider_id ) {

        if ( empty($slider_id) ) {
            return;
        }

        switch ( $navigation_type ) {
            case 'both':
                $settings_arr['navigation'] = array(
                    'nextEl' => sprintf('#%s .swiper-button-next', $slider_id),
                    'prevEl' => sprintf('#%s .swiper-button-prev', $slider_id),
                );
                $settings_arr['pagination'] = array(
                    'el'        => sprintf('#%s .swiper-pagination', $slider_id),
                    'clickable' => true,
                );
                break;

            case 'arrows':
                $settings_arr['navigation'] = array(
                    'nextEl' => sprintf('#%s .swiper-button-next', $slider_id),
                    'prevEl' => sprintf('#%s .swiper-button-prev', $slider_id),
                );
                break;

            case 'dots':
                $settings_arr['pagination'] = array(
                    'el'        => sprintf('#%s .swiper-pagination', $slider_id),
                    'clickable' => true,
                );
                break;

            case 'none':
            default:
                // Remove navigation and pagination if set to none or invalid value
                unset( $settings_arr['navigation'], $settings_arr['pagination'] );
                break;
        }
    }

    /**
     * Get slider data attributes
     */
    private function get_slider_data_attributes($display_options) {

        $attributes = [
            'data-navigation' => esc_attr( in_array($display_options['repeater_slider_navigation'], ['arrows', 'both']) ? 'yes' : '0'),
            'data-pagination' => esc_attr( $display_options['repeater_slider_navigation'] == 'dots' ? 'yes' : '0' ),
            'data-pagination' => esc_attr( $display_options['repeater_slider_navigation'] == 'dots' ? 'yes' : '0' ),
            // 'data-image-direction' => esc_attr($display_options['box_image_position'])
        ];
        
        return implode(' ', array_map(function($key, $value) {
            return sprintf('%s="%s"', $key, $value );
        }, array_keys($attributes), $attributes));
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

