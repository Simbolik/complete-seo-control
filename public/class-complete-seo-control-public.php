<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/complete-seo-control/
 * @since      1.0.0
 *
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for outputting SEO meta tags.
 *
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/public
 * @author     Dmitry Lund <dmitry.lund86@gmail.com>
 */
class Complete_SEO_Control_Public {

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
	 * Track if H1 has been replaced (only replace first H1).
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool    $h1_replaced    Whether H1 has been replaced.
	 */
	private $h1_replaced = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name       The name of the plugin.
	 * @param    string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Output custom SEO meta description in the head section.
	 * Title is handled by filter_document_title_parts() to avoid duplicates.
	 *
	 * @since    1.0.0
	 */
	public function output_seo_meta_tags() {
		$description = '';

		if ( is_front_page() || is_home() ) {
			// Homepage SEO.
			$homepage_settings = get_option( 'complete_seo_control_homepage', array() );
			if ( ! empty( $homepage_settings['meta_description'] ) ) {
				$description = $homepage_settings['meta_description'];
			}
		} elseif ( is_single() ) {
			// Single post SEO.
			$post_id = get_the_ID();
			$post_seo = get_post_meta( $post_id, '_csc_post_seo', true );
			if ( ! empty( $post_seo['description'] ) ) {
				$description = $post_seo['description'];
			}
		} elseif ( is_page() ) {
			// Page SEO.
			$page_id = get_the_ID();
			$page_seo = get_post_meta( $page_id, '_csc_page_seo', true );
			if ( ! empty( $page_seo['description'] ) ) {
				$description = $page_seo['description'];
			}
		} elseif ( is_category() ) {
			// Category SEO.
			$category = get_queried_object();
			if ( $category ) {
				$category_seo = get_term_meta( $category->term_id, '_csc_category_seo', true );
				if ( ! empty( $category_seo['description'] ) ) {
					$description = $category_seo['description'];
				}
			}
		}

		// Output meta description if set.
		if ( ! empty( $description ) ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
		}
	}

	/**
	 * Filter the document title parts to set custom title.
	 * This is the proper WordPress way to handle titles without duplicates.
	 *
	 * @since    1.0.0
	 * @param    array $title    The document title parts.
	 * @return   array           The filtered document title parts.
	 */
	public function filter_document_title_parts( $title ) {
		$custom_title = '';
		
		if ( is_front_page() || is_home() ) {
			$homepage_settings = get_option( 'complete_seo_control_homepage', array() );
			if ( ! empty( $homepage_settings['page_title'] ) ) {
				$custom_title = $homepage_settings['page_title'];
			}
		} elseif ( is_single() ) {
			$post_id = get_the_ID();
			$post_seo = get_post_meta( $post_id, '_csc_post_seo', true );
			if ( ! empty( $post_seo['title'] ) ) {
				$custom_title = $post_seo['title'];
			}
		} elseif ( is_page() ) {
			$page_id = get_the_ID();
			$page_seo = get_post_meta( $page_id, '_csc_page_seo', true );
			if ( ! empty( $page_seo['title'] ) ) {
				$custom_title = $page_seo['title'];
			}
		} elseif ( is_category() ) {
			$category = get_queried_object();
			if ( $category ) {
				$category_seo = get_term_meta( $category->term_id, '_csc_category_seo', true );
				if ( ! empty( $category_seo['title'] ) ) {
					$custom_title = $category_seo['title'];
				}
			}
		}
		
		// If we have a custom title, replace the title part.
		if ( ! empty( $custom_title ) ) {
			$title['title'] = $custom_title;
		}
		
		return $title;
	}

	/**
	 * Filter rendered block content to replace H1 tags.
	 * Works with block themes by filtering the actual HTML output.
	 *
	 * @since    1.0.0
	 * @param    string $block_content  The block content.
	 * @param    array  $block          The block data.
	 * @return   string                 Filtered block content.
	 */
	public function filter_heading_block( $block_content, $block ) {
		// Only on homepage.
		if ( ! is_front_page() && ! is_home() ) {
			return $block_content;
		}
		
		// If H1 already replaced, don't process any more blocks.
		if ( $this->h1_replaced ) {
			return $block_content;
		}
		
		// Only filter heading blocks.
		if ( empty( $block['blockName'] ) || $block['blockName'] !== 'core/heading' ) {
			return $block_content;
		}
		
		// CRITICAL: Only filter H1 (level 1 headings).
		// H2 = level 2, H3 = level 3, etc.
		if ( empty( $block['attrs']['level'] ) || (int) $block['attrs']['level'] !== 1 ) {
			return $block_content;
		}
		
		// Double-check: block content must contain <h1 tag.
		if ( stripos( $block_content, '<h1' ) === false ) {
			return $block_content;
		}
		
		// Get custom H1 text.
		$homepage_settings = get_option( 'complete_seo_control_homepage', array() );
		if ( empty( $homepage_settings['h1_text'] ) ) {
			return $block_content;
		}
		
		$custom_h1 = $homepage_settings['h1_text'];
		
		// Replace H1 content ONLY - preserves all attributes.
		$block_content = preg_replace(
			'/<h1([^>]*)>.*?<\/h1>/is',
			'<h1$1>' . esc_html( $custom_h1 ) . '</h1>',
			$block_content,
			1
		);
		
		// Mark H1 as replaced so we don't process more blocks.
		$this->h1_replaced = true;
		
		return $block_content;
	}

