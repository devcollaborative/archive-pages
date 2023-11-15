<?php
/*
	Plugin Name: Set Archive Pages
	Plugin URI: https://github.com/devcollaborative/archive-pages
	Description: Select a page to override archive page title & description.
	Author: DevCollaborative
	Author URI: https://devcollaborative.com/
	Version: 1.1.0
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
				//do this once for post types, once for categories
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
	 * Callback is blank
	 */
	add_settings_section('post_type_pages_section', 'Post Type Archives', '', 'archive-pages');

	//loop through post type archives
	archive_pages_settings_fields( $post_types, 'label', 'post_type_pages_section');

	//get category archives
	add_settings_section('category_pages_section', 'Category Archives', '', 'archive-pages');

	//get categories
	$categories = get_categories( array(
		'orderby' => 'name',
		'order'		=> 'ASC',
		'hide_empty'	=> false
	)); 

	//loop through category archives
	archive_pages_settings_fields( $categories, 'name', 'category_pages_section');

}
add_action( 'admin_init', 'archive_pages_settings_init' );


/**
 * Loop through list of archives and output nice label
 *
 * @param $archives array - archive list of post types, categories, etc
 * @param $label string - array key to use for label
 * @param $section string - section to add fields to
 * 
 */
function archive_pages_settings_fields( $archives, $label, $section ){
	// Add setting & dropdown for each archive
	foreach ($archives as $archive) {

		//get a standard slug for the archive name
		$field_slug = sanitize_title($archive->name);

		// Register setting for this archive.
		register_setting('archive_pages_settings', 'archive_page_' . $field_slug);

		// Add field for this archive.
		add_settings_field(
			'archive_page_' . $field_slug, 			//slug-name to identify field
			$archive->$label, 									//title or label of field
			'archive_pages_wp_dropdown_pages', 	//callback
			'archive-pages',										//settings page
			$section, 													//section of settings page
			array( 'name' => 'archive_page_' . $field_slug )
		);
	}
}

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

	$archive_page = '';

	if ( is_post_type_archive()  ) {

		$archive_page = get_option('archive_page_' . get_query_var( 'post_type' ) );

	}

	if ( $archive_page ) {
		return get_the_title ($archive_page );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'filter_archive_page_title', 10 );

/**
 * Override the `get_the_archive_description` function to display the post content from the selected archive page.
 */
function filter_archive_page_description( $description ) {

	$archive_page = '';

	if ( is_post_type_archive() ) {
		$archive_page = get_option('archive_page_' . get_query_var( 'post_type' ) );
	}
	
	if ( $archive_page ) {
		
		return get_the_content(null, false, $archive_page );
		
	}

	return $description;
}
add_filter( 'get_the_archive_description', 'filter_archive_page_description', 10 );


/**
 * Replace request for a category archive with a request for a specific page if it's been assigned and published
 * 
 * @param request - array of requested query variables
 * 
 * @link https://developer.wordpress.org/reference/hooks/request/
 * @link https://wordpress.stackexchange.com/questions/268589/how-to-override-a-query-and-display-specific-page-by-id
 * */
function archive_pages_request_landing_page( $request ){

	if( array_key_exists('category_name', $request ) ){

		//slug is same format used in Set Archive Pages plugin 
		$cat_slug = sanitize_title( $request['category_name'] );
		$archive_page_id = get_option('archive_page_' . $cat_slug );

		//swap out whatever was requested for the specific assigned landing page id  
		if ( 'publish' === get_post_status( $archive_page_id ) ){
	      unset( $request['category_name'] );
	      $request['page_id'] = $archive_page_id;
    	}
  }
  
  return $request;

}
add_filter( 'request', 'archive_pages_request_landing_page' );