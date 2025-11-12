/**
 * Complete SEO Control - Admin JavaScript
 *
 * @package Complete_SEO_Control
 * @since   1.0.0
 */

(function($) {
	'use strict';

	// Global state
	const state = {
		currentTab: 'homepage',
		currentPage: {
			articles: 1,
			categories: 1,
			tags: 1,
			pages: 1
		},
		currentItem: {
			type: null,
			id: null,
			name: null,
			slug: null
		}
	};

	/**
	 * Initialize plugin
	 */
	$(document).ready(function() {
		initTabs();
		initHomepage();
		initArticles();
		initCategories();
		initTags();
		initPages();
		initModal();
	});

	/**
	 * Initialize tab switching
	 */
	function initTabs() {
		$('.csc-tab-nav .nav-tab').on('click', function(e) {
			e.preventDefault();
			const tab = $(this).data('tab');
			switchTab(tab);
		});

		// Get active tab from URL or default to homepage
		const urlParams = new URLSearchParams(window.location.search);
		const activeTab = urlParams.get('tab') || 'homepage';
		state.currentTab = activeTab;
	}

	/**
	 * Switch between tabs
	 */
	function switchTab(tab) {
		state.currentTab = tab;
		
		// Update tab navigation
		$('.csc-tab-nav .nav-tab').removeClass('nav-tab-active');
		$('.csc-tab-nav .nav-tab[data-tab="' + tab + '"]').addClass('nav-tab-active');
		
		// Update tab content
		$('.csc-tab-pane').removeClass('active');
		$('#' + tab + '-tab').addClass('active');
		
		// Load data if needed
		if (tab === 'articles' && !$('#articles-list table').length) {
			loadArticles(1);
		} else if (tab === 'categories' && !$('#categories-list table').length) {
			loadCategories(1);
		} else if (tab === 'tags' && !$('#tags-list table').length) {
			loadTags(1);
		} else if (tab === 'pages' && !$('#pages-list table').length) {
			loadPages(1);
		}
	}

	/**
	 * ========================================================================
	 * HOMEPAGE TAB
	 * ========================================================================
	 */

	function initHomepage() {
		loadHomepageSettings();
		
		// Form submission
		$('#csc-homepage-form').on('submit', function(e) {
			e.preventDefault();
			saveHomepageSettings();
		});
		
		// Live preview updates
		$('#page-title').on('input', function() {
			updateHomepagePreview();
			updateTitleCharCount();
		});
		$('#meta-description').on('input', function() {
			updateHomepagePreview();
			updateDescriptionCharCount();
		});
		$('#h1-text').on('input', updateHomepagePreview);
		
		// Reset to defaults
		$('#reset-defaults-btn').on('click', resetToDefaults);
	}

	function loadHomepageSettings() {
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: {
				action: 'csc_get_homepage_settings',
				nonce: cscAjax.nonce
			},
			success: function(response) {
				if (response.success) {
					const settings = response.data.settings;
					$('#page-title').val(settings.page_title);
					$('#meta-description').val(settings.meta_description);
					$('#h1-text').val(settings.h1_text);
					$('#enable-canonical').prop('checked', settings.enable_canonical == '1');
					$('#remove-category-base').prop('checked', settings.remove_category_base == '1');
					
					updateHomepagePreview();
					updateTitleCharCount();
					updateDescriptionCharCount();
				}
			}
		});
	}

	function saveHomepageSettings() {
		const button = $('#csc-homepage-form button[type="submit"]');
		button.prop('disabled', true).text('Saving...');
		
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: {
				action: 'csc_save_homepage_settings',
				nonce: cscAjax.nonce,
				page_title: $('#page-title').val(),
				meta_description: $('#meta-description').val(),
				h1_text: $('#h1-text').val(),
				enable_canonical: $('#enable-canonical').is(':checked') ? '1' : '0',
				remove_category_base: $('#remove-category-base').is(':checked') ? '1' : '0'
			},
			success: function(response) {
				if (response.success) {
					showNotice('Settings saved successfully!', 'success');
					button.text('Saved!');
					setTimeout(function() {
						button.prop('disabled', false).text('Save Homepage Settings');
					}, 2000);
				} else {
					showNotice('Error: ' + response.data, 'error');
					button.prop('disabled', false).text('Save Homepage Settings');
				}
			},
			error: function() {
				showNotice('Error saving settings.', 'error');
				button.prop('disabled', false).text('Save Homepage Settings');
			}
		});
	}

	function updateHomepagePreview() {
		const title = $('#page-title').val() || 'Homepage Title';
		const description = $('#meta-description').val() || 'Meta description...';
		
		$('#preview-title').text(title);
		$('#preview-description').text(description);
		
		// Update preview character count warnings
		updatePreviewWarnings();
	}

	function updateTitleCharCount() {
		const count = $('#page-title').val().length;
		const $counter = $('#title-char-count');
		
		if ($counter.length) {
			$counter.text(count);
			
			// Color code: Red if > 60, yellow if > 50, blue otherwise
			$counter.parent().css('color', count > 60 ? '#dc3232' : (count > 50 ? '#f0ad4e' : '#2271b1'));
		} else {
			console.warn('Title char counter element not found:', '#title-char-count');
		}
	}

	function updateDescriptionCharCount() {
		const count = $('#meta-description').val().length;
		const $counter = $('#meta-char-count');
		
		if ($counter.length) {
			$counter.text(count);
			
			// Color code: Red if > 160, yellow if > 150, blue otherwise
			$counter.parent().css('color', count > 160 ? '#dc3232' : (count > 150 ? '#f0ad4e' : '#2271b1'));
		}
	}

	function updatePreviewWarnings() {
		const titleLength = $('#page-title').val().length;
		const descLength = $('#meta-description').val().length;
		
		// Remove existing warnings
		$('#preview-title-warning, #preview-desc-warning').remove();
		
		// Add title warning if needed
		if (titleLength > 60) {
			$('#preview-title').after('<div id="preview-title-warning" style="color: #dc3232; font-size: 11px; margin-top: 2px;">⚠ Title is too long (' + titleLength + ' chars, recommended: 50-60)</div>');
		} else if (titleLength > 50) {
			$('#preview-title').after('<div id="preview-title-warning" style="color: #f0ad4e; font-size: 11px; margin-top: 2px;">⚠ Title is getting long (' + titleLength + ' chars, recommended: 50-60)</div>');
		}
		
		// Add description warning if needed
		if (descLength > 160) {
			$('#preview-description').after('<div id="preview-desc-warning" style="color: #dc3232; font-size: 11px; margin-top: 2px;">⚠ Description is too long (' + descLength + ' chars, recommended: 150-160)</div>');
		} else if (descLength > 150) {
			$('#preview-description').after('<div id="preview-desc-warning" style="color: #f0ad4e; font-size: 11px; margin-top: 2px;">⚠ Description is getting long (' + descLength + ' chars, recommended: 150-160)</div>');
		}
	}

	function resetToDefaults() {
		if (!confirm('Are you sure you want to reset to default values?')) {
			return;
		}
		
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: {
				action: 'csc_get_homepage_settings',
				nonce: cscAjax.nonce
			},
			success: function(response) {
				if (response.success && response.data.defaults) {
					const defaults = response.data.defaults;
					$('#page-title').val(defaults.page_title);
					$('#meta-description').val(defaults.meta_description);
					$('#h1-text').val(defaults.h1_text);
					$('#enable-canonical').prop('checked', defaults.enable_canonical == '1');
					
					updateHomepagePreview();
					updateTitleCharCount();
					updateDescriptionCharCount();
					showNotice('Reset to defaults. Remember to save.', 'info');
				}
			}
		});
	}

	/**
	 * ========================================================================
	 * ARTICLES TAB
	 * ========================================================================
	 */

	function initArticles() {
		// Search
		$('#article-search-btn').on('click', function() {
			loadArticles(1, $('#article-search').val());
		});
		
		$('#article-search').on('keypress', function(e) {
			if (e.which === 13) {
				loadArticles(1, $(this).val());
			}
		});
		
		// Edit button (delegated)
		$(document).on('click', '.edit-article-seo', function() {
			const data = $(this).data();
			openModal('article', data);
		});
	}

	function loadArticles(page, search) {
		page = page || state.currentPage.articles;
		search = search || '';
		
		showLoading(true);
		
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: {
				action: 'csc_get_articles_data',
				nonce: cscAjax.nonce,
				paged: page,
				search: search
			},
			success: function(response) {
				if (response.success) {
					renderArticles(response.data.articles);
					renderPagination('articles', response.data.pagination);
					updateStats('articles', response.data.stats);
					state.currentPage.articles = page;
				}
				showLoading(false);
			},
			error: function() {
				showNotice('Error loading articles.', 'error');
				showLoading(false);
			}
		});
	}

	function renderArticles(articles) {
		if (!articles.length) {
			$('#articles-list').html('<p class="loading-message">No articles found.</p>');
			return;
		}
		
		let html = '<table class="wp-list-table widefat fixed striped">';
		html += '<thead><tr>';
		html += '<th style="width: 60px;">ID</th>';
		html += '<th>Title</th>';
		html += '<th style="width: 100px;">Status</th>';
		html += '<th style="width: 120px;">Actions</th>';
		html += '</tr></thead>';
		html += '<tbody>';
		
		articles.forEach(function(article) {
			const badge = article.status === 'custom' ? 
				'<span class="csc-badge badge-custom">Custom</span>' : 
				'<span class="csc-badge badge-default">Default</span>';
			
			html += '<tr>';
			html += '<td>' + article.ID + '</td>';
			html += '<td>';
			html += '<strong>' + escapeHtml(article.title) + '</strong>';
			html += '<div class="row-actions">';
			html += '<span class="view"><a href="' + article.view_url + '" target="_blank">View</a></span>';
			html += '</div>';
			html += '</td>';
			html += '<td>' + badge + '</td>';
			html += '<td><button type="button" class="button button-small edit-article-seo" ' +
				'data-type="article" ' +
				'data-id="' + article.ID + '" ' +
				'data-name="' + escapeHtml(article.title) + '" ' +
				'data-slug="' + escapeHtml(article.slug) + '" ' +
				'data-title="' + escapeHtml(article.custom_title) + '" ' +
				'data-description="' + escapeHtml(article.custom_description) + '">' +
				'Edit SEO</button></td>';
			html += '</tr>';
		});
		
		html += '</tbody></table>';
		$('#articles-list').html(html);
	}

	/**
	 * ========================================================================
	 * CATEGORIES TAB
	 * ========================================================================
	 */

	function initCategories() {
		// Search
		$('#category-search-btn').on('click', function() {
			loadCategories(1, $('#category-search').val());
		});
		
		$('#category-search').on('keypress', function(e) {
			if (e.which === 13) {
				loadCategories(1, $(this).val());
			}
		});
		
		// Edit button (delegated)
		$(document).on('click', '.edit-category-seo', function() {
			const data = $(this).data();
			openModal('category', data);
		});
	}

	function loadCategories(page, search) {
		page = page || state.currentPage.categories;
		search = search || '';
		
		showLoading(true);
		
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: {
				action: 'csc_get_categories_data',
				nonce: cscAjax.nonce,
				page: page,
				search: search
			},
			success: function(response) {
				if (response.success) {
					renderCategories(response.data.items);
					renderPagination('categories', {
						current_page: response.data.current_page,
						total_pages: response.data.total_pages
					});
					state.currentPage.categories = page;
				}
				showLoading(false);
			},
			error: function() {
				showNotice('Error loading categories.', 'error');
				showLoading(false);
			}
		});
	}

	function renderCategories(categories) {
		if (!categories.length) {
			$('#categories-list').html('<p class="loading-message">No categories found.</p>');
			return;
		}
		
		let html = '<table class="wp-list-table widefat fixed striped">';
		html += '<thead><tr>';
		html += '<th style="width: 60px;">ID</th>';
		html += '<th>Category Name</th>';
		html += '<th style="width: 100px;">Status</th>';
		html += '<th style="width: 120px;">Actions</th>';
		html += '</tr></thead>';
		html += '<tbody>';
		
		categories.forEach(function(category) {
			const badge = category.status === 'custom' ? 
				'<span class="csc-badge badge-custom">Custom</span>' : 
				'<span class="csc-badge badge-default">Default</span>';
			
			html += '<tr>';
			html += '<td>' + category.id + '</td>';
			html += '<td>';
			html += '<strong>' + escapeHtml(category.name) + '</strong>';
			html += '<div class="row-actions">';
			html += '<span class="view"><a href="' + category.url + '" target="_blank">View</a></span>';
			html += '</div>';
			html += '</td>';
			html += '<td>' + badge + '</td>';
			html += '<td><button type="button" class="button button-small edit-category-seo" ' +
				'data-type="category" ' +
				'data-id="' + category.id + '" ' +
				'data-name="' + escapeHtml(category.name) + '" ' +
				'data-slug="' + escapeHtml(category.slug) + '" ' +
				'data-title="' + escapeHtml(category.title) + '" ' +
				'data-description="' + escapeHtml(category.description) + '" ' +
				'data-h1="' + escapeHtml(category.h1) + '">' +
				'Edit SEO</button></td>';
			html += '</tr>';
		});
		
		html += '</tbody></table>';
		$('#categories-list').html(html);
	}

	/**
	 * ========================================================================
	 * TAGS TAB
	 * ========================================================================
	 */

	function initTags() {
		// Search
		$('#tag-search-btn').on('click', function() {
			loadTags(1, $('#tag-search').val());
		});
		
		$('#tag-search').on('keypress', function(e) {
			if (e.which === 13) {
				loadTags(1, $(this).val());
			}
		});
		
		// Edit button (delegated)
		$(document).on('click', '.edit-tag-seo', function() {
			const data = $(this).data();
			openModal('tag', data);
		});
	}

	function loadTags(page, search) {
		page = page || state.currentPage.tags;
		search = search || '';
		
		showLoading(true);
		
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: {
				action: 'csc_get_tags_data',
				nonce: cscAjax.nonce,
				page: page,
				search: search
			},
			success: function(response) {
				if (response.success) {
					renderTags(response.data.items);
					renderPagination('tags', {
						current_page: response.data.current_page,
						total_pages: response.data.total_pages
					});
					state.currentPage.tags = page;
				}
				showLoading(false);
			},
			error: function() {
				showNotice('Error loading tags.', 'error');
				showLoading(false);
			}
		});
	}

	function renderTags(tags) {
		if (!tags.length) {
			$('#tags-list').html('<p class="loading-message">No tags found.</p>');
			return;
		}
		
		let html = '<table class="wp-list-table widefat fixed striped">';
		html += '<thead><tr>';
		html += '<th style="width: 60px;">ID</th>';
		html += '<th>Tag Name</th>';
		html += '<th style="width: 100px;">Status</th>';
		html += '<th style="width: 120px;">Actions</th>';
		html += '</tr></thead>';
		html += '<tbody>';
		
		tags.forEach(function(tag) {
			const badge = tag.status === 'custom' ? 
				'<span class="csc-badge badge-custom">Custom</span>' : 
				'<span class="csc-badge badge-default">Default</span>';
			
			html += '<tr>';
			html += '<td>' + tag.id + '</td>';
			html += '<td>';
			html += '<strong>' + escapeHtml(tag.name) + '</strong>';
			html += '<div class="row-actions">';
			html += '<span class="view"><a href="' + tag.url + '" target="_blank">View</a></span>';
			html += '</div>';
			html += '</td>';
			html += '<td>' + badge + '</td>';
			html += '<td><button type="button" class="button button-small edit-tag-seo" ' +
				'data-type="tag" ' +
				'data-id="' + tag.id + '" ' +
				'data-name="' + escapeHtml(tag.name) + '" ' +
				'data-slug="' + escapeHtml(tag.slug) + '" ' +
				'data-title="' + escapeHtml(tag.title) + '" ' +
				'data-description="' + escapeHtml(tag.description) + '" ' +
				'data-h1="' + escapeHtml(tag.h1) + '">' +
				'Edit SEO</button></td>';
			html += '</tr>';
		});
		
		html += '</tbody></table>';
		$('#tags-list').html(html);
	}

	/**
	 * ========================================================================
	 * PAGES TAB
	 * ========================================================================
	 */

	function initPages() {
		// Search
		$('#page-search-btn').on('click', function() {
			loadPages(1, $('#page-search').val());
		});
		
		$('#page-search').on('keypress', function(e) {
			if (e.which === 13) {
				loadPages(1, $(this).val());
			}
		});
		
		// Edit button (delegated)
		$(document).on('click', '.edit-page-seo', function() {
			const data = $(this).data();
			openModal('page', data);
		});
	}

	function loadPages(page, search) {
		page = page || state.currentPage.pages;
		search = search || '';
		
		showLoading(true);
		
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: {
				action: 'csc_get_pages_data',
				nonce: cscAjax.nonce,
				paged: page,
				search: search
			},
			success: function(response) {
				if (response.success) {
					renderPages(response.data.pages);
					renderPagination('pages', response.data.pagination);
					updateStats('pages', response.data.stats);
					state.currentPage.pages = page;
				}
				showLoading(false);
			},
			error: function() {
				showNotice('Error loading pages.', 'error');
				showLoading(false);
			}
		});
	}

	function renderPages(pages) {
		if (!pages.length) {
			$('#pages-list').html('<p class="loading-message">No pages found.</p>');
			return;
		}
		
		let html = '<table class="wp-list-table widefat fixed striped">';
		html += '<thead><tr>';
		html += '<th style="width: 60px;">ID</th>';
		html += '<th>Title</th>';
		html += '<th style="width: 100px;">Status</th>';
		html += '<th style="width: 120px;">Actions</th>';
		html += '</tr></thead>';
		html += '<tbody>';
		
		pages.forEach(function(page) {
			const badge = page.status === 'custom' ? 
				'<span class="csc-badge badge-custom">Custom</span>' : 
				'<span class="csc-badge badge-default">Default</span>';
			
			html += '<tr>';
			html += '<td>' + page.ID + '</td>';
			html += '<td>';
			html += '<strong>' + escapeHtml(page.title) + '</strong>';
			html += '<div class="row-actions">';
			html += '<span class="view"><a href="' + page.view_url + '" target="_blank">View</a></span>';
			html += '</div>';
			html += '</td>';
			html += '<td>' + badge + '</td>';
			html += '<td><button type="button" class="button button-small edit-page-seo" ' +
				'data-type="page" ' +
				'data-id="' + page.ID + '" ' +
				'data-name="' + escapeHtml(page.title) + '" ' +
				'data-slug="' + escapeHtml(page.slug) + '" ' +
				'data-title="' + escapeHtml(page.custom_title) + '" ' +
				'data-description="' + escapeHtml(page.custom_description) + '">' +
				'Edit SEO</button></td>';
			html += '</tr>';
		});
		
		html += '</tbody></table>';
		$('#pages-list').html(html);
	}

	/**
	 * ========================================================================
	 * MODAL
	 * ========================================================================
	 */

	function initModal() {
		// Close modal
		$('.csc-modal-close, #modal-cancel-btn').on('click', closeModal);
		
		// Click outside to close
		$('#csc-seo-modal').on('click', function(e) {
			if ($(e.target).is('#csc-seo-modal')) {
				closeModal();
			}
		});
		
		// Save button
		$('#modal-save-btn').on('click', saveModalContent);
		
		// Clear button
		$('#modal-clear-btn').on('click', clearModalContent);
		
		// Live preview
		$('#modal-seo-title').on('input', function() {
			updateModalPreview();
			updateModalTitleCharCount();
		});
		$('#modal-seo-description').on('input', function() {
			updateModalPreview();
			updateModalDescCharCount();
		});
	}

	function openModal(type, data) {
		state.currentItem = {
			type: type,
			id: data.id,
			name: data.name,
			slug: data.slug
		};
		
		// Populate modal
		$('#modal-item-name').text(data.name);
		$('#modal-seo-title').val(data.title || '');
		$('#modal-seo-description').val(data.description || '');
		
		// Show/hide H1 field based on type (only for categories and tags)
		if (type === 'category' || type === 'tag') {
			$('#modal-h1-field').show();
			$('#modal-seo-h1').val(data.h1 || '');
		} else {
			$('#modal-h1-field').hide();
			$('#modal-seo-h1').val('');
		}
		
		// Update preview
		updateModalPreview();
		updateModalTitleCharCount();
		updateModalDescCharCount();
		
		// Show modal
		$('#csc-seo-modal').fadeIn(200);
		$('body').addClass('modal-open');
	}

	function closeModal() {
		$('#csc-seo-modal').fadeOut(200);
		$('body').removeClass('modal-open');
		
		// Reset state
		state.currentItem = { type: null, id: null, name: null, slug: null };
		$('#modal-save-btn').prop('disabled', false).text('Save SEO Settings');
	}

	function saveModalContent() {
		const { type, id } = state.currentItem;
		const title = $('#modal-seo-title').val();
		const description = $('#modal-seo-description').val();
		const h1 = $('#modal-seo-h1').val();
		
		const button = $('#modal-save-btn');
		button.prop('disabled', true).text('Saving...');
		
		let action = '';
		let dataKey = '';
		
		if (type === 'article') {
			action = 'csc_save_article_seo';
			dataKey = 'post_id';
		} else if (type === 'page') {
			action = 'csc_save_page_seo';
			dataKey = 'post_id';
		} else if (type === 'category') {
			action = 'csc_save_category_seo';
			dataKey = 'term_id';
		} else if (type === 'tag') {
			action = 'csc_save_tag_seo';
			dataKey = 'term_id';
		}
		
		const ajaxData = {
			action: action,
			nonce: cscAjax.nonce,
			custom_title: title,
			custom_description: description
		};
		
		// Add H1 for categories and tags
		if (type === 'category' || type === 'tag') {
			ajaxData.custom_h1 = h1;
		}
		
		ajaxData[dataKey] = id;
		
		$.ajax({
			url: cscAjax.ajax_url,
			type: 'POST',
			data: ajaxData,
			success: function(response) {
				if (response.success) {
					showNotice('SEO settings saved successfully!', 'success');
					button.text('Saved!');
					
					setTimeout(function() {
						closeModal();
						
						// Reload the appropriate tab
						if (type === 'article') {
							loadArticles();
						} else if (type === 'category') {
							loadCategories();
						} else if (type === 'tag') {
							loadTags();
						} else if (type === 'page') {
							loadPages();
						}
					}, 1000);
				} else {
					showNotice('Error: ' + response.data, 'error');
					button.prop('disabled', false).text('Save SEO Settings');
				}
			},
			error: function() {
				showNotice('Error saving SEO settings.', 'error');
				button.prop('disabled', false).text('Save SEO Settings');
			}
		});
	}

	function clearModalContent() {
		if (!confirm('Are you sure you want to clear custom SEO settings?')) {
			return;
		}
		
		$('#modal-seo-title').val('');
		$('#modal-seo-description').val('');
		updateModalPreview();
		updateModalTitleCharCount();
		updateModalDescCharCount();
		
		// Trigger save with empty values
		saveModalContent();
	}

	function updateModalPreview() {
		const title = $('#modal-seo-title').val() || state.currentItem.name;
		const description = $('#modal-seo-description').val() || 'Meta description...';
		const url = 'https://example.com/' + (state.currentItem.slug || '');
		
		$('#modal-preview-title').text(title);
		$('#modal-preview-url').text(url);
		$('#modal-preview-description').text(description);
		
		// Update preview warnings
		updateModalPreviewWarnings();
	}

	function updateModalTitleCharCount() {
		const count = $('#modal-seo-title').val().length;
		const $counter = $('#modal-title-char-count');
		
		if ($counter.length) {
			$counter.text(count);
			
			// Color code: Red if > 60, yellow if > 50, blue otherwise
			$counter.parent().css('color', count > 60 ? '#dc3232' : (count > 50 ? '#f0ad4e' : '#2271b1'));
		}
	}

	function updateModalDescCharCount() {
		const count = $('#modal-seo-description').val().length;
		const $counter = $('#modal-char-count');
		
		if ($counter.length) {
			$counter.text(count);
			
			// Color code: Red if > 160, yellow if > 150, blue otherwise
			$counter.parent().css('color', count > 160 ? '#dc3232' : (count > 150 ? '#f0ad4e' : '#2271b1'));
		}
	}

	function updateModalPreviewWarnings() {
		const titleLength = $('#modal-seo-title').val().length;
		const descLength = $('#modal-seo-description').val().length;
		
		// Remove existing warnings
		$('#modal-preview-title-warning, #modal-preview-desc-warning').remove();
		
		// Add title warning if needed
		if (titleLength > 60) {
			$('#modal-preview-title').after('<div id="modal-preview-title-warning" style="color: #dc3232; font-size: 11px; margin-top: 2px;">⚠ Title is too long (' + titleLength + ' chars, recommended: 50-60)</div>');
		} else if (titleLength > 50) {
			$('#modal-preview-title').after('<div id="modal-preview-title-warning" style="color: #f0ad4e; font-size: 11px; margin-top: 2px;">⚠ Title is getting long (' + titleLength + ' chars, recommended: 50-60)</div>');
		}
		
		// Add description warning if needed
		if (descLength > 160) {
			$('#modal-preview-description').after('<div id="modal-preview-desc-warning" style="color: #dc3232; font-size: 11px; margin-top: 2px;">⚠ Description is too long (' + descLength + ' chars, recommended: 150-160)</div>');
		} else if (descLength > 150) {
			$('#modal-preview-description').after('<div id="modal-preview-desc-warning" style="color: #f0ad4e; font-size: 11px; margin-top: 2px;">⚠ Description is getting long (' + descLength + ' chars, recommended: 150-160)</div>');
		}
	}

	/**
	 * ========================================================================
	 * PAGINATION
	 * ========================================================================
	 */

	function renderPagination(type, pagination) {
		if (pagination.total_pages <= 1) {
			$('#' + type + '-pagination').empty();
			return;
		}
		
		let html = '';
		const current = pagination.current_page;
		const total = pagination.total_pages;
		
		// Previous button
		if (current > 1) {
			html += '<button type="button" class="button" data-page="' + (current - 1) + '" data-type="' + type + '">← Previous</button>';
		}
		
		// Page numbers (simple version)
		for (let i = 1; i <= total; i++) {
			if (i === current) {
				html += '<button type="button" class="button current" disabled>' + i + '</button>';
			} else if (i === 1 || i === total || (i >= current - 2 && i <= current + 2)) {
				html += '<button type="button" class="button" data-page="' + i + '" data-type="' + type + '">' + i + '</button>';
			} else if (i === current - 3 || i === current + 3) {
				html += '<span class="page-info">...</span>';
			}
		}
		
		// Next button
		if (current < total) {
			html += '<button type="button" class="button" data-page="' + (current + 1) + '" data-type="' + type + '">Next →</button>';
		}
		
		$('#' + type + '-pagination').html(html);
		
		// Attach click handlers
		$('#' + type + '-pagination button[data-page]').on('click', function() {
			const page = $(this).data('page');
			const dataType = $(this).data('type');
			
			if (dataType === 'articles') {
				loadArticles(page);
			} else if (dataType === 'categories') {
				loadCategories(page);
			} else if (dataType === 'tags') {
				loadTags(page);
			} else if (dataType === 'pages') {
				loadPages(page);
			}
		});
	}

	/**
	 * ========================================================================
	 * STATISTICS
	 * ========================================================================
	 */

	function updateStats(type, stats) {
		if (type === 'articles') {
			$('#total-articles').text(stats.total_articles);
			$('#custom-articles').text(stats.custom_seo_count);
		} else if (type === 'pages') {
			$('#total-pages').text(stats.total_pages);
			$('#custom-pages').text(stats.custom_page_seo_count);
		}
	}

	/**
	 * ========================================================================
	 * UTILITY FUNCTIONS
	 * ========================================================================
	 */

	function showLoading(show) {
		if (show) {
			$('.csc-loading-overlay').show();
		} else {
			$('.csc-loading-overlay').hide();
		}
	}

	function showNotice(message, type) {
		type = type || 'success';
		
		// Remove existing notices
		$('.csc-notice').remove();
		
		const noticeClass = type === 'error' ? 'notice-error' : (type === 'info' ? 'notice-info' : 'notice-success');
		const notice = $('<div class="notice ' + noticeClass + ' is-dismissible csc-notice"><p>' + message + '</p></div>');
		
		$('.csc-admin-wrap > h1').after(notice);
		
		// Auto dismiss after 5 seconds
		setTimeout(function() {
			notice.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
		
		// Scroll to notice
		$('html, body').animate({
			scrollTop: notice.offset().top - 100
		}, 300);
	}

	function escapeHtml(text) {
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return String(text || '').replace(/[&<>"']/g, function(m) {
			return map[m];
		});
	}

})(jQuery);
