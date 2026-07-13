<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Abstract base class shared by all REPEFOEL widgets.
 * Holds every method that was duplicated across the three widget files.
 */
abstract class REPEFOEL_Widget_Base extends \Elementor\Widget_Base {

    /* ------------------------------------------------------------------ */
    /* Shared identity                                                       */
    /* ------------------------------------------------------------------ */

    public function get_icon() {
        return 'REPEFOEL-icon';
    }

    public function get_categories() {
        return [ 'REPEFOEL_category_advanced' ];
    }

    /* ------------------------------------------------------------------ */
    /* Template helpers                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Return all published Elementor templates of type REPEFOEL_repeater.
    */
    
    public function get_all_repeater_template() {
        $templates = get_posts( [
            'post_type'              => 'elementor_library',
            'posts_per_page'         => -1,
            'post_status'            => 'publish',
            'meta_key'               => '_elementor_template_type',
            'meta_value'             => 'REPEFOEL_repeater',
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ] );

        if ( empty( $templates ) ) {
            return [ '' => esc_html__( 'No templates found. Create templates in Elementor Templates section.', 'addoncraft-repeater-for-elementor-acf' ) ];
        }

        $options = [ '' => esc_html__( 'Select Template', 'addoncraft-repeater-for-elementor-acf' ) ];

        foreach ( $templates as $template_id ) {
            $options[ $template_id ] = esc_html( get_the_title( $template_id ) );
        }

        return $options;
    }

    /**
     * Return all ACF repeater field names (cached for one hour).
     */
    public function get_only_repeater_name() {
        if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
            return [];
        }

        $cached = wp_cache_get( 'repefoel_acf_repeater_fields' );

        if ( $cached !== false ) {
            return $cached;
        }

        $all_repeater_fields = [];
        $field_groups        = acf_get_field_groups();

        if ( ! empty( $field_groups ) ) {
            foreach ( $field_groups as $group ) {
                $fields = acf_get_fields( $group['ID'] );
                if ( ! empty( $fields ) ) {
                    foreach ( $fields as $field ) {
                        if ( $field['type'] === 'repeater' ) {
                            $all_repeater_fields[ $field['name'] ] = esc_html( $field['label'] );
                        }
                    }
                }
            }
        }

        wp_cache_set( 'repefoel_acf_repeater_fields', $all_repeater_fields, '', 3600 );

