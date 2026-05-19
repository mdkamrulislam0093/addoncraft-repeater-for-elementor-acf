<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class REPEFOEL_Post_Title_Tag extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'REPEFOEL-post-title';
	}

	public function get_title() {
		return esc_html__( 'Post Title', 'addoncraft-repeater-for-elementor-acf' );
	}

	public function get_group() {
		return 'REPEFOEL-dynamic-tag';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	public function render() {
		$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );

		if ( ! empty( $document ) && $document->get_name() === 'REPEFOEL_repeater' ) {
			// Inside the loop item template editor: resolve against the configured preview post.
			$preview_id = (int) $document->get_settings( 'preview_id' );
			if ( ! $preview_id ) {
				$preview_id = (int) $document->get_settings( 'REPEFOEL_preview_post' );
			}

			echo $preview_id
				? esc_html( get_the_title( $preview_id ) )
				: esc_html__( 'Post Title', 'addoncraft-repeater-for-elementor-acf' );
		} else {
			// Frontend: $query->the_post() has set the global post to the current loop item.
			echo esc_html( get_the_title() );
		}
	}
}