	/**
	 * Filter the archive title (used by some block themes for H1).
	 * DISABLED: This was causing unintended side effects.
	 * H1 replacement is handled by filter_heading_block() instead.
	 *
	 * @since    1.0.0
	 * @param    string $title    The archive title.
	 * @return   string           The filtered title.
	 */
	public function filter_archive_title( $title ) {
		// Return title unchanged to prevent side effects.
		return $title;
	}

	/**
	 * Filter the page title (alternative hook for some themes).
	 * DISABLED: This was causing unintended side effects.
	 * H1 replacement is handled by filter_heading_block() instead.
	 *
	 * @since    1.0.0
	 * @param    string $title    The page title.
	 * @return   string           The filtered title.
	 */
	public function filter_page_title( $title ) {
		// Return title unchanged to prevent side effects.
		return $title;
	}

	/**
	 * Filter the post title for homepage.
	 * DISABLED: This was causing H2 post titles to also be changed.
	 * H1 replacement is handled by filter_heading_block() instead.
	 *
	 * @since    1.0.0
	 * @param    string $title    The post title.
	 * @return   string           The filtered title.
	 */
	public function filter_the_title( $title ) {
		// Return title unchanged to prevent affecting post titles (H2 tags).
		return $title;
	}

	/**
	 * Output canonical URL for non-singular pages.
	 * WordPress core already handles canonical tags for singular content (posts, pages)
	 * via rel_canonical(). This function complements it by adding canonical tags for
	 * archive views and the home page.
	 *
	 * @since    1.0.0
	 */
	public function output_canonical_for_non_singular() {
		// Check if canonical URLs are enabled.
		$settings = get_option( 'complete_seo_control_homepage', array() );
		
		// If enable_canonical is not set, default to enabled (backward compatibility).
		$canonical_enabled = isset( $settings['enable_canonical'] ) ? $settings['enable_canonical'] : '1';
		
		// Only output if explicitly enabled.
		if ( $canonical_enabled !== '1' ) {
			return;
		}

		// Let WordPress handle singular content (posts, pages, CPTs).
		if ( is_singular() ) {
			return;
		}

		$canonical = '';
		$paged     = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

		// Homepage / blog index.
		if ( is_front_page() || is_home() ) {
			if ( $paged > 1 ) {
				$canonical = get_pagenum_link( $paged );
			} else {
				$canonical = home_url( '/' );
			}
		}

		// Category archive.
		elseif ( is_category() ) {
			$category = get_queried_object();
			if ( $category && ! is_wp_error( $category ) ) {
				if ( $paged > 1 ) {
					$canonical = get_pagenum_link( $paged );
				} else {
					$canonical = get_term_link( $category );
				}
			}
		}

		// Tag archive.
		elseif ( is_tag() ) {
			$tag = get_queried_object();
			if ( $tag && ! is_wp_error( $tag ) ) {
				if ( $paged > 1 ) {
					$canonical = get_pagenum_link( $paged );
				} else {
					$canonical = get_term_link( $tag );
				}
			}
		}

		// Custom taxonomy archive.
		elseif ( is_tax() ) {
			$term = get_queried_object();
			if ( $term && ! is_wp_error( $term ) ) {
				if ( $paged > 1 ) {
					$canonical = get_pagenum_link( $paged );
				} else {
					$canonical = get_term_link( $term );
				}
			}
		}

		// Post type archive.
		elseif ( is_post_type_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			if ( $post_type ) {
				if ( $paged > 1 ) {
					$canonical = get_pagenum_link( $paged );
				} else {
					$canonical = get_post_type_archive_link( $post_type );
				}
			}
		}

		// Author archive.
		elseif ( is_author() ) {
			$author = get_queried_object();
			if ( $author ) {
				if ( $paged > 1 ) {
					$canonical = get_pagenum_link( $paged );
				} else {
					$canonical = get_author_posts_url( $author->ID );
				}
			}
		}

		// Output canonical tag if we have a valid URL.
		if ( ! empty( $canonical ) && ! is_wp_error( $canonical ) ) {
			echo '<link rel="canonical" href="' . esc_url( $canonical ) . "\" />\n";
		}
	}
}
