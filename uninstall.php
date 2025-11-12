<?php
/**
 * Complete SEO Control - Uninstall Script
 *
 * This file is triggered when the plugin is deleted via the WordPress admin.
 * It removes all data created by the plugin from the database.
 *
 * @package    Complete_SEO_Control
 * @author     Dmitry Lund <dmitry.lund86@gmail.com>
 * @license    GPL-2.0+
 * @since      1.0.0
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove all plugin options from wp_options table
 */
delete_option( 'complete_seo_control_homepage' );
delete_option( 'complete_seo_control_version' );

/**
 * Remove all post meta data
 * Meta keys:
 * - _csc_post_seo
 * - _csc_post_seo_updated
 * - _csc_page_seo
 * - _csc_page_seo_updated
 */
global $wpdb;

// Delete post meta for all posts
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key IN ('_csc_post_seo', '_csc_post_seo_updated', '_csc_page_seo', '_csc_page_seo_updated')" );

/**
 * Remove all term meta data
 * Meta keys:
 * - _csc_category_seo
 * - _csc_category_seo_updated
 * - _csc_tag_seo
 * - _csc_tag_seo_updated
 */
$wpdb->query( "DELETE FROM $wpdb->termmeta WHERE meta_key IN ('_csc_category_seo', '_csc_category_seo_updated', '_csc_tag_seo', '_csc_tag_seo_updated')" );

/**
 * Clear any cached data
 */
wp_cache_flush();
