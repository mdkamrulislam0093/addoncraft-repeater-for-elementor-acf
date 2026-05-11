<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class REPEFOEL_post_Repeater extends REPEFOEL_Widget_Base {

    public function get_name() {
        return 'REPEFOEL_widget_post_repeater';
    }

    public function get_title() {
        return __( 'Post Repeater', 'addoncraft-repeater-for-elementor-acf' );
    }

    public function get_style_depends() {
        return [ 'repefoel-rp-style' ];
    }

    /* ------------------------------------------------------------------ */
    /* Option helpers                                                         */
    /* ------------------------------------------------------------------ */

    private function get_terms_options( $taxonomy ) {
        $terms   = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
        $options = [];

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->term_id ] = $term->name;
            }
        }

        return $options;
    }

    private function get_authors_options() {
        $options = [ '' => __( 'All', 'addoncraft-repeater-for-elementor-acf' ) ];
        $users   = get_users( [ 'capability' => 'edit_posts', 'fields' => [ 'ID', 'display_name' ] ] );

        if ( ! empty( $users ) ) {
            foreach ( $users as $user ) {
                $options[ $user->ID ] = $user->display_name;
            }
        }

        return $options;
    }

    private function get_post_type_options() {
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $exclude    = [
            'attachment', 'elementor_library', 'e-floating-buttons',
            'elementor_font', 'elementor_icons', 'wp_block',
            'wp_navigation', 'wp_template', 'wp_template_part', 'wp_global_styles',
        ];
        $options = [];

        foreach ( $post_types as $post_type ) {
            if ( ! in_array( $post_type->name, $exclude, true ) ) {
                $options[ $post_type->name ] = $post_type->label;
            }
        }

        return $options;
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

        $this->add_control( 'REPEFOEL_repeater_post_type', [
            'label'   => __( 'Select Post Types', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SELECT2,
            'options' => $this->get_post_type_options(),
            'default' => 'post',
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

        // ── Query ────────────────────────────────────────────────────────
        $this->start_controls_section( 'repeater_section_query', [
            'label' => __( 'Query', 'addoncraft-repeater-for-elementor-acf' ),
        ] );

        $this->add_control( 'REPEFOEL_posts_per_page', [
            'label'   => __( 'Limit', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 6,
        ] );

        $this->add_control( 'include_posts', [
            'label'       => __( 'Include Posts', 'addoncraft-repeater-for-elementor-acf' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'description' => __( 'Enter post IDs separated by comma (1,2,3)', 'addoncraft-repeater-for-elementor-acf' ),
        ] );

        $this->add_control( 'categories', [
            'label'    => __( 'Categories', 'addoncraft-repeater-for-elementor-acf' ),
            'type'     => \Elementor\Controls_Manager::SELECT2,
            'multiple' => true,
            'options'  => $this->get_terms_options( 'category' ),
        ] );

        $this->add_control( 'tags', [
            'label'    => __( 'Tags', 'addoncraft-repeater-for-elementor-acf' ),
            'type'     => \Elementor\Controls_Manager::SELECT2,
            'multiple' => true,
            'options'  => $this->get_terms_options( 'post_tag' ),
        ] );

        $this->add_control( 'author', [
            'label'   => __( 'Author', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => $this->get_authors_options(),
        ] );

        $this->add_control( 'orderby', [
            'label'   => __( 'Order By', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'date',
            'options' => [
                'date'          => __( 'Date', 'addoncraft-repeater-for-elementor-acf' ),
                'title'         => __( 'Title', 'addoncraft-repeater-for-elementor-acf' ),
                'menu_order'    => __( 'Menu Order', 'addoncraft-repeater-for-elementor-acf' ),
                'rand'          => __( 'Random', 'addoncraft-repeater-for-elementor-acf' ),
                'comment_count' => __( 'Comment Count', 'addoncraft-repeater-for-elementor-acf' ),
            ],
        ] );

        $this->add_control( 'order', [
            'label'   => __( 'Order', 'addoncraft-repeater-for-elementor-acf' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'DESC',
            'options' => [
                'DESC' => __( 'Descending', 'addoncraft-repeater-for-elementor-acf' ),
                'ASC'  => __( 'Ascending', 'addoncraft-repeater-for-elementor-acf' ),
            ],
        ] );

        $this->add_control( 'ignore_sticky', [
            'label'        => __( 'Ignore Sticky Posts', 'addoncraft-repeater-for-elementor-acf' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
            'label_off'    => __( 'No', 'addoncraft-repeater-for-elementor-acf' ),
            'return_value' => 'yes',
            'default'      => 'yes',
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

        // ── Pagination ───────────────────────────────────────────────────
        $this->start_controls_section( 'pagination_section', [
            'label' => __( 'Pagination', 'addoncraft-repeater-for-elementor-acf' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'enable_pagination', [
            'label'        => __( 'Enable Pagination', 'addoncraft-repeater-for-elementor-acf' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'addoncraft-repeater-for-elementor-acf' ),
            'label_off'    => __( 'No', 'addoncraft-repeater-for-elementor-acf' ),
            'return_value' => 'yes',
            'default'      => '',
        ] );

        $this->add_control( 'pagination_align', [
            'label'     => __( 'Alignment', 'addoncraft-repeater-for-elementor-acf' ),
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'options'   => [
                'left'   => [ 'title' => __( 'Left', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-text-align-left' ],
                'center' => [ 'title' => __( 'Center', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-text-align-center' ],
                'right'  => [ 'title' => __( 'Right', 'addoncraft-repeater-for-elementor-acf' ), 'icon' => 'eicon-text-align-right' ],
            ],
            'default'   => 'center',
            'condition' => [ 'enable_pagination' => 'yes' ],
            'selectors' => [ '{{WRAPPER}} .repefoel-pagination' => 'justify-content: {{VALUE}};' ],
        ] );

        $this->end_controls_section();
    }

    /* ------------------------------------------------------------------ */
    /* Render                                                                */
    /* ------------------------------------------------------------------ */

    protected function render() {
        $settings          = $this->get_settings_for_display();
        $template_id       = isset( $settings['repefoel_template_select'] ) ? (int) $settings['repefoel_template_select'] : 0;
        $repeater_post_type = isset( $settings['REPEFOEL_repeater_post_type'] ) ? sanitize_text_field( $settings['REPEFOEL_repeater_post_type'] ) : '';

        if ( empty( $template_id ) ) {
            printf( '<div class="elementor-alert elementor-alert-info">%s</div>', esc_html__( 'Please select a template in the widget settings.', 'addoncraft-repeater-for-elementor-acf' ) );
            return;
        }

        if ( empty( $repeater_post_type ) ) {
            printf( '<div class="elementor-alert elementor-alert-info">%s</div>', esc_html__( 'Please select a post type in the widget settings.', 'addoncraft-repeater-for-elementor-acf' ) );
            return;
        }

        $pagination_enabled = ( isset( $settings['enable_pagination'] ) && $settings['enable_pagination'] === 'yes' );
        $paged              = $pagination_enabled ? max( 1, get_query_var( 'paged', 1 ) ) : 1;

        $query_args = [
            'post_type'           => $repeater_post_type,
            'posts_per_page'      => ! empty( $settings['REPEFOEL_posts_per_page'] ) ? absint( $settings['REPEFOEL_posts_per_page'] ) : 6,
            'orderby'             => ! empty( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'date',
            'order'               => ( ! empty( $settings['order'] ) && in_array( $settings['order'], [ 'ASC', 'DESC' ], true ) ) ? $settings['order'] : 'DESC',
            'ignore_sticky_posts' => ( isset( $settings['ignore_sticky'] ) && $settings['ignore_sticky'] === 'yes' ) ? 1 : 0,
            'paged'               => $paged,
            'no_found_rows'       => ! $pagination_enabled, // skip COUNT(*) when pagination is off
        ];

        if ( ! empty( $settings['include_posts'] ) ) {
            $post_ids = array_filter( array_map( 'absint', explode( ',', $settings['include_posts'] ) ) );
            if ( ! empty( $post_ids ) ) {
                $query_args['post__in'] = $post_ids;
            }
        }

        if ( ! empty( $settings['categories'] ) && is_array( $settings['categories'] ) ) {
            $cat_ids = array_filter( array_map( 'absint', $settings['categories'] ) );
            if ( ! empty( $cat_ids ) ) {
                $query_args['category__in'] = $cat_ids;
            }
        }

        if ( ! empty( $settings['tags'] ) && is_array( $settings['tags'] ) ) {
            $tag_ids = array_filter( array_map( 'absint', $settings['tags'] ) );
            if ( ! empty( $tag_ids ) ) {
                $query_args['tag__in'] = $tag_ids;
            }
        }

        if ( ! empty( $settings['author'] ) ) {
            $author_id = absint( $settings['author'] );
            if ( $author_id > 0 ) {
                $query_args['author'] = $author_id;
            }
        }

        $query = new WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            wp_reset_postdata();
            return;
        }

        ob_start();
        ?>
        <div class="repefoel_loop_repeater_main">
            <div class="repefoel-rp-row">
                <?php
                while ( $query->have_posts() ) :
                    $query->the_post();
                    ?>
                    <div class="repefoel-rp-item">
                        <div class="repefoel-rp-inner">
                            <?php echo wp_kses( $this->render_template( $template_id ), $this->allow_all_html_tags() ); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ( $pagination_enabled && $query->max_num_pages > 1 ) : ?>
                <nav class="repefoel-pagination" aria-label="<?php esc_attr_e( 'Posts navigation', 'addoncraft-repeater-for-elementor-acf' ); ?>">
                    <?php
                    echo wp_kses_post( paginate_links( [
                        'base'    => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
                        'format'  => '?paged=%#%',
                        'current' => $paged,
                        'total'   => $query->max_num_pages,
                    ] ) );
                    ?>
                </nav>
            <?php endif; ?>
        </div>
        <?php
        wp_reset_postdata();

        echo wp_kses( ob_get_clean(), $this->allow_all_html_tags() );
    }

    protected function content_template() {}
}
