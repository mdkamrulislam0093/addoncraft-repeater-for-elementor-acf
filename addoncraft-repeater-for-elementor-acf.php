<?php
/**
 * Plugin Name: Addoncraft Repeater For Elementor ACF
 * Description: Addoncraft Repeater for Elementor ACF is a plugin you install after Elementor! It’s packed with a variety of stunning elements and different types of widgets to enhance your website design.
 * Plugin URI: https://wordpress.org/plugins/addoncraft-repeater-for-elementor-acf
 * Author: addoncraft
 * Version: 1.2
 * Requires Plugins: elementor
 * Author URI: https://addonscraft.com/
 * License: GPLv3
 * License URI: https://opensource.org/licenses/GPL-3.0
 * Text Domain: addoncraft-repeater-for-elementor-acf
 */


defined( 'ABSPATH' ) || exit;

define( 'REPEFOEL__PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'REPEFOEL__PLUGIN_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'REPEFOEL__PLUGIN_VERSION', '1.1.0');

class REPEFOEL_ELEMENTOR_ADDON {

	private static $instance;

	public static function get_instance(){
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct(){
		add_action('plugins_loaded', [$this, 'REPEFOEL_init']);
	}

	public function REPEFOEL_init() {

		if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'repefoel_admin_notice_missing_main_plugin']);
            return;
        }

		if ( ! class_exists( 'ACF' ) ) {
            add_action('admin_notices', [$this, 'repefoel_admin_notice_missing_acf_pro_plugin']);
            return;
		}        

        // Check for minimum Elementor version
        if (!version_compare(ELEMENTOR_VERSION, '3.0.0', '>=')) {
            add_action('admin_notices', [$this, 'repefoel_admin_notice_minimum_elementor_version']);
            return;
        }


	    if ( class_exists('ACF') ) {

	        // Detect Pro
	        if ( function_exists('acf_add_options_page') ) {
	            define('REPEFOEL_USING_ACF_PRO', true);
	            $this->repefoel_acf_pro_include();
	            $this->repefoel_acf_free_include();
	        } else {
	            define('REPEFOEL_USING_ACF_PRO', false);
	            $this->repefoel_acf_free_include();
	        }

	    }

	}

	public function repefoel_acf_pro_include() {
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'repefoel_register_new_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'REPEFOEL_cat_register' ] );
		add_action('elementor/init', [$this, 'REPEFOEL_elementor_init']);
		add_action( 'elementor/controls/register', [$this, 'REPEFOEL_custom_controls'] );
		add_action( 'wp_enqueue_scripts', [$this, 'repefoel_frontend_enqueue_script'] );

		// Register Elementor Template
		add_action('elementor/documents/register', [$this, 'REPEFOEL_elementor_document_register']);
        
        // Add to template library
        add_filter('elementor/template-library/sources/local/template_types', [$this, 'REPEFOEL_add_template_type']);
        
        // Add template type to admin
        add_action('elementor/admin/after_create_settings', [$this, 'REPEFOEL_register_admin_fields'], 15);

        add_action('elementor/editor/after_enqueue_scripts', [$this, 'repefoel_enqueue_editor_scripts']);
        add_action( 'wp_ajax_repefoel_create_elementor_repeater_template', [$this, 'repefoel_create_elementor_repeater_template']);

        // Check if Dynamic Tags module exists (Pro feature)
        if (!class_exists('\Elementor\Modules\DynamicTags\Module')) {
            add_action('admin_notices', [$this, 'repefoel_admin_notice_pro_required']);
            return;
        }

		add_action('elementor/dynamic_tags/register', [$this, 'repefoel_register_dynamic_tags']);
	}

	public function repefoel_acf_free_include() {
	    add_action('elementor/dynamic_tags/register', [$this, 'repefoel_free_register_dynamic_tags']);		
	}

    /**
     * Enqueue custom scripts for the editor
     */
    public function repefoel_enqueue_editor_scripts() {
	    wp_enqueue_script(
	        'REPEFOEL-repeater-template',
	        plugin_dir_url( __FILE__ ) . 'assets/js/repeaters_template.js',
	        [ 'jquery', 'elementor-editor' ],
	        REPEFOEL__PLUGIN_VERSION,
	        true
	    );

	    // Localized strings for i18n
	    wp_localize_script(
	        'REPEFOEL-repeater-template',
	        'REPEFOELTemplateData',
	        [
	        	'ajax_url' => admin_url('admin-ajax.php'),
	            'customTemplate' => esc_html__( 'Custom Template', 'addoncraft-repeater-for-elementor-acf' ),
	            'customTemplates' => esc_html__( 'Custom Templates', 'addoncraft-repeater-for-elementor-acf' ),
	            'successMessage' => esc_html__( 'Custom template saved successfully!', 'addoncraft-repeater-for-elementor-acf' ),
	        ]
	    );	
	    
	    wp_enqueue_style(
	        'REPEFOEL-repeater-template',
	        plugin_dir_url( __FILE__ ) . 'assets/css/custom-template.css',
	        [],
	        REPEFOEL__PLUGIN_VERSION
	    );   
    } 


    public function repefoel_admin_notice_missing_main_plugin() {

        if (!current_user_can('activate_plugins')) {
            return;
        }

        $message = sprintf(
            __('<strong>Addoncraft Repeater For Elementor ACF</strong> requires <strong>Elementor</strong> to be installed and activated.', 'addoncraft-repeater-for-elementor-acf')
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>',  wp_kses_data( $message ) );
    }

    public function repefoel_admin_notice_missing_acf_pro_plugin() {

        if (!current_user_can('activate_plugins')) {
            return;
        }

        $message = sprintf(
            __('<strong>Addoncraft Repeater For Elementor ACF</strong> requires <strong><strong>Advanced Custom Fields (Free or Pro)</strong> to work properly.<</strong> to be installed and activated.', 'addoncraft-repeater-for-elementor-acf')
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_data( $message) );
    }


    public function repefoel_admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
        
        $message = sprintf(
            __('<strong>Addoncraft Repeater For Elementor ACF</strong> requires <strong>Elementor</strong> version 3.0.0 or greater.', 'addoncraft-repeater-for-elementor-acf')
        );
        
        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_data($message) );
    }

    public function repefoel_admin_notice_pro_required() {
        ?>
        <div class="notice notice-error is-dismissible">
            <h3><?php esc_html_e( 'Elementor Pro Required', 'addoncraft-repeater-for-elementor-acf'); ?></h3>
            <p>
                <?php esc_html_e('Addoncraft Repeater For Elementor ACF requires <strong>Elementor Pro</strong> to work because Dynamic Tags are a Pro-only feature.', 'addoncraft-repeater-for-elementor-acf'); ?>
            </p>
        </div>
        <?php
    }
   
	public function repefoel_free_register_dynamic_tags( $tags_manager ) {

		$this->register_free_group($tags_manager);

		/**
		* Dynamic Tag for simple text field dynamic tag like heading
		*/
		require_once ( __DIR__ . '/dynamic-tags/normal/acf_text_tag.php' );
		$tags_manager->register( new \REPEFOEL_ACF_Text() );

		require_once ( __DIR__ . '/dynamic-tags/normal/acf_image_tag.php' );
		$tags_manager->register( new \REPEFOEL_ACF_Image() );		

		require_once ( __DIR__ . '/dynamic-tags/normal/acf_url_tag.php' );
		$tags_manager->register( new \REPEFOEL_ACF_URL() );		

		require_once ( __DIR__ . '/dynamic-tags/normal/site_title.php' );
		$tags_manager->register( new \REPEFOEL_SITE_TITLE() );		

		require_once ( __DIR__ . '/dynamic-tags/normal/author-info.php' );
		$tags_manager->register( new \REPEFOEL_Author_Info() );		

		require_once ( __DIR__ . '/dynamic-tags/normal/author-profile.php' );
		$tags_manager->register( new \REPEFOEL_Author_Profile() );		


	}

    public function register_free_group($tags_manager)
    {
      
        $tags_manager->register_group(
            'REPEFOEL-free_dynamic-tag',
            [
                'title' => esc_html__('Addoncraft ACF', 'addoncraft-repeater-for-elementor-acf')
            ]
        );
    }

	public function repefoel_register_dynamic_tags( $tags_manager ) {

		$this->register_group($tags_manager);

		/**
		* Dynamic Tag for simple text field dynamic tag like heading
		*/
		require_once ( __DIR__ . '/dynamic-tags/repeater/acf_text_repeater_tag.php' );
		$tags_manager->register( new \REPEFOEL_ACF_Repeater_Text() );

		/**
		* Dynamic Tag for Image field dynamic tag like Image
		*/
		require_once ( __DIR__ . '/dynamic-tags/repeater/acf_image_repeater_tag.php' );
		$tags_manager->register( new \REPEFOEL_ACF_Repeater_Image() );

		/**
		* Dynamic Tag for URL field dynamic tag like Hyperlink
		*/
		require_once ( __DIR__ . '/dynamic-tags/repeater/acf_url_repeater_tag.php' );
		$tags_manager->register( new \REPEFOEL_ACF_Repeater_URL() );

	}

	public function REPEFOEL_custom_controls($controls_manager) {
		/**
		* Dynamic Tag for URL field dynamic tag like Hyperlink
		*/
		require_once 'controls/repeater-control.php';
		$controls_manager->register( new \REPEFOEL_repeater_control() );


		
	}

	public function REPEFOEL_elementor_document_register( $documents_manager ) {
		require_once 'templates/repeater-document.php';
		$documents_manager->register_document_type( 'REPEFOEL_repeater', 'REPEFOEL_Repeater_Document' );
	}

    /**
     * Add custom template type to Elementor's template types
     */
	public function REPEFOEL_add_template_type($template_types) {
        $template_types[self::TEMPLATE_TYPE] = [
            'title' => __('Addoncraft Repeater', 'addoncraft-repeater-for-elementor-acf'),
            'plural_title' => __('Addoncraft Repeaters', 'addoncraft-repeater-for-elementor-acf'),
        ];
        
        return $template_types;
	}

    /**
     * Register admin fields
     */
    public function REPEFOEL_register_admin_fields($settings) {
        // Add template type to new template dialog
        add_filter( 'elementor/template-library/create_new_dialog_types', [$this, 'REPEFOEL_template_dialog_types'] );
    }

	public function REPEFOEL_template_dialog_types( $types ) {
	    $types['REPEFOEL_repeater'] = [
            'title' => __('Addoncraft Repeater', 'addoncraft-repeater-for-elementor-acf'),
            'icon' => 'eicon-document-file',
        ];
	    

	    return $types;
	}

    public function register_group($tags_manager)
    {
      
        $tags_manager->register_group(
            'REPEFOEL-dynamic-tag',
            [
                'title' => esc_html__('Addoncraft Repeater', 'addoncraft-repeater-for-elementor-acf')
            ]
        );
    }

	/**
	 * Register Enqueue Scripts
	 *
	 * @since v1.0.0
	 */
	public function repefoel_frontend_enqueue_script() {
		$css_deps = [ 'elementor-frontend' ];

		wp_register_style( 'REPEFOEL_custom_style', plugins_url('/assets/css/style.css', __FILE__), $css_deps, REPEFOEL__PLUGIN_VERSION, 'all' );
		wp_enqueue_style( 'REPEFOEL_custom_style' );

		wp_register_style( 'repefoel-rp-style', plugins_url('/assets/css/rp-widget.css', __FILE__), $css_deps, REPEFOEL__PLUGIN_VERSION, 'all' );
		wp_enqueue_style( 'repefoel-rp-style' );

		
		
	}

	/**
	 * Register Categories
	 *
	 * @since v1.0.0
	 */

	public function REPEFOEL_cat_register( $elements_manager ) {

		$categories = [];
		$categories['REPEFOEL_category_advanced'] =
			[
				'title' => 'Addoncraft Repeater',
				'icon'  => 'fa fa-plug',
				// 'promotion' => [
				// 	'url' => 'https://go.elementor.com/go-pro-section-woocommerce-widget-panel/'
				// ]	
			];

		$old_categories = $elements_manager->get_categories();

		$categories = array_merge(
			array_slice($old_categories, 0, 4),
			$categories,
			array_slice($old_categories, 4, null),
		);

		$set_categories = function ( $categories ) {
			$this->categories = $categories;
		};

		$set_categories->call( $elements_manager, $categories );

	}

	/**
	 * Register widgets
	 *
	 * @since v1.0.0
	 */

	public function repefoel_register_new_widgets() {
	    require_once 'elementor/element/acf/acf_repeater.php';
	    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new REPEFOEL_ACF_Repeater );

	    require_once 'elementor/element/acf/acf_repeater_carousel.php';
	    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new REPEFOEL_ACF_Repeater_Carousel );


	}

	public function REPEFOEL_elementor_init() {
	    $args = [
	        'labels' => 'Addoncraft Repeater',
	        'public' => true,
	        'show_ui' => true,
	        'show_in_menu' => true,
	        'show_in_nav_menus' => true,
	        'exclude_from_search' => false,
	        'capability_type' => 'post',
	        'hierarchical' => false,
	        'supports' => ['title', 'editor', 'thumbnail'],
	    ];

	    register_post_type('repeRepeaterTypes', $args);
	}


	public function repefoel_create_elementor_repeater_template() {

	   	if (!current_user_can('edit_posts')) {
	        wp_die('Insufficient permissions');
	    }


	    $template_title = 'REPEFOEL Repater #' . uniqid();
	   	$post_data = array(
	        'post_title' => $template_title,
	        'post_status' => 'publish',
	        'post_type' => 'elementor_library',
	        'meta_input' => array(
	            '_elementor_edit_mode' => 'builder',
	            '_elementor_template_type' => 'REPEFOEL_repeater',
	            '_elementor_version' => ELEMENTOR_VERSION,
	            '_elementor_pro_version' => defined('ELEMENTOR_PRO_VERSION') ? ELEMENTOR_PRO_VERSION : '',
	            '_wp_page_template' => 'elementor_canvas'
	        )
	    );
	    
	    $template_id = wp_insert_post($post_data);
			

	    if (is_wp_error($template_id)) {
	        wp_send_json_error('Failed to create template');
	    }
	    
	    // Set initial Elementor data
	    update_post_meta($template_id, '_elementor_data', '[]');
	    update_post_meta($template_id, '_elementor_page_settings', array());

	    $edit_url = add_query_arg(array(
	        'post' => $template_id,
	        'action' => 'elementor'
	    ), admin_url('post.php'));

	    // Return success with edit URL
	    wp_send_json_success(array(
	        'template_id' => $template_id,
	        'template_title' => $template_title,
	        'edit_url' => $edit_url
	    ));
	}


}


REPEFOEL_ELEMENTOR_ADDON::get_instance();
