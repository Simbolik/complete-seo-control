<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wordpress.org/plugins/complete-seo-control/
 * @since      1.0.0
 *
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/includes
 * @author     Dmitry Lund <dmitry.lund86@gmail.com>
 */
class Complete_SEO_Control {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Complete_SEO_Control_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = COMPLETE_SEO_CONTROL_VERSION;
		$this->plugin_name = 'complete-seo-control';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		// Hook category base removal into init
		add_action( 'init', array( $this, 'maybe_remove_category_base' ), 999 );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Complete_SEO_Control_Loader. Orchestrates the hooks of the plugin.
	 * - Complete_SEO_Control_i18n. Defines internationalization functionality.
	 * - Complete_SEO_Control_Admin. Defines all hooks for the admin area.
	 * - Complete_SEO_Control_Public. Defines all hooks for the public-facing side.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'includes/class-complete-seo-control-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'includes/class-complete-seo-control-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'admin/class-complete-seo-control-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side.
		 */
		require_once COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'public/class-complete-seo-control-public.php';

		$this->loader = new Complete_SEO_Control_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Complete_SEO_Control_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Complete_SEO_Control_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Complete_SEO_Control_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		// AJAX hooks for homepage settings.
		$this->loader->add_action( 'wp_ajax_csc_save_homepage_settings', $plugin_admin, 'ajax_save_homepage_settings' );
		$this->loader->add_action( 'wp_ajax_csc_get_homepage_settings', $plugin_admin, 'ajax_get_homepage_settings' );

		// AJAX hooks for articles.
		$this->loader->add_action( 'wp_ajax_csc_get_articles_data', $plugin_admin, 'ajax_get_articles_data' );
		$this->loader->add_action( 'wp_ajax_csc_save_article_seo', $plugin_admin, 'ajax_save_article_seo' );

		// AJAX hooks for pages.
		$this->loader->add_action( 'wp_ajax_csc_get_pages_data', $plugin_admin, 'ajax_get_pages_data' );
		$this->loader->add_action( 'wp_ajax_csc_save_page_seo', $plugin_admin, 'ajax_save_page_seo' );

		// AJAX hooks for categories.
		$this->loader->add_action( 'wp_ajax_csc_get_categories_data', $plugin_admin, 'ajax_get_categories_data' );
		$this->loader->add_action( 'wp_ajax_csc_save_category_seo', $plugin_admin, 'ajax_save_category_seo' );

		// AJAX hooks for tags.
		$this->loader->add_action( 'wp_ajax_csc_get_tags_data', $plugin_admin, 'ajax_get_tags_data' );
		$this->loader->add_action( 'wp_ajax_csc_save_tag_seo', $plugin_admin, 'ajax_save_tag_seo' );

