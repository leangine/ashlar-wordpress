<?php
/**
 * Plugin bootstrap.
 *
 * @package ashlar-kits
 */

namespace Ashlar\Kits;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's features. Nothing runs on the front end unless a kit needs it.
 */
final class Plugin {

	/**
	 * Start the plugin.
	 *
	 * @return void
	 */
	public static function boot(): void {
		load_plugin_textdomain( 'ashlar-kits', false, dirname( plugin_basename( ASHLAR_KITS_FILE ) ) . '/languages' );
		add_action( 'init', array( self::class, 'register_pattern_category' ) );
	}

	/**
	 * Register a pattern category so kit patterns group together in the inserter.
	 *
	 * @return void
	 */
	public static function register_pattern_category(): void {
		register_block_pattern_category(
			'ashlar-kits',
			array( 'label' => __( 'Ashlar kits', 'ashlar-kits' ) )
		);
	}
}
