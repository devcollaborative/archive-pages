<?php
/*
	Plugin Name: Set Archive Pages
	Plugin URI: https://github.com/devcollaborative/archive-pages
	Description: Select a page to override archive page title & description.
	Author: DevCollaborative
	Author URI: https://devcollaborative.com/
	Version: 1.0.0
	Update URI: https://api.github.com/devcollaborative/archive-pages/releases/latest
*/

defined( 'ABSPATH' ) or exit;


/**
 * Add plugin settings page.
 */
function archive_pages_add_settings_page() {
    add_options_page( 'Archive Pages', 'Archive Pages', 'edit_posts', 'archive-pages', 'archive_pages_render_settings_page' );
}
add_action( 'admin_menu', 'archive_pages_add_settings_page' );

/**
 * Render plugin settings page.
 */
function archive_pages_render_settings_page() {
  ?>

	<div class="wrap">
		<h1><?php echo get_admin_page_title(); ?></h1>
		<form action="options.php" method="post">
				<?php
					settings_fields( 'archive_pages_settings' );
					do_settings_sections( 'archive-pages' );
				?>
				<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
		</form>
	</div>

	<?php
}

/**
 * Register plugin setting options.
 */
function archive_pages_settings_init() {
	// Get custom post types that have public archive pages.
	$post_types = get_post_types(array(
		'has_archive' => true,
		'_builtin' 		=> false,
	), 'objects');

	/**
	 * Register a section for our settings page.
	 * Title & callback are blank because we don't want a section heading to show, but settings need a section to appear.
	 */
	add_settings_section('archive_pages_section', '', '', 'archive-pages');

	// Add setting & dropdown for each post type.
	foreach ($post_types as $post_type) {
		// Register setting for this CPT.
		register_setting('archive_pages_settings', 'archive_page_' . $post_type->name);

		// Add field for this CPT.
		add_settings_field(
			'archive_page_' . $post_type->name,
			"{$post_type->label} ({$post_type->name})",
			'archive_pages_wp_dropdown_pages',
			'archive-pages',
			'archive_pages_section',
			array( 'name' => 'archive_page_' . $post_type->name )
		);
	}
}
add_action( 'admin_init', 'archive_pages_settings_init' );

/**
 * Render list of all pages.
 *
 * @param array Args passed by add_settings_field function
 */
function archive_pages_wp_dropdown_pages($args) {
	$setting = get_option($args['name']) ?: '';

	wp_dropdown_pages(array(
		'name' 						 => $args['name'],
		'selected' 				 => $setting,
		'show_option_none' => '-- None --',
	));
}

function archive_pages_filter_archive_titles($args) {
	$setting = get_option($args['name']) ?: '';

	wp_dropdown_pages(array(
		'name' 						 => $args['name'],
		'selected' 				 => $setting,
		'show_option_none' => '-- None --',
	));
}

/**
 * Override the `get_the_archive_title` function to display the title from the selected archive page.
 */
function filter_archive_page_title( $title ) {
	if ( is_post_type_archive() ) {
		$archive_page = get_option('archive_page_' . get_query_var( 'post_type' ) );

		if ( $archive_page ) {
			return get_the_title ($archive_page );
		}
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'filter_archive_page_title', 10 );

/**
 * Override the `get_the_archive_description` function to display the post content from the selected archive page.
 */
function filter_archive_page_description( $description ) {
	if ( is_post_type_archive() ) {
		$archive_page = get_option('archive_page_' . get_query_var( 'post_type' ) );

		if ( $archive_page ) {
			return get_the_content(null, false, $archive_page );
		}
	}

	return $description;
}
add_filter( 'get_the_archive_description', 'filter_archive_page_description', 10 );