<?php
/**
 * Ashlar theme setup.
 *
 * Block theme: layout, colors and type come from theme.json, so there is no theme stylesheet to load on
 * every page. Add scripts and styles only where they are needed.
 *
 * @package ashlar
 */

defined( 'ABSPATH' ) || exit;

define( 'ASHLAR_THEME_VERSION', '0.1.0' );

add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'ashlar', get_template_directory() . '/languages' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'wp-block-styles' );
		add_editor_style( 'assets/css/editor.css' );
	}
);

// Load each core block's CSS only on pages that use the block.
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
