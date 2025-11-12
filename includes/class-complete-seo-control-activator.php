<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wordpress.org/plugins/complete-seo-control/
 * @since      1.0.0
 *
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/includes
 * @author     Dmitry Lund <dmitry.lund86@gmail.com>
 */
class Complete_SEO_Control_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Check if WordPress version is compatible.
		if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
			deactivate_plugins( COMPLETE_SEO_CONTROL_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'Complete SEO Control requires WordPress 5.8 or higher. Please update WordPress to use this plugin.', 'complete-seo-control' ),
				esc_html__( 'Plugin Activation Error', 'complete-seo-control' ),
				array( 'back_link' => true )
			);
		}

		// Set default homepage settings.
		$default_homepage = array(
			'page_title'           => get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ),
			'meta_description'     => get_bloginfo( 'description' ),
			'h1_text'              => '', // Empty by default - no changes unless user sets it.
			'enable_canonical'     => '0', // Disabled by default - let user choose.
			'remove_category_base' => '0', // Disabled by default - let user choose.
		);

		// Merge with existing settings to preserve user data and add missing keys.
		$existing_settings = get_option( 'complete_seo_control_homepage', array() );
		$merged_settings   = array_merge( $default_homepage, $existing_settings );
		update_option( 'complete_seo_control_homepage', $merged_settings );

		// Set default options on first activation.
		if ( false === get_option( 'complete_seo_control_version' ) ) {
			// Store plugin version.
			add_option( 'complete_seo_control_version', COMPLETE_SEO_CONTROL_VERSION );

			// Set activation timestamp.
			add_option( 'complete_seo_control_activated', time() );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();
	}
}