		// Add settings link on plugins page.
		$plugin_basename = COMPLETE_SEO_CONTROL_PLUGIN_BASENAME;
		$this->loader->add_filter( "plugin_action_links_{$plugin_basename}", $plugin_admin, 'add_action_links' );
	}

	/**
	 * Register all of the hooks related to public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Complete_SEO_Control_Public( $this->get_plugin_name(), $this->get_version() );

		// Output SEO meta description in the head section.
		$this->loader->add_action( 'wp_head', $plugin_public, 'output_seo_meta_tags', 1 );
		
		// Output canonical URL for non-singular pages (priority 20 to run after WordPress core).
		$this->loader->add_action( 'wp_head', $plugin_public, 'output_canonical_for_non_singular', 20 );
		
		// Filter the document title (browser tab title).
		$this->loader->add_filter( 'document_title_parts', $plugin_public, 'filter_document_title_parts', 10 );
		
		// Filter rendered blocks to replace H1 content (works with all block themes).
		$this->loader->add_filter( 'render_block', $plugin_public, 'filter_heading_block', 10, 2 );
		
		// Filter archive title (H1 for some block themes).
		$this->loader->add_filter( 'get_the_archive_title', $plugin_public, 'filter_archive_title', 10 );
		
		// Filter single post title (alternative for some themes).
		$this->loader->add_filter( 'single_post_title', $plugin_public, 'filter_page_title', 10 );
		
		// Filter the post title (for classic themes using the_title()).
		$this->loader->add_filter( 'the_title', $plugin_public, 'filter_the_title', 10 );
	}

	/**
	 * Remove category base from URLs if enabled.
	 *
	 * @since    1.0.0
	 */
	public function maybe_remove_category_base() {
		$settings = get_option( 'complete_seo_control_homepage', array() );
		$remove_category_base = isset( $settings['remove_category_base'] ) && $settings['remove_category_base'] === '1';

		if ( $remove_category_base ) {
			// Remove category base from URLs
			add_filter( 'category_rewrite_rules', array( $this, 'remove_category_base_rewrite_rules' ) );
			add_filter( 'category_link', array( $this, 'remove_category_base_from_link' ), 10, 2 );
			
			// Handle category query when /category/ is removed
			add_filter( 'request', array( $this, 'handle_category_request' ) );
			
			// Add 301 redirect from old URLs to new ones
			add_action( 'template_redirect', array( $this, 'redirect_old_category_urls' ), 1 );
		} else {
			// When disabled, catch URLs without /category/ and redirect them
			add_action( 'parse_request', array( $this, 'catch_and_redirect_to_category_base' ), 1 );
		}
	}

	/**
	 * Modify rewrite rules to remove category base.
	 *
	 * @since    1.0.0
	 * @param    array $rules    The existing rewrite rules.
	 * @return   array           Modified rewrite rules.
	 */
	public function remove_category_base_rewrite_rules( $rules ) {
		$categories = get_categories( array( 'hide_empty' => false ) );
		$new_rules  = array();

		foreach ( $categories as $category ) {
			$category_nicename = $category->slug;
			$new_rules[ '(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?category_name=$matches[1]&feed=$matches[2]';
			$new_rules[ '(' . $category_nicename . ')/page/?([0-9]{1,})/?$' ] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
			$new_rules[ '(' . $category_nicename . ')/?$' ] = 'index.php?category_name=$matches[1]';
		}

		return $new_rules + $rules;
	}

	/**
	 * Remove /category/ from category links.
	 *
	 * @since    1.0.0
	 * @param    string $link        The category link.
	 * @param    int    $category_id The category ID.
	 * @return   string              Modified link.
	 */
	public function remove_category_base_from_link( $link, $category_id ) {
		$category_base = get_option( 'category_base', 'category' );
		if ( empty( $category_base ) ) {
			$category_base = 'category';
		}
		
		// Remove the category base from the URL
		$link = preg_replace( '/' . preg_quote( $category_base, '/' ) . '\//i', '', $link, 1 );
		
		return $link;
	}

	/**
	 * Handle category requests when base is removed.
	 *
	 * @since    1.0.0
	 * @param    array $query_vars The query variables.
	 * @return   array             Modified query variables.
	 */
	public function handle_category_request( $query_vars ) {
		if ( isset( $query_vars['category_name'] ) ) {
			return $query_vars;
		}

		if ( isset( $query_vars['name'] ) ) {
			$category = get_category_by_slug( $query_vars['name'] );
			if ( $category ) {
				unset( $query_vars['name'] );
				$query_vars['category_name'] = $category->slug;
			}
		}

		return $query_vars;
	}

	/**
	 * Redirect old category URLs with /category/ to new URLs without it.
	 *
	 * @since    1.0.0
	 */
	public function redirect_old_category_urls() {
		// Only redirect on category archives
		if ( ! is_category() ) {
			return;
		}

		$category_base = get_option( 'category_base', 'category' );
		if ( empty( $category_base ) ) {
			$category_base = 'category';
		}

		// Get the current URL
		$current_url = home_url( add_query_arg( array(), $_SERVER['REQUEST_URI'] ) );
		
		// Check if the URL contains /category/
		if ( strpos( $_SERVER['REQUEST_URI'], '/' . $category_base . '/' ) !== false ) {
			// Get the category object
			$category = get_queried_object();
			
			if ( $category && isset( $category->term_id ) ) {
				// Get the new URL without /category/
				$new_url = get_term_link( $category );
				
				if ( ! is_wp_error( $new_url ) && $current_url !== $new_url ) {
					// Perform 301 redirect
					wp_redirect( $new_url, 301 );
					exit;
				}
			}
		}
	}

	/**
	 * Catch and redirect category URLs without /category/ back to URLs with it (when feature is disabled).
	 *
	 * @since    1.0.0
	 * @param    object $wp The WordPress object.
	 */
	public function catch_and_redirect_to_category_base( $wp ) {
		$category_base = get_option( 'category_base', 'category' );
		if ( empty( $category_base ) ) {
			$category_base = 'category';
		}

		// Check if the URL does NOT contain /category/
		if ( strpos( $_SERVER['REQUEST_URI'], '/' . $category_base . '/' ) !== false ) {
			return; // Already has /category/, no redirect needed
		}

		// Parse the request URI to get potential category slug
		$request_uri = trim( $_SERVER['REQUEST_URI'], '/' );
		$uri_parts = explode( '/', $request_uri );
		
		if ( empty( $uri_parts[0] ) ) {
			return;
		}

		// Check if the first part is a category slug
		$potential_category_slug = $uri_parts[0];
		$category = get_category_by_slug( $potential_category_slug );
		
		if ( $category ) {
			// This is a category! Redirect to the proper URL with /category/
			$redirect_url = home_url( '/' . $category_base . '/' . $category->slug . '/' );
			
			// Handle pagination
			if ( isset( $uri_parts[1] ) && $uri_parts[1] === 'page' && isset( $uri_parts[2] ) ) {
				$paged = intval( $uri_parts[2] );
				if ( $paged > 1 ) {
					$redirect_url = home_url( '/' . $category_base . '/' . $category->slug . '/page/' . $paged . '/' );
				}
			}
			
			// Handle feeds
			if ( isset( $uri_parts[1] ) && in_array( $uri_parts[1], array( 'feed', 'rdf', 'rss', 'rss2', 'atom' ) ) ) {
				$redirect_url = home_url( '/' . $category_base . '/' . $category->slug . '/' . $uri_parts[1] . '/' );
			}
			
			// Perform 301 redirect
			wp_redirect( $redirect_url, 301 );
			exit;
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Complete_SEO_Control_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
