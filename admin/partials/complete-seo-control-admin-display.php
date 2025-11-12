<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wordpress.org/plugins/complete-seo-control/
 * @since      1.0.0
 *
 * @package    Complete_SEO_Control
 * @subpackage Complete_SEO_Control/admin/partials
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap csc-admin-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<p class="csc-description">
		<?php esc_html_e( 'Manage SEO settings for your WordPress site. Control meta titles, descriptions, and H1 tags across your homepage, articles, categories, and pages.', 'complete-seo-control' ); ?>
	</p>

	<?php settings_errors(); ?>

	<!-- Tab Navigation -->
	<nav class="nav-tab-wrapper csc-tab-nav" aria-label="<?php esc_attr_e( 'SEO Settings Tabs', 'complete-seo-control' ); ?>">
		<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=homepage" 
		   class="nav-tab <?php echo 'homepage' === $active_tab ? 'nav-tab-active' : ''; ?>"
		   data-tab="homepage">
			<span class="dashicons dashicons-admin-home"></span>
			<?php esc_html_e( 'Homepage', 'complete-seo-control' ); ?>
		</a>
		<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=articles" 
		   class="nav-tab <?php echo 'articles' === $active_tab ? 'nav-tab-active' : ''; ?>"
		   data-tab="articles">
			<span class="dashicons dashicons-admin-post"></span>
			<?php esc_html_e( 'Articles', 'complete-seo-control' ); ?>
		</a>
		<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=categories" 
		   class="nav-tab <?php echo 'categories' === $active_tab ? 'nav-tab-active' : ''; ?>"
		   data-tab="categories">
			<span class="dashicons dashicons-category"></span>
			<?php esc_html_e( 'Categories', 'complete-seo-control' ); ?>
		</a>
		<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=tags" 
		   class="nav-tab <?php echo 'tags' === $active_tab ? 'nav-tab-active' : ''; ?>"
		   data-tab="tags">
			<span class="dashicons dashicons-tag"></span>
			<?php esc_html_e( 'Tags', 'complete-seo-control' ); ?>
		</a>
		<a href="?page=<?php echo esc_attr( $this->plugin_name ); ?>&tab=pages" 
		   class="nav-tab <?php echo 'pages' === $active_tab ? 'nav-tab-active' : ''; ?>"
		   data-tab="pages">
			<span class="dashicons dashicons-admin-page"></span>
			<?php esc_html_e( 'Pages', 'complete-seo-control' ); ?>
		</a>
	</nav>

	<div class="csc-tab-content">
		<!-- Loading Overlay -->
		<div class="csc-loading-overlay" style="display: none;">
			<span class="spinner is-active"></span>
		</div>

		<!-- Homepage Tab -->
		<div id="homepage-tab" class="csc-tab-pane <?php echo 'homepage' === $active_tab ? 'active' : ''; ?>">
			<div class="csc-card">
				<h2><?php esc_html_e( 'Homepage SEO Settings', 'complete-seo-control' ); ?></h2>
				<p><?php esc_html_e( 'Control how your homepage appears in search engines and browser tabs.', 'complete-seo-control' ); ?></p>
				
				<form id="csc-homepage-form" class="csc-form">
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row">
									<label for="page-title"><?php esc_html_e( 'Page Title', 'complete-seo-control' ); ?></label>
								</th>
								<td>
									<input type="text" id="page-title" name="page_title" class="regular-text" />
									<p class="description">
										<?php esc_html_e( 'The title that appears in browser tabs and search results. Recommended length: 50-60 characters.', 'complete-seo-control' ); ?>
										<span class="csc-char-count">
											<strong><?php esc_html_e( 'Characters:', 'complete-seo-control' ); ?></strong> 
											<span id="title-char-count">0</span>
										</span>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="meta-description"><?php esc_html_e( 'Meta Description', 'complete-seo-control' ); ?></label>
								</th>
								<td>
									<textarea id="meta-description" name="meta_description" rows="3" class="large-text"></textarea>
									<p class="description">
										<?php esc_html_e( 'Description shown in search results. Recommended length: 150-160 characters.', 'complete-seo-control' ); ?>
										<span class="csc-char-count">
											<strong><?php esc_html_e( 'Characters:', 'complete-seo-control' ); ?></strong> 
											<span id="meta-char-count">0</span>
										</span>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="h1-text"><?php esc_html_e( 'H1 Heading', 'complete-seo-control' ); ?></label>
								</th>
								<td>
									<input type="text" id="h1-text" name="h1_text" class="regular-text" />
									<p class="description">
										<?php esc_html_e( 'The main H1 heading displayed on your homepage.', 'complete-seo-control' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Canonical URLs', 'complete-seo-control' ); ?>
								</th>
								<td>
									<fieldset>
										<label for="enable-canonical">
											<input type="checkbox" id="enable-canonical" name="enable_canonical" value="1" />
											<?php esc_html_e( 'Enable canonical URLs for homepage and archive pages', 'complete-seo-control' ); ?>
										</label>
										<p class="description">
											<?php esc_html_e( 'Automatically adds canonical URLs to homepage, categories, tags, and archive pages. WordPress already handles canonical URLs for individual posts and pages.', 'complete-seo-control' ); ?>
										</p>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Category URL', 'complete-seo-control' ); ?>
								</th>
								<td>
									<fieldset>
										<label for="remove-category-base">
											<input type="checkbox" id="remove-category-base" name="remove_category_base" value="1" />
											<?php esc_html_e( 'Remove /category/ from category URLs', 'complete-seo-control' ); ?>
										</label>
										<p class="description">
											<?php esc_html_e( 'Makes category URLs more SEO-friendly by removing the default /category/ prefix.', 'complete-seo-control' ); ?><br>
											<?php esc_html_e( 'Example: yoursite.com/uncategorized/ instead of yoursite.com/category/uncategorized/.', 'complete-seo-control' ); ?><br><br>
											<span style="color: #dc3232; font-weight: 600;"><?php esc_html_e( 'Note: After enabling/disabling this, visit Settings → Permalinks to flush rewrite rules.', 'complete-seo-control' ); ?></span><br>
											<?php esc_html_e( 'This feature requires "Post name" permalink structure (Settings → Permalinks). This is the most SEO-friendly structure.', 'complete-seo-control' ); ?><br><br>
											<?php esc_html_e( 'Old URLs with /category/ will automatically redirect (301) to new URLs to prevent duplicate content.', 'complete-seo-control' ); ?>
										</p>
									</fieldset>
								</td>
							</tr>
						</tbody>
					</table>

					<div class="csc-preview-section">
						<h3><?php esc_html_e( 'Search Engine Preview', 'complete-seo-control' ); ?></h3>
						<div class="csc-serp-preview">
							<div class="serp-title" id="preview-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
							<div class="serp-url"><?php echo esc_url( home_url() ); ?></div>
							<div class="serp-description" id="preview-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></div>
						</div>
					</div>

					<p class="submit">
						<button type="submit" class="button button-primary button-large">
							<?php esc_html_e( 'Save Homepage Settings', 'complete-seo-control' ); ?>
						</button>
						<button type="button" id="reset-defaults-btn" class="button button-secondary">
							<?php esc_html_e( 'Reset to Defaults', 'complete-seo-control' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>

		<!-- Articles Tab -->
		<div id="articles-tab" class="csc-tab-pane <?php echo 'articles' === $active_tab ? 'active' : ''; ?>">
			<div class="csc-card">
				<div class="csc-controls">
					<div class="csc-search-box">
						<input type="search" id="article-search" placeholder="<?php esc_attr_e( 'Search articles...', 'complete-seo-control' ); ?>" />
						<button type="button" class="button" id="article-search-btn">
							<?php esc_html_e( 'Search', 'complete-seo-control' ); ?>
						</button>
					</div>
					<div class="csc-stats">
						<span class="stat-item">
							<?php esc_html_e( 'Total:', 'complete-seo-control' ); ?> 
							<strong id="total-articles">0</strong>
						</span>
						<span class="stat-item">
							<?php esc_html_e( 'Custom SEO:', 'complete-seo-control' ); ?> 
							<strong id="custom-articles">0</strong>
						</span>
					</div>
				</div>

				<div id="articles-list" class="csc-items-list">
					<p class="loading-message"><?php esc_html_e( 'Loading articles...', 'complete-seo-control' ); ?></p>
				</div>

				<div id="articles-pagination" class="csc-pagination"></div>
			</div>
		</div>

		<!-- Categories Tab -->
		<div id="categories-tab" class="csc-tab-pane <?php echo 'categories' === $active_tab ? 'active' : ''; ?>">
			<div class="csc-card">
				<div class="csc-controls">
					<div class="csc-search-box">
						<input type="search" id="category-search" placeholder="<?php esc_attr_e( 'Search categories...', 'complete-seo-control' ); ?>" />
						<button type="button" class="button" id="category-search-btn">
							<?php esc_html_e( 'Search', 'complete-seo-control' ); ?>
						</button>
					</div>
				</div>

				<div id="categories-list" class="csc-items-list">
					<p class="loading-message"><?php esc_html_e( 'Loading categories...', 'complete-seo-control' ); ?></p>
				</div>

				<div id="categories-pagination" class="csc-pagination"></div>
			</div>
		</div>

		<!-- Tags Tab -->
		<div id="tags-tab" class="csc-tab-pane <?php echo 'tags' === $active_tab ? 'active' : ''; ?>">
			<div class="csc-card">
				<div class="csc-controls">
					<div class="csc-search-box">
						<input type="search" id="tag-search" placeholder="<?php esc_attr_e( 'Search tags...', 'complete-seo-control' ); ?>" />
						<button type="button" class="button" id="tag-search-btn">
							<?php esc_html_e( 'Search', 'complete-seo-control' ); ?>
						</button>
					</div>
				</div>

				<div id="tags-list" class="csc-items-list">
					<p class="loading-message"><?php esc_html_e( 'Loading tags...', 'complete-seo-control' ); ?></p>
				</div>

				<div id="tags-pagination" class="csc-pagination"></div>
			</div>
		</div>

		<!-- Pages Tab -->
		<div id="pages-tab" class="csc-tab-pane <?php echo 'pages' === $active_tab ? 'active' : ''; ?>">
			<div class="csc-card">
				<div class="csc-controls">
					<div class="csc-search-box">
						<input type="search" id="page-search" placeholder="<?php esc_attr_e( 'Search pages...', 'complete-seo-control' ); ?>" />
						<button type="button" class="button" id="page-search-btn">
							<?php esc_html_e( 'Search', 'complete-seo-control' ); ?>
						</button>
					</div>
					<div class="csc-stats">
						<span class="stat-item">
							<?php esc_html_e( 'Total:', 'complete-seo-control' ); ?> 
							<strong id="total-pages">0</strong>
						</span>
						<span class="stat-item">
							<?php esc_html_e( 'Custom SEO:', 'complete-seo-control' ); ?> 
							<strong id="custom-pages">0</strong>
						</span>
					</div>
				</div>

				<div id="pages-list" class="csc-items-list">
					<p class="loading-message"><?php esc_html_e( 'Loading pages...', 'complete-seo-control' ); ?></p>
				</div>

				<div id="pages-pagination" class="csc-pagination"></div>
			</div>
		</div>
	</div>

	<!-- SEO Edit Modal -->
	<div id="csc-seo-modal" class="csc-modal" style="display: none;">
		<div class="csc-modal-dialog">
			<div class="csc-modal-content">
				<div class="csc-modal-header">
					<h2 id="modal-title"><?php esc_html_e( 'Edit SEO Settings', 'complete-seo-control' ); ?></h2>
					<button type="button" class="csc-modal-close" aria-label="<?php esc_attr_e( 'Close', 'complete-seo-control' ); ?>">
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>
				<div class="csc-modal-body">
					<div class="modal-info">
						<p>
							<strong><?php esc_html_e( 'Item:', 'complete-seo-control' ); ?></strong> 
							<span id="modal-item-name"></span>
						</p>
					</div>

					<div class="modal-field">
						<label for="modal-seo-title"><?php esc_html_e( 'Custom SEO Title', 'complete-seo-control' ); ?></label>
						<input type="text" id="modal-seo-title" class="widefat" />
						<p class="description">
							<?php esc_html_e( 'Leave empty to use default title. Recommended: 50-60 characters.', 'complete-seo-control' ); ?>
							<span class="csc-char-count">
								<strong><?php esc_html_e( 'Characters:', 'complete-seo-control' ); ?></strong> 
								<span id="modal-title-char-count">0</span>
							</span>
						</p>
					</div>

					<div class="modal-field">
						<label for="modal-seo-description"><?php esc_html_e( 'Custom Meta Description', 'complete-seo-control' ); ?></label>
						<textarea id="modal-seo-description" rows="3" class="widefat"></textarea>
						<p class="description">
							<?php esc_html_e( 'Leave empty to auto-generate. Recommended: 150-160 characters.', 'complete-seo-control' ); ?>
							<span class="csc-char-count">
								<strong><?php esc_html_e( 'Characters:', 'complete-seo-control' ); ?></strong> 
								<span id="modal-char-count">0</span>
							</span>
						</p>
					</div>

					<div class="modal-field" id="modal-h1-field" style="display: none;">
						<label for="modal-seo-h1"><?php esc_html_e( 'Custom H1 Heading', 'complete-seo-control' ); ?></label>
						<input type="text" id="modal-seo-h1" class="widefat" />
						<p class="description">
							<?php esc_html_e( 'Leave empty to use default name as H1.', 'complete-seo-control' ); ?>
						</p>
					</div>

					<div class="modal-preview">
						<h3><?php esc_html_e( 'Search Engine Preview', 'complete-seo-control' ); ?></h3>
						<div class="csc-serp-preview">
							<div class="serp-title" id="modal-preview-title"></div>
							<div class="serp-url" id="modal-preview-url"></div>
							<div class="serp-description" id="modal-preview-description"></div>
						</div>
					</div>
				</div>
				<div class="csc-modal-footer">
					<button type="button" class="button button-primary button-large" id="modal-save-btn">
						<?php esc_html_e( 'Save SEO Settings', 'complete-seo-control' ); ?>
					</button>
					<button type="button" class="button button-secondary" id="modal-clear-btn">
						<?php esc_html_e( 'Clear Custom SEO', 'complete-seo-control' ); ?>
					</button>
					<button type="button" class="button" id="modal-cancel-btn">
						<?php esc_html_e( 'Cancel', 'complete-seo-control' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>
