<?php
/**
 * Custom Elementor Control: Repeater Template Field
 *
 * @package REPEFOEL ElementorAddon
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class REPEFOEL_repeater_control extends \Elementor\Base_Data_Control {

    /**
     * Get control type.
     *
     * @return string
     */
    public function get_type() {
        return 'repeater_template_select';
    }

    /**
     * Default settings.
     *
     * @return array
     */
    protected function get_default_settings() {
        return [
            'options' => [], // Selector options.
            'label'   => esc_html__( 'Choose a template', 'addoncraft-repeater-for-elementor-acf' ),
        ];
    }

    /**
     * Render control in editor (Backbone.js template).
     */
    public function content_template() {
        ?>
        <div class="elementor-control-field">
            <label class="elementor-control-title" for="repefoel-template-select" >{{{ data.label }}}</label>
            <div class="elementor-control-input-wrapper">
                <select class="elementor-select2" type="select2" data-setting="{{ data.name }}" id="repefoel-template-select">
                    <# _.each( data.options, function( option_title, option_value ) { #>
                        <option value="{{ option_value }}">{{{ _.escape(option_title) }}}</option>
                    <# } ); #>
                </select>
            </div>
        </div>

        <div class="elementor-control-field repefoel-template-actions" style="justify-content: center; margin-top: 15px;">
            <button type="button" class="elementor-button elementor-button-success" data-action="add" data-after-action="switch_document">
                <?php echo esc_html__( 'Add template', 'addoncraft-repeater-for-elementor-acf' ); ?>
            </button>
            <button type="button" class="elementor-button elementor-button-success" data-action="edit" data-after-action="switch_document" style="display:none;">
                <?php echo esc_html__( 'Edit template', 'addoncraft-repeater-for-elementor-acf' ); ?>
            </button>
        </div>
        <?php
    }
}
