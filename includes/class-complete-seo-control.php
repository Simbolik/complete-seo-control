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
		$this->loader->add_action( 'wp_ajax_csc_save_category_seo', $plugin_admin, 'ajax_save_category_seo' );

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
