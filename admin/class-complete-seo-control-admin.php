<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/complete-seo-control/
 * @since      1.0.0
 *
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/admin
 * @author     Dmitry Lund <dmitry.lund86@gmail.com>
 */
class Complete_SEO_Control_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook    The current admin page.
	 */
	public function enqueue_styles( $hook ) {
		// Only enqueue on our plugin pages.
		if ( 'toplevel_page_complete-seo-control' !== $hook ) {
			return;
		}

	wp_enqueue_style(
			$this->plugin_name,
			COMPLETE_SEO_CONTROL_PLUGIN_URL . 'assets/css/complete-seo-control-admin.css',
			array(),
			$this->version . '.' . time(),
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook    The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		// Only enqueue on our plugin pages.
		if ( 'toplevel_page_complete-seo-control' !== $hook ) {
			return;
		}

	wp_enqueue_script(
			$this->plugin_name,
			COMPLETE_SEO_CONTROL_PLUGIN_URL . 'assets/js/complete-seo-control-admin.js',
			array( 'jquery' ),
			$this->version . '.' . time(),
			false
		);

		// Localize script for AJAX.
		wp_localize_script(
			$this->plugin_name,
			'cscAjax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'csc_nonce' ),
			)
		);
	}

	/**
	 * Add plugin admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		add_menu_page(
			__( 'Complete SEO Control', 'complete-seo-control' ),
			__( 'SEO Control', 'complete-seo-control' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-search',
			32
		);
	}

	/**
	 * Add action links to the plugins page.
	 *
	 * @since    1.0.0
	 * @param    array $links    The existing action links.
	 * @return   array           The modified action links.
	 */
	public function add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', 'complete-seo-control' ) . '</a>',
		);
		return array_merge( $settings_link, $links );
	}

	/**
	 * Register plugin settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting(
			'complete_seo_control_homepage',
			'complete_seo_control_homepage',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_homepage_settings' ),
			)
		);
	}

	/**
	 * Sanitize homepage settings.
	 *
	 * @since    1.0.0
	 * @param    array $input    The input array to sanitize.
	 * @return   array           The sanitized array.
	 */
	public function sanitize_homepage_settings( $input ) {
		$sanitized = array();

		if ( isset( $input['page_title'] ) ) {
			$sanitized['page_title'] = sanitize_text_field( $input['page_title'] );
		}

		if ( isset( $input['meta_description'] ) ) {
			$sanitized['meta_description'] = sanitize_textarea_field( $input['meta_description'] );
		}

		if ( isset( $input['h1_text'] ) ) {
			$sanitized['h1_text'] = sanitize_text_field( $input['h1_text'] );
		}

		// Sanitize canonical setting
		if ( isset( $input['enable_canonical'] ) ) {
			$sanitized['enable_canonical'] = ( $input['enable_canonical'] === '1' ) ? '1' : '0';
		}

		return $sanitized;
	}

	/**
	 * Render the admin page.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'complete-seo-control' ) );
		}

		// Get active tab.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'homepage';

		include_once COMPLETE_SEO_CONTROL_PLUGIN_DIR . 'admin/partials/complete-seo-control-admin-display.php';
	}

	/**
	 * Get default homepage settings.
	 *
	 * @since    1.0.0
	 * @return   array    Default settings.
	 */
	private function get_default_homepage_settings() {
		return array(
			'page_title'       => get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ),
			'meta_description' => get_bloginfo( 'description' ),
			'h1_text'          => get_bloginfo( 'name' ),
			'enable_canonical' => '0', // Disabled by default.
		);
	}

	/**
	 * AJAX handler to get homepage settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_homepage_settings() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$saved    = get_option( 'complete_seo_control_homepage', array() );
		$defaults = $this->get_default_homepage_settings();
		$settings = wp_parse_args( $saved, $defaults );

		wp_send_json_success(
			array(
				'settings' => $settings,
				'defaults' => $defaults,
			)
		);
	}

	/**
	 * AJAX handler to save homepage settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_homepage_settings() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Insufficient permissions.', 'complete-seo-control' ) );
		}

		$page_title       = isset( $_POST['page_title'] ) ? sanitize_text_field( wp_unslash( $_POST['page_title'] ) ) : '';
		$meta_description = isset( $_POST['meta_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['meta_description'] ) ) : '';
		$h1_text          = isset( $_POST['h1_text'] ) ? sanitize_text_field( wp_unslash( $_POST['h1_text'] ) ) : '';
		// Get the canonical setting - it's sent as '1' or '0' from JavaScript
		$enable_canonical = isset( $_POST['enable_canonical'] ) && $_POST['enable_canonical'] === '1' ? '1' : '0';

		$settings = array(
			'page_title'       => $page_title,
			'meta_description' => $meta_description,
			'h1_text'          => $h1_text,
			'enable_canonical' => $enable_canonical,
			'updated_at'       => time(),
		);

		update_option( 'complete_seo_control_homepage', $settings );

		// Clear all caches to ensure canonical URL changes take effect immediately.
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		// Clear popular caching plugins.
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
		if ( class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
			\LiteSpeed_Cache_API::purge_all();
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Settings saved successfully.', 'complete-seo-control' ),
				'settings' => $settings,
			)
		);
	}

	/**
	 * AJAX handler to get articles data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_articles_data() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$paged  = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => 20,
			'paged'          => $paged,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
		);

		if ( ! empty( $search ) ) {
			if ( is_numeric( $search ) ) {
				$args['p'] = intval( $search );
			} else {
				$args['s'] = $search;
			}
		}

		$query         = new WP_Query( $args );
		$articles_data = array();

		foreach ( $query->posts as $post ) {
			$custom_seo = get_post_meta( $post->ID, '_csc_post_seo', true );
			$has_custom = ! empty( $custom_seo );

			$custom_title       = $has_custom && ! empty( $custom_seo['title'] ) ? $custom_seo['title'] : '';
			$custom_description = $has_custom && ! empty( $custom_seo['description'] ) ? $custom_seo['description'] : '';

			$last_updated = get_post_meta( $post->ID, '_csc_post_seo_updated', true );

			$articles_data[] = array(
				'ID'                 => $post->ID,
				'title'              => $post->post_title,
				'slug'               => $post->post_name,
				'status'             => $has_custom ? 'custom' : 'default',
				'custom_title'       => $custom_title,
				'custom_description' => $custom_description,
				'last_updated'       => $last_updated ? gmdate( 'Y-m-d H:i', $last_updated ) : '-',
				'edit_url'           => get_edit_post_link( $post->ID, 'raw' ),
				'view_url'           => get_permalink( $post->ID ),
			);
		}

		// Get statistics.
		$total_posts      = wp_count_posts( 'post' )->publish;
		$custom_seo_count = $this->count_posts_with_custom_seo();

		wp_send_json_success(
			array(
				'articles'   => $articles_data,
				'pagination' => array(
					'total_pages'   => $query->max_num_pages,
					'current_page'  => $paged,
					'total_items'   => $query->found_posts,
				),
				'stats'      => array(
					'total_articles'   => $total_posts,
					'custom_seo_count' => $custom_seo_count,
				),
			)
		);
	}

	/**
	 * AJAX handler to save article SEO settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_article_seo() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$post_id            = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$custom_title       = isset( $_POST['custom_title'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_title'] ) ) : '';
		$custom_description = isset( $_POST['custom_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['custom_description'] ) ) : '';

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( __( 'Invalid post or insufficient permissions.', 'complete-seo-control' ) );
		}

		if ( empty( $custom_title ) && empty( $custom_description ) ) {
			delete_post_meta( $post_id, '_csc_post_seo' );
			delete_post_meta( $post_id, '_csc_post_seo_updated' );
		} else {
			$seo_data = array(
				'title'       => $custom_title,
				'description' => $custom_description,
			);
			update_post_meta( $post_id, '_csc_post_seo', $seo_data );
			update_post_meta( $post_id, '_csc_post_seo_updated', time() );
		}

		wp_send_json_success(
			array(
				'message'      => __( 'Article SEO settings saved successfully.', 'complete-seo-control' ),
				'status'       => ( empty( $custom_title ) && empty( $custom_description ) ) ? 'default' : 'custom',
				'last_updated' => ( empty( $custom_title ) && empty( $custom_description ) ) ? '-' : gmdate( 'Y-m-d H:i' ),
			)
		);
	}

	/**
	 * AJAX handler to get pages data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_pages_data() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$paged  = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		$args = array(
			'post_type'      => 'page',
			'posts_per_page' => 20,
			'paged'          => $paged,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		if ( ! empty( $search ) ) {
			if ( is_numeric( $search ) ) {
				$args['p'] = intval( $search );
			} else {
				$args['s'] = $search;
			}
		}

		$query      = new WP_Query( $args );
		$pages_data = array();

		foreach ( $query->posts as $page ) {
			$custom_seo = get_post_meta( $page->ID, '_csc_page_seo', true );
			$has_custom = ! empty( $custom_seo );

			$custom_title       = $has_custom && ! empty( $custom_seo['title'] ) ? $custom_seo['title'] : '';
			$custom_description = $has_custom && ! empty( $custom_seo['description'] ) ? $custom_seo['description'] : '';

			$last_updated = get_post_meta( $page->ID, '_csc_page_seo_updated', true );

			$pages_data[] = array(
				'ID'                 => $page->ID,
				'title'              => $page->post_title,
				'slug'               => $page->post_name,
				'status'             => $has_custom ? 'custom' : 'default',
				'custom_title'       => $custom_title,
				'custom_description' => $custom_description,
				'last_updated'       => $last_updated ? gmdate( 'Y-m-d H:i', $last_updated ) : '-',
				'edit_url'           => get_edit_post_link( $page->ID, 'raw' ),
				'view_url'           => get_permalink( $page->ID ),
			);
		}

		// Get statistics.
		$total_pages_obj  = wp_count_posts( 'page' );
		$total_pages      = isset( $total_pages_obj->publish ) ? $total_pages_obj->publish : 0;
		$custom_seo_count = $this->count_pages_with_custom_seo();

		wp_send_json_success(
			array(
				'pages'      => $pages_data,
				'pagination' => array(
					'total_pages'   => $query->max_num_pages,
					'current_page'  => $paged,
					'total_items'   => $query->found_posts,
				),
				'stats'      => array(
					'total_pages'          => $total_pages,
					'custom_page_seo_count' => $custom_seo_count,
				),
			)
		);
	}

	/**
	 * AJAX handler to save page SEO settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_page_seo() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$post_id            = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$custom_title       = isset( $_POST['custom_title'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_title'] ) ) : '';
		$custom_description = isset( $_POST['custom_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['custom_description'] ) ) : '';

		if ( ! $post_id || ! current_user_can( 'edit_page', $post_id ) ) {
			wp_send_json_error( __( 'Invalid page or insufficient permissions.', 'complete-seo-control' ) );
		}

		if ( empty( $custom_title ) && empty( $custom_description ) ) {
			delete_post_meta( $post_id, '_csc_page_seo' );
			delete_post_meta( $post_id, '_csc_page_seo_updated' );
		} else {
			$seo_data = array(
				'title'       => $custom_title,
				'description' => $custom_description,
			);
			update_post_meta( $post_id, '_csc_page_seo', $seo_data );
			update_post_meta( $post_id, '_csc_page_seo_updated', time() );
		}

		wp_send_json_success(
			array(
				'message'      => __( 'Page SEO settings saved successfully.', 'complete-seo-control' ),
				'status'       => ( empty( $custom_title ) && empty( $custom_description ) ) ? 'default' : 'custom',
				'last_updated' => ( empty( $custom_title ) && empty( $custom_description ) ) ? '-' : gmdate( 'Y-m-d H:i' ),
			)
		);
	}

	/**
	 * AJAX handler to save category SEO settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_category_seo() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$term_id            = isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;
		$custom_title       = isset( $_POST['custom_title'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_title'] ) ) : '';
		$custom_description = isset( $_POST['custom_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['custom_description'] ) ) : '';
		$custom_h1          = isset( $_POST['custom_h1'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_h1'] ) ) : '';

		if ( ! $term_id || ! current_user_can( 'manage_categories' ) ) {
			wp_send_json_error( __( 'Invalid category or insufficient permissions.', 'complete-seo-control' ) );
		}

		if ( empty( $custom_title ) && empty( $custom_description ) && empty( $custom_h1 ) ) {
			delete_term_meta( $term_id, '_csc_category_seo' );
			delete_term_meta( $term_id, '_csc_category_seo_updated' );
		} else {
			$seo_data = array(
				'title'       => $custom_title,
				'description' => $custom_description,
				'h1_text'     => $custom_h1,
			);
			update_term_meta( $term_id, '_csc_category_seo', $seo_data );
			update_term_meta( $term_id, '_csc_category_seo_updated', time() );
		}

		wp_send_json_success(
			array(
				'message'      => __( 'Category SEO settings saved successfully.', 'complete-seo-control' ),
				'status'       => ( empty( $custom_title ) && empty( $custom_description ) ) ? 'default' : 'custom',
				'last_updated' => ( empty( $custom_title ) && empty( $custom_description ) ) ? '-' : gmdate( 'Y-m-d H:i' ),
			)
		);
	}

	/**
	 * AJAX handler to save tag SEO settings.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_tag_seo() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$term_id            = isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;
		$custom_title       = isset( $_POST['custom_title'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_title'] ) ) : '';
		$custom_description = isset( $_POST['custom_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['custom_description'] ) ) : '';
		$custom_h1          = isset( $_POST['custom_h1'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_h1'] ) ) : '';

		if ( ! $term_id || ! current_user_can( 'manage_categories' ) ) {
			wp_send_json_error( __( 'Invalid tag or insufficient permissions.', 'complete-seo-control' ) );
		}

		if ( empty( $custom_title ) && empty( $custom_description ) && empty( $custom_h1 ) ) {
			delete_term_meta( $term_id, '_csc_tag_seo' );
			delete_term_meta( $term_id, '_csc_tag_seo_updated' );
		} else {
			$seo_data = array(
				'title'       => $custom_title,
				'description' => $custom_description,
				'h1_text'     => $custom_h1,
			);
			update_term_meta( $term_id, '_csc_tag_seo', $seo_data );
			update_term_meta( $term_id, '_csc_tag_seo_updated', time() );
		}

		wp_send_json_success(
			array(
				'message'      => __( 'Tag SEO settings saved successfully.', 'complete-seo-control' ),
				'status'       => ( empty( $custom_title ) && empty( $custom_description ) && empty( $custom_h1 ) ) ? 'default' : 'custom',
				'last_updated' => ( empty( $custom_title ) && empty( $custom_description ) && empty( $custom_h1 ) ) ? '-' : gmdate( 'Y-m-d H:i' ),
			)
		);
	}

	/**
	 * AJAX handler to get categories data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_categories_data() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$page   = isset( $_POST['page'] ) ? max( 1, intval( $_POST['page'] ) ) : 1;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;

		$args = array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => $per_page,
			'offset'     => $offset,
		);

		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		$categories = get_terms( $args );

		// Get total count.
		$count_args = array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'fields'     => 'count',
		);
		if ( ! empty( $search ) ) {
			$count_args['search'] = $search;
		}
		$total = intval( wp_count_terms( $count_args ) );

		$items = array();
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $category ) {
				$seo_data   = get_term_meta( $category->term_id, '_csc_category_seo', true );
				$has_custom = ! empty( $seo_data );

				$items[] = array(
					'id'          => $category->term_id,
					'name'        => $category->name,
					'slug'        => $category->slug,
					'url'         => get_term_link( $category ),
					'status'      => $has_custom ? 'custom' : 'default',
					'title'       => $has_custom && ! empty( $seo_data['title'] ) ? $seo_data['title'] : '',
					'description' => $has_custom && ! empty( $seo_data['description'] ) ? $seo_data['description'] : '',
					'h1'          => $has_custom && ! empty( $seo_data['h1_text'] ) ? $seo_data['h1_text'] : '',
				);
			}
		}

		wp_send_json_success(
			array(
				'items'       => $items,
				'total'       => $total,
				'total_pages' => ceil( $total / $per_page ),
				'current_page' => $page,
			)
		);
	}

	/**
	 * AJAX handler to get tags data.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_tags_data() {
		check_ajax_referer( 'csc_nonce', 'nonce' );

		$page   = isset( $_POST['page'] ) ? max( 1, intval( $_POST['page'] ) ) : 1;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		$per_page = 20;
		$offset   = ( $page - 1 ) * $per_page;

		$args = array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => $per_page,
			'offset'     => $offset,
		);

		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		$tags = get_terms( $args );

		// Get total count.
		$count_args = array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'fields'     => 'count',
		);
		if ( ! empty( $search ) ) {
			$count_args['search'] = $search;
		}
		$total = intval( wp_count_terms( $count_args ) );

		$items = array();
		if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
			foreach ( $tags as $tag ) {
				$seo_data   = get_term_meta( $tag->term_id, '_csc_tag_seo', true );
				$has_custom = ! empty( $seo_data );

				$items[] = array(
					'id'          => $tag->term_id,
					'name'        => $tag->name,
					'slug'        => $tag->slug,
					'url'         => get_term_link( $tag ),
					'status'      => $has_custom ? 'custom' : 'default',
					'title'       => $has_custom && ! empty( $seo_data['title'] ) ? $seo_data['title'] : '',
					'description' => $has_custom && ! empty( $seo_data['description'] ) ? $seo_data['description'] : '',
					'h1'          => $has_custom && ! empty( $seo_data['h1_text'] ) ? $seo_data['h1_text'] : '',
				);
			}
		}

		wp_send_json_success(
			array(
				'items'       => $items,
				'total'       => $total,
				'total_pages' => ceil( $total / $per_page ),
				'current_page' => $page,
			)
		);
	}

	/**
	 * Count posts with custom SEO settings.
	 *
	 * @since    1.0.0
	 * @return   int    Count of posts with custom SEO.
	 */
	private function count_posts_with_custom_seo() {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
				WHERE meta_key = %s AND meta_value != ''",
				'_csc_post_seo'
			)
		);

		return intval( $count );
	}

	/**
	 * Count pages with custom SEO settings.
	 *
	 * @since    1.0.0
	 * @return   int    Count of pages with custom SEO.
	 */
	private function count_pages_with_custom_seo() {
		global $wpdb;

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
				WHERE meta_key = %s AND meta_value != ''",
				'_csc_page_seo'
			)
		);

		return intval( $count );
	}
}
