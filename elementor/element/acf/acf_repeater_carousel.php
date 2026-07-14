<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class REPEFOEL_ACF_Repeater_Carousel extends REPEFOEL_Widget_Base {

    public function get_name() {
        return 'REPEFOEL_widget_repeater_carousel';
    }

    public function get_title() {
        return __( 'Repeater Slider', 'addoncraft-repeater-for-elementor-acf' );
    }

    public function get_style_depends() {
        return [ 'swiper', 'repefoel-rp-style' ];
    }

    public function get_script_depends() {
        return [ 'swiper', 'repefoel-swiper-init' ];
    }

    /* ------------------------------------------------------------------ */
    /* Controls                                                              */
    /* ------------------------------------------------------------------ */

    protected function register_controls() {

        // ── Content ──────────────────────────────────────────────────────
        $this->start_controls_section( 'content_section', [
            'label' => __( 'Content', 'addoncraft-repeater-for-elementor-acf' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'repefoel_template_select', [
            'label'   => esc_html__( 'Repeater Template', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => 'repeater_template_select',
            'options' => $this->get_all_repeater_template(),
        ] );

        $this->add_control( 'REPEFOEL_repeater_field', [
            'label'   => __( 'Repeater Field', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SELECT2,
            'options' => $this->get_only_repeater_name(),
            'default' => '',
        ] );

        $this->add_control( 'REPEFOEL_apply_preview', [
            'label'       => esc_html__( 'Refresh Repeater', 'addoncraft-repeater-for-elementor-acf' ),
            'type'        => \Elementor\Controls_Manager::BUTTON,
            'separator'   => 'before',
            'button_type' => 'success',
            'show_label'  => false,
            'text'        => esc_html__( 'Refresh Repeater', 'addoncraft-repeater-for-elementor-acf' ),
            'event'       => 'REPEFOEL:apply_preview',
        ] );

        $this->end_controls_section();

        $this->register_settings_controls();

        // ── Query ────────────────────────────────────────────────────────
        $this->start_controls_section( 'repeater_section_query', [
            'label' => __( 'Query', 'addoncraft-repeater-for-elementor-acf' ),
        ] );

        $this->add_control( 'repeater_query_source', [
            'label'   => __( 'ACF Repeater Source', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'current',
            'options' => [
                'current' => __( 'Current', 'addoncraft-repeater-for-elementor-acf' ),
                'manual'  => __( 'Manual', 'addoncraft-repeater-for-elementor-acf' ),
            ],
        ] );

        $this->add_control( 'repeater_manual_posts', [
            'label'       => __( 'Select Posts', 'addoncraft-repeater-for-elementor-acf' ),
            'type'        => \Elementor\Controls_Manager::SELECT2,
            'options'     => $this->get_all_posts(),
            'multiple'    => false,
            'label_block' => true,
            'condition'   => [ 'repeater_query_source' => 'manual' ],
        ] );

        $this->end_controls_section();
    }

    /* ------------------------------------------------------------------ */
    /* Slider settings controls                                              */
    /* ------------------------------------------------------------------ */

    private function register_settings_controls() {

        $this->start_controls_section( 'repeater_slider_settings_section', [
            'label' => esc_html__( 'Slider Settings', 'addoncraft-repeater-for-elementor-acf' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $slides_to_show = range( 1, 10 );
        $slides_to_show = array_combine( $slides_to_show, $slides_to_show );

        $this->add_responsive_control( 'repeater_slider_slides_to_show', [
            'label'              => esc_html__( 'Slides to Show', 'addoncraft-repeater-for-elementor-acf' ),
            'type'               => \Elementor\Controls_Manager::SELECT,
            'options'            => [ '' => esc_html__( 'Default', 'addoncraft-repeater-for-elementor-acf' ) ] + $slides_to_show,
            'default'            => 3,
            'frontend_available' => true,
            'render_type'        => 'template',
        ] );

        $this->add_responsive_control( 'repeater_slider_slider_space_between', [
            'label'   => esc_html__( 'Space Between', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SLIDER,
            'default' => [ 'size' => 15 ],
        ] );

        $this->add_control( 'repeater_slider_autoplay', [
            'label'        => __( 'Autoplay', 'addoncraft-repeater-for-elementor-acf' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
            'label_off'    => __( 'No', 'addoncraft-repeater-for-elementor-acf' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'repeater_slider_pause_on_hover', [
            'label'              => esc_html__( 'Pause on Hover', 'addoncraft-repeater-for-elementor-acf' ),
            'type'               => \Elementor\Controls_Manager::SWITCHER,
            'label_on'           => esc_html__( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
            'label_off'          => esc_html__( 'No', 'addoncraft-repeater-for-elementor-acf' ),
            'return_value'       => 'yes',
            'default'            => 'yes',
            'condition'          => [ 'repeater_slider_autoplay' => 'yes' ],
            'render_type'        => 'none',
            'frontend_available' => true,
        ] );

        $this->add_control( 'repeater_slider_pause_on_interaction', [
            'label'              => esc_html__( 'Pause on Interaction', 'addoncraft-repeater-for-elementor-acf' ),
            'type'               => \Elementor\Controls_Manager::SWITCHER,
            'label_on'           => esc_html__( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
            'label_off'          => esc_html__( 'No', 'addoncraft-repeater-for-elementor-acf' ),
            'return_value'       => 'yes',
            'default'            => 'yes',
            'condition'          => [ 'repeater_slider_autoplay' => 'yes' ],
            'frontend_available' => true,
        ] );

        $this->add_control( 'repeater_slider_autoplay_speed', [
            'label'     => __( 'Autoplay Speed (ms)', 'addoncraft-repeater-for-elementor-acf' ),
            'type'      => \Elementor\Controls_Manager::NUMBER,
            'default'   => 3000,
            'condition' => [ 'repeater_slider_autoplay' => 'yes' ],
        ] );

        $this->add_control( 'repeater_slider_infinite_loop', [
            'label'        => __( 'Infinite Loop', 'addoncraft-repeater-for-elementor-acf' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
            'label_off'    => __( 'No', 'addoncraft-repeater-for-elementor-acf' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'repeater_slider_navigation', [
            'label'              => esc_html__( 'Navigation', 'addoncraft-repeater-for-elementor-acf' ),
            'type'               => \Elementor\Controls_Manager::SELECT,
            'default'            => 'both',
            'options'            => [
                'both'   => esc_html__( 'Arrows and Dots', 'addoncraft-repeater-for-elementor-acf' ),
                'arrows' => esc_html__( 'Arrows', 'addoncraft-repeater-for-elementor-acf' ),
                'dots'   => esc_html__( 'Dots', 'addoncraft-repeater-for-elementor-acf' ),
                'none'   => esc_html__( 'None', 'addoncraft-repeater-for-elementor-acf' ),
            ],
            'frontend_available' => true,
        ] );

        $this->add_control( 'repeater_slider_arrow_type', [
            'label'   => __( 'Arrow Type', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'default',
            'options' => [
                'default' => __( 'Default', 'addoncraft-repeater-for-elementor-acf' ),
                'icon'    => __( 'Icon', 'addoncraft-repeater-for-elementor-acf' ),
                'image'   => __( 'Custom Image', 'addoncraft-repeater-for-elementor-acf' ),
            ],
            'conditions' => [
                'relation' => 'or',
                'terms'    => [
                    [ 'name' => 'repeater_slider_navigation', 'operator' => '==', 'value' => 'both' ],
                    [ 'name' => 'repeater_slider_navigation', 'operator' => '==', 'value' => 'arrows' ],
                ],
            ],
        ] );

        $this->start_controls_tabs( 'repeater_slider_arrows_tabs', [
            'conditions' => [
                'relation' => 'or',
                'terms'    => [
                    [ 'name' => 'repeater_slider_arrow_type', 'operator' => 'in', 'value' => [ 'icon', 'image' ] ],
                ],
            ],
        ] );

            $this->start_controls_tab( 'repeater_slider_prev_tab', [
                'label' => __( 'Prev', 'addoncraft-repeater-for-elementor-acf' ),
            ] );

                $this->add_control( 'repeater_slider_prev_icon', [
                    'label'     => __( 'Prev Icon', 'addoncraft-repeater-for-elementor-acf' ),
                    'type'      => \Elementor\Controls_Manager::ICONS,
                    'default'   => [ 'value' => 'fas fa-chevron-left' ],
                    'condition' => [ 'repeater_slider_arrow_type' => 'icon' ],
                ] );

                $this->add_control( 'repeater_slider_prev_image', [
                    'label'     => __( 'Prev Image', 'addoncraft-repeater-for-elementor-acf' ),
                    'type'      => \Elementor\Controls_Manager::MEDIA,
                    'condition' => [ 'repeater_slider_arrow_type' => 'image' ],
                ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'repeater_slider_next_tab', [
                'label' => __( 'Next', 'addoncraft-repeater-for-elementor-acf' ),
            ] );

                $this->add_control( 'repeater_slider_next_icon', [
                    'label'     => __( 'Next Icon', 'addoncraft-repeater-for-elementor-acf' ),
                    'type'      => \Elementor\Controls_Manager::ICONS,
                    'default'   => [ 'value' => 'fas fa-chevron-right' ],
                    'condition' => [ 'repeater_slider_arrow_type' => 'icon' ],
                ] );

                $this->add_control( 'repeater_slider_next_image', [
                    'label'     => __( 'Next Image', 'addoncraft-repeater-for-elementor-acf' ),
                    'type'      => \Elementor\Controls_Manager::MEDIA,
                    'condition' => [ 'repeater_slider_arrow_type' => 'image' ],
                ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->register_layout_style_controls();
        $this->register_navigation_style_controls();
        $this->register_pagination_style_controls();
    }

    /* ------------------------------------------------------------------ */
    /* Style controls                                                         */
    /* ------------------------------------------------------------------ */

    public function register_layout_style_controls() {
        $this->start_controls_section( 'acf_repeater__content_style', [
            'label' => esc_html__( 'Inner Layout', 'addoncraft-repeater-for-elementor-acf' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->add_responsive_control( 'acf_repeater_container_padding', [
            'label'      => esc_html__( 'Padding', 'addoncraft-repeater-for-elementor-acf' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
            'selectors'  => [
                '{{WRAPPER}} .repefoel_acf_repeater_rs_sliders' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();
    }

    private function register_navigation_style_controls() {
        $this->start_controls_section( 'acf_repeater_slider_navigation_style', [
            'label' => esc_html__( 'Navigation', 'addoncraft-repeater-for-elementor-acf' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->start_controls_tabs( 'acf_repeater_rs_navigation_orientation_tabs' );

            // ── Previous ─────────────────────────────────────────────────
            $this->start_controls_tab( 'acf_repeater_rs_navigation_left_orient', [
                'label' => esc_html__( 'Previous', 'addoncraft-repeater-for-elementor-acf' ),
            ] );

            $this->add_control( 'acf_repeater_rs_navigation_left_x_position', [
                'label'   => esc_html__( 'Horizontal', 'addoncraft-repeater-for-elementor-acf' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'atc_ss_x_left'  => [ 'title' => __( 'Left', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-h-align-left' ],
                    'atc_ss_x_right' => [ 'title' => __( 'Right', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-h-align-right' ],
                ],
                'default' => 'atc_ss_x_left',
                'toggle'  => true,
            ] );

            $this->add_control( 'acf_repeater_rs_navigaiton_left_x_position_offset', [
                'label'      => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => -500, 'max' => 500 ], '%' => [ 'min' => -100, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 30 ],
            ] );

            $this->add_control( 'acf_repeater_rs_navigation_position_horizontal_hr', [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ] );

            $this->add_control( 'acf_repeater_rs_navigation_left_y_position', [
                'label'   => esc_html__( 'Vertical', 'addoncraft-repeater-for-elementor-acf' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'atc_ss_y_top'    => [ 'title' => __( 'Top', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-v-align-top' ],
                    'atc_ss_y_center' => [ 'title' => __( 'Center', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-v-align-middle' ],
                    'atc_ss_y_bottom' => [ 'title' => __( 'Bottom', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-v-align-bottom' ],
                ],
                'default' => 'atc_ss_y_center',
                'toggle'  => true,
            ] );

            $this->add_control( 'acf_repeater_rs_navigaiton_left_y_position_offset', [
                'label'      => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => -500, 'max' => 500 ], '%' => [ 'min' => -100, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 0 ],
            ] );

            $this->add_control( 'acf_repeater_rs_navigation_icon_hr', [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ] );

            $this->add_control( 'acf_repeater_rs_navigaiton_prev_icon_size', [
                'label'      => esc_html__( 'Image / Icon Size', 'addoncraft-repeater-for-elementor-acf' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'devices'    => [ 'desktop', 'tablet', 'mobile' ],
                'conditions' => [ 'relation' => 'or', 'terms' => [ [ 'name' => 'repeater_slider_arrow_type', 'operator' => 'in', 'value' => [ 'icon', 'image' ] ] ] ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ], '%' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => '%', 'size' => 75 ],
                'selectors'  => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev svg' => 'width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->end_controls_tab();

            // ── Next ─────────────────────────────────────────────────────
            $this->start_controls_tab( 'acf_repeater_rs_navigation_right_orient', [
                'label' => esc_html__( 'Next', 'addoncraft-repeater-for-elementor-acf' ),
            ] );

            $this->add_control( 'acf_repeater_rs_navigation_right_x_position', [
                'label'   => esc_html__( 'Horizontal', 'addoncraft-repeater-for-elementor-acf' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'atc_ss_x_left'  => [ 'title' => __( 'Left', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-h-align-left' ],
                    'atc_ss_x_right' => [ 'title' => __( 'Right', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-h-align-right' ],
                ],
                'default' => 'atc_ss_x_right',
                'toggle'  => true,
            ] );

            $this->add_control( 'acf_repeater_rs_navigaiton_right_x_position_offset', [
                'label'      => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => -500, 'max' => 500 ], '%' => [ 'min' => -100, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => -30 ],
            ] );

            $this->add_control( 'acf_repeater_rs_navigation_position_left_horizontal_hr', [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ] );

            $this->add_control( 'acf_repeater_rs_navigation_right_y_position', [
                'label'   => esc_html__( 'Vertical', 'addoncraft-repeater-for-elementor-acf' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'atc_ss_y_top'    => [ 'title' => __( 'Top', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-v-align-top' ],
                    'atc_ss_y_center' => [ 'title' => __( 'Center', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-v-align-middle' ],
                    'atc_ss_y_bottom' => [ 'title' => __( 'Bottom', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-v-align-bottom' ],
                ],
                'default' => 'atc_ss_y_center',
                'toggle'  => true,
            ] );

            $this->add_control( 'acf_repeater_rs_navigaiton_right_y_position_offset', [
                'label'      => esc_html__( 'Offset', 'addoncraft-repeater-for-elementor-acf' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => -500, 'max' => 500 ], '%' => [ 'min' => -100, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 0 ],
            ] );

            $this->add_control( 'acf_repeater_rs_navigaiton_next_icon_size', [
                'label'      => esc_html__( 'Image / Icon Size', 'addoncraft-repeater-for-elementor-acf' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'devices'    => [ 'desktop', 'tablet', 'mobile' ],
                'conditions' => [ 'relation' => 'or', 'terms' => [ [ 'name' => 'repeater_slider_arrow_type', 'operator' => 'in', 'value' => [ 'icon', 'image' ] ] ] ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ], '%' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => '%', 'size' => 75 ],
                'selectors'  => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next svg' => 'width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control( 'acf_repeater_rs_navigation_position_hr', [ 'type' => \Elementor\Controls_Manager::DIVIDER ] );

        $this->add_group_control( \Elementor\Group_Control_Background::get_type(), [
            'name'     => 'acf_repeater_rs_navigation_background',
            'types'    => [ 'classic' ],
            'exclude'  => [ 'image' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Elementor Group_Control_Background param, not a WP_Query arg.
            'selector' => '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev, {{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next',
        ] );

        $this->add_control( 'acf_repeater_rs_navigation_color', [
            'label'     => esc_html__( 'Color', 'addoncraft-repeater-for-elementor-acf' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#fff',
            'selectors' => [
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev'           => 'color: {{VALUE}}',
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next'           => 'color: {{VALUE}}',
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next.icon svg path' => 'fill: {{VALUE}}',
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev.icon svg path' => 'fill: {{VALUE}}',
            ],
        ] );

        $this->add_control( 'acf_repeater_rs_navigaiton_size', [
            'label'     => esc_html__( 'Size', 'addoncraft-repeater-for-elementor-acf' ),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'devices'   => [ 'desktop', 'tablet', 'mobile' ],
            'selectors' => [
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev' => 'width: {{SIZE}}px; height: {{SIZE}}px; font-size: calc({{SIZE}}px / 1.5);',
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next' => 'width: {{SIZE}}px; height: {{SIZE}}px; font-size: calc({{SIZE}}px / 1.5);',
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next:after, {{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev:after' => 'font-size: calc({{SIZE}}px / 1.5);',
            ],
        ] );

        $this->add_responsive_control( 'acf_repeater_rs_navigaiton_border_radius', [
            'label'      => esc_html__( 'Border Radius', 'addoncraft-repeater-for-elementor-acf' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px' ],
            'default'    => [ 'top' => 5, 'right' => 5, 'bottom' => 5, 'left' => 5, 'unit' => 'px', 'isLinked' => false ],
            'selectors'  => [
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();
    }

    private function register_pagination_style_controls() {
        $this->start_controls_section( 'acf_repeater_slider_pagination_style', [
            'label' => esc_html__( 'Pagination', 'addoncraft-repeater-for-elementor-acf' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );

        $this->start_controls_tabs( 'acf_repeater_rs_pagination_background_tabs' );

            $this->start_controls_tab( 'acf_repeater_rs_pagination_background_normal', [
                'label' => esc_html__( 'Normal', 'addoncraft-repeater-for-elementor-acf' ),
            ] );

            $this->add_control( 'acf_repeater_rs_pagination_normal_background', [
                'label'     => esc_html__( 'Color', 'addoncraft-repeater-for-elementor-acf' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
                ],
            ] );

            $this->end_controls_tab();

            $this->start_controls_tab( 'acf_repeater_rs_pagination_background_active', [
                'label' => esc_html__( 'Active', 'addoncraft-repeater-for-elementor-acf' ),
            ] );

            $this->add_control( 'acf_repeater_rs_pagination_active_background', [
                'label'     => esc_html__( 'Color', 'addoncraft-repeater-for-elementor-acf' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet:hover'                       => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
                ],
            ] );

            $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control( 'acf_repeater_rs_pagination_size', [
            'label'     => esc_html__( 'Size', 'addoncraft-repeater-for-elementor-acf' ),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'devices'   => [ 'desktop', 'tablet', 'mobile' ],
            'selectors' => [
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet' => 'height: {{SIZE}}px; width: {{SIZE}}px;',
            ],
        ] );

        $this->add_control( 'acf_repeater_rs_pagination_gap', [
            'label'     => esc_html__( 'Gap', 'addoncraft-repeater-for-elementor-acf' ),
            'type'      => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination-bullet' => 'margin: 0 {{SIZE}}px;',
            ],
        ] );

        $this->add_control( 'acf_repeater_rs_pagination_space', [
            'label'      => esc_html__( 'Offset Y', 'addoncraft-repeater-for-elementor-acf' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px' ],
            'range'      => [ 'px' => [ 'min' => -200, 'max' => 200 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 0 ],
            'selectors'  => [
                '{{WRAPPER}} .acf_repeater_rs_swiper_product_sliders-nav .swiper-pagination' => 'bottom: {{SIZE}}px;',
            ],
        ] );

        $this->end_controls_section();
    }

    /* ------------------------------------------------------------------ */
    /* Render                                                                */
    /* ------------------------------------------------------------------ */

    protected function render() {
        $settings     = $this->get_settings_for_display();
        $template_id  = isset( $settings['repefoel_template_select'] ) ? (int) $settings['repefoel_template_select'] : 0;
        $repeater_field  = isset( $settings['REPEFOEL_repeater_field'] ) ? sanitize_text_field( $settings['REPEFOEL_repeater_field'] ) : '';
        $query_source = isset( $settings['repeater_query_source'] ) ? sanitize_text_field( $settings['repeater_query_source'] ) : '';

        if ( empty( $query_source ) ) {
            printf( '<div class="elementor-alert elementor-alert-info">%s</div>', esc_html__( 'Please select a query source in the widget settings.', 'addoncraft-repeater-for-elementor-acf' ) );
            return;
        }

        if ( empty( $template_id ) ) {
            printf( '<div class="elementor-alert elementor-alert-info">%s</div>', esc_html__( 'Please select a template in the widget settings.', 'addoncraft-repeater-for-elementor-acf' ) );
            return;
        }

        if ( empty( $repeater_field ) ) {
            printf( '<div class="elementor-alert elementor-alert-info">%s</div>', esc_html__( 'Please select a repeater field in the widget settings.', 'addoncraft-repeater-for-elementor-acf' ) );
            return;
        }

        if ( $query_source === 'manual' && ! empty( $settings['repeater_manual_posts'] ) ) {
            $query_post_id = absint( $settings['repeater_manual_posts'] );
        } else {
            $query_post_id = get_the_ID();
        }

        if ( empty( $query_post_id ) ) {
            $query_post_id = get_the_ID();
        }

        $template_content = $this->render_template( $template_id );
        $repeater_val     = get_field( $repeater_field, $query_post_id );

        if ( empty( $repeater_val ) || ! is_array( $repeater_val ) ) {
            return;
        }

        $slider_id       = 'repefoel-sliders-' . $this->get_id();
        $addition_options = $this->additional_options_slider( $settings );
        $slider_settings  = $this->get_slider_settings( $settings, $slider_id );

        ob_start();
        ?>
        <div class="repefoel_loop_repeater_main" id="<?php echo esc_attr( $slider_id ); ?>">
            <div class="repefoel_sliders-wrapper" data-nav="<?php echo esc_attr( $settings['repeater_slider_navigation'] ); ?>">
                <div class="repefoel_acf_repeater_rs_sliders swiper" data-swiper_settings='<?php echo esc_attr( wp_json_encode( $slider_settings ) ); ?>'>
                    <div class="swiper-wrapper">
                        <?php foreach ( $repeater_val as $repeat ) :
                            if ( ! is_array( $repeat ) ) continue; ?>
                            <div class="swiper-slide">
                                <div class="repefoel-slide-wrap">
                                    <?php echo wp_kses( $this->parse_template_content( $template_content, $repeat ), $this->allow_all_html_tags() ); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="acf_repeater_rs_swiper_product_sliders-nav">
                    <?php $this->render_slider_controls( $addition_options ); ?>
                </div>
            </div>
        </div>
        <?php
        echo wp_kses( ob_get_clean(), $this->allow_all_html_tags() );
    }

    /* ------------------------------------------------------------------ */
    /* Slider helpers                                                         */
    /* ------------------------------------------------------------------ */

    private function additional_options_slider( $settings ) {
        return [
            'show_navigation'          => $settings['repeater_slider_navigation'],
            'navigation_left_x_position'  => $settings['acf_repeater_rs_navigation_left_x_position'],
            'navigation_left_y_position'  => $settings['acf_repeater_rs_navigation_left_y_position'],
            'navigation_left_x_offset'    => $settings['acf_repeater_rs_navigaiton_left_x_position_offset'],
            'navigation_left_y_offset'    => $settings['acf_repeater_rs_navigaiton_left_y_position_offset'],
            'navigation_right_x_position' => $settings['acf_repeater_rs_navigation_right_x_position'],
            'navigation_right_y_position' => $settings['acf_repeater_rs_navigation_right_y_position'],
            'navigation_right_x_offset'   => $settings['acf_repeater_rs_navigaiton_right_x_position_offset'],
            'navigation_right_y_offset'   => $settings['acf_repeater_rs_navigaiton_right_y_position_offset'],
            'repeater_slider_arrow_type'   => $settings['repeater_slider_arrow_type'],
            'repeater_slider_prev_icon'    => $settings['repeater_slider_prev_icon'],
            'repeater_slider_next_icon'    => $settings['repeater_slider_next_icon'],
            'repeater_slider_prev_image'   => $settings['repeater_slider_prev_image'],
            'repeater_slider_next_image'   => $settings['repeater_slider_next_image'],
        ];
    }

    private function render_slider_controls( $opts ) {
        $show_navigation = $opts['show_navigation'] ?? '';

        if ( in_array( $show_navigation, [ 'arrows', 'both' ] ) ) {

            $nav_left_classes  = array_filter( [ $opts['navigation_left_x_position'], $opts['navigation_left_y_position'] ] );
            $nav_right_classes = array_filter( [ $opts['navigation_right_x_position'], $opts['navigation_right_y_position'] ] );

            $allowed_units = [ 'px', '%', 'em', 'rem', 'vw', 'vh' ];

            $lx_size = (float) ( $opts['navigation_left_x_offset']['size'] ?? 0 );
            $lx_unit = in_array( $opts['navigation_left_x_offset']['unit'] ?? 'px', $allowed_units ) ? $opts['navigation_left_x_offset']['unit'] : 'px';
            $ly_size = (float) ( $opts['navigation_left_y_offset']['size'] ?? 0 );
            $ly_unit = in_array( $opts['navigation_left_y_offset']['unit'] ?? 'px', $allowed_units ) ? $opts['navigation_left_y_offset']['unit'] : 'px';

            $rx_size = (float) ( $opts['navigation_right_x_offset']['size'] ?? 0 );
            $rx_unit = in_array( $opts['navigation_right_x_offset']['unit'] ?? 'px', $allowed_units ) ? $opts['navigation_right_x_offset']['unit'] : 'px';
            $ry_size = (float) ( $opts['navigation_right_y_offset']['size'] ?? 0 );
            $ry_unit = in_array( $opts['navigation_right_y_offset']['unit'] ?? 'px', $allowed_units ) ? $opts['navigation_right_y_offset']['unit'] : 'px';

            $arrow_type = sanitize_text_field( $opts['repeater_slider_arrow_type'] ?? '' );

            $left_style  = sprintf( 'transform: translate(%s%s, %s%s);', $lx_size, $lx_unit, $ly_size, $ly_unit );
            $right_style = sprintf( 'transform: translate(%s%s, %s%s);', $rx_size, $rx_unit, $ry_size, $ry_unit );
            ?>

            <div class="swiper-button-prev <?php echo esc_attr( implode( ' ', $nav_left_classes ) ); ?> <?php echo esc_attr( $arrow_type ); ?>"
                 style="<?php echo esc_attr( $left_style ); ?>">
                <?php
                if ( $arrow_type === 'image' ) {
                    printf( '<img src="%s" alt="" />', esc_url( $opts['repeater_slider_prev_image']['url'] ?? '' ) );
                } elseif ( $arrow_type === 'icon' ) {
                    \Elementor\Icons_Manager::render_icon( $opts['repeater_slider_prev_icon'], [ 'aria-hidden' => 'true' ] );
                }
                ?>
            </div>

            <div class="swiper-button-next <?php echo esc_attr( implode( ' ', $nav_right_classes ) ); ?> <?php echo esc_attr( $arrow_type ); ?>"
                 style="<?php echo esc_attr( $right_style ); ?>">
                <?php
                if ( $arrow_type === 'image' ) {
                    printf( '<img src="%s" alt="" />', esc_url( $opts['repeater_slider_next_image']['url'] ?? '' ) );
                } elseif ( $arrow_type === 'icon' ) {
                    \Elementor\Icons_Manager::render_icon( $opts['repeater_slider_next_icon'], [ 'aria-hidden' => 'true' ] );
                }
                ?>
            </div>

            <?php
        }

        if ( in_array( $show_navigation, [ 'dots', 'both' ] ) ) {
            echo '<div class="swiper-pagination"></div>';
        }
    }

    private function get_slider_settings( $settings, $slider_id ) {
        if ( ! is_array( $settings ) ) {
            $settings = [];
        }

        // Responsive slides-per-view
        $desktop_slide = $this->sanitize_slides_per_view( $settings['repeater_slider_slides_to_show']        ?? 3 );
        $tablet_slide  = $this->sanitize_slides_per_view( $settings['repeater_slider_slides_to_show_tablet'] ?? 2 );
        $mobile_slide  = $this->sanitize_slides_per_view( $settings['repeater_slider_slides_to_show_mobile'] ?? 1 );

        // Responsive space-between
        $desktop_space = absint( $settings['repeater_slider_slider_space_between']['size']        ?? 30 );
        $tablet_space  = absint( $settings['repeater_slider_slider_space_between_tablet']['size'] ?? 20 );
        $mobile_space  = absint( $settings['repeater_slider_slider_space_between_mobile']['size'] ?? 10 );

        $settings_arr = [
            'slidesPerView' => $mobile_slide,
            'spaceBetween'  => $mobile_space,
            'loop'          => ( ( $settings['repeater_slider_infinite_loop'] ?? '' ) === 'yes' ),
            'autoHeight'    => false,
            'keyboard'      => [ 'enabled' => true, 'onlyInViewport' => true ],
            'breakpoints'   => [
                '768'  => [ 'slidesPerView' => $tablet_slide,  'spaceBetween' => $tablet_space ],
                '1024' => [ 'slidesPerView' => $desktop_slide, 'spaceBetween' => $desktop_space ],
            ],
        ];

        if ( ( $settings['repeater_slider_autoplay'] ?? '' ) === 'yes' ) {
            $settings_arr['autoplay'] = [
                'delay'                => $this->sanitize_autoplay_delay( $settings['repeater_slider_autoplay_speed'] ?? 2500 ),
                'disableOnInteraction' => ( ( $settings['repeater_slider_pause_on_interaction'] ?? '' ) === 'yes' ),
                'pauseOnMouseEnter'    => ( ( $settings['repeater_slider_pause_on_hover'] ?? '' ) === 'yes' ),
            ];
        }

        $navigation_type = sanitize_text_field( $settings['repeater_slider_navigation'] ?? 'none' );
        $this->configure_navigation( $settings_arr, $navigation_type, $slider_id );

        return apply_filters( 'repefoel_repeater_slider_settings', $settings_arr, $settings, $this->get_id() );
    }

    private function sanitize_slides_per_view( $value ) {
        if ( 'auto' === $value ) return 'auto';
        return max( 1, min( 10, absint( $value ) ) );
    }

    private function sanitize_autoplay_delay( $value ) {
        return max( 500, min( 10000, absint( $value ) ) );
    }

    private function configure_navigation( &$settings_arr, $navigation_type, $slider_id ) {
        if ( empty( $slider_id ) ) return;

        switch ( $navigation_type ) {
            case 'both':
                $settings_arr['navigation'] = [
                    'nextEl' => sprintf( '#%s .swiper-button-next', $slider_id ),
                    'prevEl' => sprintf( '#%s .swiper-button-prev', $slider_id ),
                ];
                $settings_arr['pagination'] = [
                    'el'        => sprintf( '#%s .swiper-pagination', $slider_id ),
                    'clickable' => true,
                ];
                break;

            case 'arrows':
                $settings_arr['navigation'] = [
                    'nextEl' => sprintf( '#%s .swiper-button-next', $slider_id ),
                    'prevEl' => sprintf( '#%s .swiper-button-prev', $slider_id ),
                ];
                break;

            case 'dots':
                $settings_arr['pagination'] = [
                    'el'        => sprintf( '#%s .swiper-pagination', $slider_id ),
                    'clickable' => true,
                ];
                break;

            default:
                unset( $settings_arr['navigation'], $settings_arr['pagination'] );
                break;
        }
    }

    protected function content_template() {}
}
