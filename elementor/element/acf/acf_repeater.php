<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class REPEFOEL_ACF_Repeater extends REPEFOEL_Widget_Base {

    public function get_name() {
        return 'REPEFOEL_widget_repeater';
    }

    public function get_title() {
        return __( 'Repeater', 'addoncraft-repeater-for-elementor-acf' );
    }

    public function get_style_depends() {
        return [ 'repefoel-rp-style' ];
    }

    protected function register_controls() {

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

        // ── Layout ───────────────────────────────────────────────────────
        $this->start_controls_section( 'layout_section', [
            'label' => __( 'Layout', 'addoncraft-repeater-for-elementor-acf' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_responsive_control( 'columns', [
            'label'          => __( 'Columns', 'addoncraft-repeater-for-elementor-acf' ),
            'type'           => \Elementor\Controls_Manager::SELECT,
            'default'        => '3',
            'tablet_default' => '2',
            'mobile_default' => '1',
            'options'        => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ],
            'selectors'      => [
                '{{WRAPPER}} .repefoel-rp-row' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
            ],
        ] );

        $this->add_control( 'grid_gap', [
            'label'      => __( 'Gap Between Items', 'addoncraft-repeater-for-elementor-acf' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
            'default'    => [ 'unit' => 'px', 'size' => 5 ],
            'selectors'  => [ '{{WRAPPER}} .repefoel-rp-row' => 'gap: {{SIZE}}{{UNIT}};' ],
        ] );

        $this->end_controls_section();


    }

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

        ob_start();
        ?>
        <div class="repefoel_loop_repeater_main">
            <div class="repefoel-rp-row">
                <?php foreach ( $repeater_val as $repeat ) :
                    if ( ! is_array( $repeat ) ) continue; ?>
                    <div class="repefoel-rp-item">
                        <div class="repefoel-rp-inner">
                            <?php echo wp_kses( $this->parse_template_content( $template_content, $repeat ), $this->allow_all_html_tags() ); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        echo wp_kses( ob_get_clean(), $this->allow_all_html_tags() );
    }

    protected function content_template() {}
}
