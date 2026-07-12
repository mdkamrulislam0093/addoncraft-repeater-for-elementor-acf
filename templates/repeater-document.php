<?php

use Elementor\Core\Documents_Manager;
use Elementor\Modules\Library\Documents\Library_Document;

class REPEFOEL_Repeater_Document extends Library_Document {
    /**
     * Get document properties
     */
    public static function get_properties() {
        $properties = parent::get_properties();

        $properties['support_kit'] = true;
        $properties['show_in_finder'] = true;
        $properties['show_on_admin_bar'] = true;

        return $properties;
    }

     public function get_name() {
        return 'REPEFOEL_repeater';
    }
    
    public static function get_title() {
        return esc_html__( 'Addoncraft Repeater', 'addoncraft-repeater-for-elementor-acf' );
    }

    /**
     * Get document plural title
     */
    public static function get_plural_title() {
        return __('Addoncraft Repeaters', 'addoncraft-repeater-for-elementor-acf');
    }

    public static function get_type() {
        return 'REPEFOEL_repeater';
    }

    /**
     * Get create URL
     */
    public static function get_create_url() {
        return parent::get_create_url() . '#library';
    }

    /**
     * Save document as template
     */
    public function get_initial_config() {
        $config = parent::get_initial_config();

        $config['library'] = [
            'save_as_same_type' => true,
            'show_in_library' => true,
        ];

        return $config;
    }


    protected function register_controls() {
        parent::register_controls();
        
        // Add custom controls specific to this template type
        $this->start_controls_section(
            'custom_template_settings',
            [
                'label' => __('Repeater Preview Settings', 'addoncraft-repeater-for-elementor-acf'),
                'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
            ]
        );

        // Main select control
        $this->add_control(
            'REPEFOEL_preview_width',
            [
                'label' => esc_html__( 'Width', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 410,
                ],
                'selectors' => [
                    'div[data-elementor-type="REPEFOEL_repeater"]' => 'width: {{SIZE}}{{UNIT}}; margin: auto;',
                    
                ],
            ]
        );

        // Main select control
        $this->add_control(
            'REPEFOEL_preview_post',
            [
                'label' => __('Preview Post', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_all_posts(),
                'default' => get_option('page_on_front'),
                'render_type' => 'ui'
            ]
        );
        
        $this->add_control(
            'REPEFOEL_repeater_field',
            [
                'label' => __('Preview Repeater Field', 'addoncraft-repeater-for-elementor-acf'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_only_repeater_name(),
                'default' => '',
                'render_type' => 'ui'
            ]
        );
   
        $this->add_control(
            'REPEFOEL_apply_preview',
            [
                'label' => esc_html__( 'Apply & Preview', 'addoncraft-repeater-for-elementor-acf' ),
                'type' => \Elementor\Controls_Manager::BUTTON,
                'separator' => 'before',
                'button_type' => 'success',
                'show_label' => false,
                'text' => esc_html__( 'Apply & Preview', 'addoncraft-repeater-for-elementor-acf' ),
                'event' => 'REPEFOEL:apply_preview'
            ]
        );


        $this->end_controls_section();
    }

    public function get_css_wrapper_selector() {
        return '.elementor-REPEFOEL_repeater-' . $this->get_main_id();
    }
   
    /**
     * Print elements with wrapper
     */
    public function print_elements_with_wrapper($elements_data = null) {
        if (!$elements_data) {
            $elements_data = $this->get_elements_data();
        }

        ?>
        <div class="elementor elementor-<?php echo esc_attr( $this->get_main_id() ); ?> elementor-REPEFOEL-template">
            <div class="elementor-inner">
                <div class="elementor-section-wrap">
                    <?php $this->print_elements($elements_data); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get edit URL
     */
    public function get_edit_url() {
        $url = parent::get_edit_url();
        
        if ($url) {
            $url = add_query_arg([
                'post_type' => 'elementor_library',
                'template_type' => $this->get_name(),
            ], $url);
        }

        return $url;
    } 

    /**
     * Is built with elementor
     */
    public function is_built_with_elementor() {
        return true;
    }

    /**
     * Get container attributes
     */
    public function get_container_attributes() {
        $attributes = parent::get_container_attributes();

        $attributes['class'] .= ' elementor-REPEFOEL-template';

        return $attributes;
    }

    /**
     * Before delete
     */
    public function on_delete() {
        parent::on_delete();

        // Clean up any custom data when template is deleted
        delete_post_meta($this->get_main_id(), '_custom_template_setting');
    }

    /**
     * Get export data
     */
    public function get_export_data() {
        $export_data = parent::get_export_data();

        // Add custom template specific export data
        $export_data['custom_settings'] = [
            'description' => $this->get_settings('custom_template_description'),
            'category' => $this->get_settings('custom_template_category'),
        ];

        return $export_data;
    }

    /**
     * Import data
     */
    public function import_data($data) {
        parent::import_data($data);

        // Handle custom template specific import data
        if (isset($data['custom_settings'])) {
            $this->update_settings([
                'custom_template_description' => $data['custom_settings']['description'] ?? '',
                'custom_template_category' => $data['custom_settings']['category'] ?? 'general',
            ]);
        }
    }

    protected static function get_editor_panel_categories() {
        $categories = [
            'REPEFOEL_repeater' => [
                'title' => __('Addoncraft Repeater Settings', 'addoncraft-repeater-for-elementor-acf'),
                'active' => true,
            ],
        ];
        
        return $categories + parent::get_editor_panel_categories();
    }

    // protected static function get_site_editor_type() {
    //     return 'REPEFOEL_repeater';
    // }
    
    public function get_all_posts() {
        // global $wpdb;


        $all_posts = get_posts( [
            'post_type' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ] );


        if ( empty($all_posts) ) {
            return [];
        }

        $REPEFOEL_all_posts = [];
        foreach ($all_posts as $post_id) {
            $REPEFOEL_all_posts[$post_id] = esc_html( get_the_title( $post_id ) );
        }

        return $REPEFOEL_all_posts;
    }

    public function get_only_repeater_name() {

        if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
            return [];
        }

                
        $field_groups = acf_get_field_groups();
        $all_repeater_fields = [];

        if ( empty($field_groups) ) {
            return [];
        }

        foreach ($field_groups as $group) {
            $fields = acf_get_fields($group['ID']);
            
            if ( !empty($fields) ) {

                foreach ($fields as $field) {
                    // Check for direct repeater fields
                    if ($field['type'] === 'repeater') {
                        
                        $title = $field['label'];
                        $name = $field['name'];

                        $all_repeater_fields[$name] = $title;
                    }
                }
            }
        }

        return $all_repeater_fields;
    }
 
}
