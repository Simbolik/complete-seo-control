<?php
/**
 * Plugin Name:       Complete SEO Control
 * Plugin URI:        https://wordpress.org/plugins/complete-seo-control/
 * Description:       Comprehensive SEO management for WordPress. Control meta titles, descriptions, and H1 tags for your homepage, articles, categories, tags, and pages with live previews and pagination.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Dmitry Lund
 * Author URI:        https://profiles.wordpress.org/dmitrylund/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       complete-seo-control
 * Domain Path:       /languages
 *
 * @package Complete_SEO_Control
 */

/*
Complete SEO Control is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Complete SEO Control is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Complete SEO Control. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'COMPLETE_SEO_CONTROL_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'COMPLETE_SEO_CONTROL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'COMPLETE_SEO_CONTROL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'COMPLETE_SEO_CONTROL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_complete_seo_control() {
	require_once COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'includes/class-complete-seo-control-activator.php';
	Complete_SEO_Control_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_complete_seo_control() {
	require_once COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'includes/class-complete-seo-control-deactivator.php';
	Complete_SEO_Control_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_complete_seo_control' );
register_deactivation_hook( __FILE__, 'deactivate_complete_seo_control' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'includes/class-complete-seo-control.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_complete_seo_control() {
	$plugin = new Complete_SEO_Control();
	$plugin->run();
}
run_complete_seo_control();