        return $all_repeater_fields;
    }

    /**
     * Return all published posts/pages as an ID => title map.
     */
    protected function get_all_posts() {
        $post_ids = get_posts( [
            'post_type'              => 'any',
            'posts_per_page'         => -1,
            'post_status'            => 'publish',
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ] );

        $options = [];
        foreach ( $post_ids as $id ) {
            $options[ $id ] = get_the_title( $id );
        }

        return $options;
    }

    /**
     * Map each ACF repeater field name to the post IDs that currently have
     * data in it, so the editor can grey out fields that don't apply to the
     * selected source post — no AJAX needed, computed once and cached.
     */
    public function get_repeater_field_post_map() {
        $cached = wp_cache_get( 'repefoel_field_post_map' );

        if ( $cached !== false ) {
            return $cached;
        }

        $fields = array_keys( $this->get_only_repeater_name() );
        $map    = array_fill_keys( $fields, [] );

        if ( ! empty( $fields ) ) {
            foreach ( $this->get_all_posts() as $post_id => $title ) {
                foreach ( $fields as $field_name ) {
                    $value = get_field( $field_name, $post_id );
                    if ( ! empty( $value ) && is_array( $value ) ) {
                        $map[ $field_name ][] = $post_id;
                    }
                }
            }
        }

        wp_cache_set( 'repefoel_field_post_map', $map, '', 3600 );

        return $map;
    }

    /* ------------------------------------------------------------------ */
    /* Render helpers                                                        */
    /* ------------------------------------------------------------------ */

    /**
     * Return Elementor template HTML for the given template ID.
     */
    protected function render_template( $template_id ) {
        if ( ! $template_id || ! class_exists( '\Elementor\Plugin' ) ) {
            return '';
        }
        return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, true );
    }

    /**
     * Extended wp_kses allowlist that covers SVG, <style>, and <script>.
     */
    protected function allow_all_html_tags() {
        $allowed = wp_kses_allowed_html( 'post' );

        // Allow <style> with all attributes Elementor uses when inlining template CSS.
        $allowed['style'] = [
            'type'  => true,
            'id'    => true,
            'class' => true,
            'media' => true,
        ];

        // Allow <script> (needed for Elementor widget JS data attributes).
        $allowed['script'] = [ 'type' => true ];

        // Allow <link rel="stylesheet"> — Elementor may output template CSS as a
        // <link> tag inside get_builder_content_for_display() return value.
        $allowed['link'] = [
            'rel'   => true,
            'href'  => true,
            'type'  => true,
            'media' => true,
            'id'    => true,
            'class' => true,
        ];

        // SVG support for icon-type fields.
        $allowed['svg']   = [
            'class'           => true,
            'aria-hidden'     => true,
            'aria-labelledby' => true,
            'role'            => true,
            'xmlns'           => true,
            'width'           => true,
            'height'          => true,
            'viewbox'         => true,
        ];
        $allowed['g']     = [ 'fill' => true ];
        $allowed['title'] = [ 'title' => true ];
        $allowed['path']  = [ 'd' => true, 'fill' => true ];

        return $allowed;
    }

    /**
     * Replace REPEFOEL placeholder tokens in $template with real values from $repeat row.
     *
     * Text  : {REPEFOEL|field_name|y}  (|y = strip tags)
     * Image : src="#repefoel_repeat_img_field_name"
     * URL   : href="#repefoel_url_field_name"
     */
    protected function parse_template_content( $template, $repeat ) {
        if ( empty( $template ) || empty( $repeat ) || ! is_array( $repeat ) ) {
            return $template;
        }

        // --- Text fields -------------------------------------------------
        preg_match_all( '/\{REPEFOEL\|([^}]+)\}/', $template, $matches );

        if ( ! empty( $matches[1] ) ) {
            foreach ( $matches[1] as $field_group ) {
                $parts      = explode( '|', $field_group );
                $field_name = $parts[0] ?? '';
                $strip_tags = $parts[1] ?? '';

                $clean = sanitize_text_field( $field_name );
                $value = $repeat[ $clean ] ?? '';

                if ( is_array( $value ) ) {
                    $value = '';
                } else {
                    $value = ( $strip_tags === 'y' ) ? wp_strip_all_tags( $value ) : wp_kses_post( $value );
                }

                $template = str_replace( '{REPEFOEL|' . $field_name . '|' . $strip_tags . '}', $value, $template );
            }
        }

        // --- Image fields ------------------------------------------------
        preg_match_all( '/src="(#repefoel_repeat_img_)([^"]*)"/', $template, $img_matches );

        if ( ! empty( $img_matches[2] ) ) {
            foreach ( $img_matches[2] as $field_name ) {
                $clean    = sanitize_text_field( $field_name );
                $image_id = 0;

                if ( isset( $repeat[ $clean ] ) ) {
                    $data = $repeat[ $clean ];

                    if ( is_array( $data ) && isset( $data['id'] ) ) {
                        $image_id = (int) $data['id'];
                    } elseif ( is_numeric( $data ) ) {
                        $image_id = (int) $data;
                    } elseif ( is_string( $data ) && ! empty( $data ) ) {
                        $image_id = (int) attachment_url_to_postid( esc_url_raw( $data ) );
                    }
                }

                $url      = $image_id ? esc_url( wp_get_attachment_url( $image_id ) ) : '';
                $template = str_replace( '#repefoel_repeat_img_' . $field_name, $url, $template );
            }
        }

        // --- URL fields --------------------------------------------------
        preg_match_all( '/href="(#repefoel_url_)([^"]*)"/', $template, $url_matches );

        if ( ! empty( $url_matches[2] ) ) {
            foreach ( $url_matches[2] as $field_name ) {
                $clean    = sanitize_text_field( $field_name );
                $url      = ! empty( $repeat[ $clean ] ) ? esc_url( $repeat[ $clean ] ) : '#';
                $template = str_replace( '#repefoel_url_' . $field_name, $url, $template );
            }
        }

        return $template;
    }
}
