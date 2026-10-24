<?php
/**
 * Plugin Name:       Ashlar Kits
 * Plugin URI:        https://example.com/ashlar
 * Description:       Site kits for Ashlar: block patterns, page templates and a kit importer. Native blocks only, so it stays light. Elementor support lives in a separate add-on.
 * Version:           0.1.0
 * Requires at least: 7.0
 * Requires PHP:      8.2
 * Author:            Leangine
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ashlar-kits
 *
 * @package ashlar-kits
 */

defined( 'ABSPATH' ) || exit;

define( 'ASHLAR_KITS_VERSION', '0.1.0' );
define( 'ASHLAR_KITS_FILE', __FILE__ );
define( 'ASHLAR_KITS_DIR', plugin_dir_path( __FILE__ ) );

require_once ASHLAR_KITS_DIR . 'src/class-plugin.php';

add_action( 'plugins_loaded', array( \Ashlar\Kits\Plugin::class, 'boot' ) );
