<?php
/*
Plugin Name: Mug Monday
Plugin URI:  https://geoffreyshilling.com/plugins/mug-monday/
Description: More to come...
Version:     0.1
Author:      Geoffrey Shilling
Author URI:  https://geoffreyshilling.com/
Text Domain: mug-monday
Domain Path: /languages
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Mug Monday is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Mug Monday is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Mug Monday. If not, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

function mug_monday_register_mug_monday_post_type() {
	$labels = array(
		'name'               => _x( 'Mug Monday', 'post type general name', 'mug-monday' ),
		'singular_name'      => _x( 'Mug Monday', 'post type singular name', 'mug-monday' ),
		'menu_name'          => _x( 'Mug Mondays', 'admin menu', 'mug-monday' ),
		'name_admin_bar'     => _x( 'Mug Monday', 'add new on admin bar', 'mug-monday' ),
		'add_new'            => _x( 'Add New', 'mug-monday' ),
		'add_new_item'       => __( 'Add New Mug Monday', 'mug-monday' ),
		'new_item'           => __( 'New Mug Monday', 'mug-monday' ),
		'edit_item'          => __( 'Edit Mug Monday', 'mug-monday' ),
		'view_item'          => __( 'View Mug Mondays', 'mug-monday' ),
		'all_items'          => __( 'All Mug Mondays', 'mug-monday' ),
		'search_items'       => __( 'Search Mug Mondays', 'mug-monday' ),
		'not_found'          => __( 'No Mug Mondays found.', 'mug-monday' ),
		'not_found_in_trash' => __( 'No Mug Mondays found in Trash.', 'mug-monday' )
	);
    $args = array(
        'public' => true,
        'labels'  => $labels,
		'taxonomies' => array('post_tag','category'),
		'supports' => array('editor','title','thumbnail'),
        'show_in_rest' => true,
        'template' => array(
            array( 'core/image', array(
            ) ),
            array( 'core/paragraph', array(
                'placeholder' => 'Add notes about this mug including when you got it, where it came from...',
            ) ),
        ),
    );
    register_post_type( 'mug-monday', $args );
}
add_action( 'init', 'mug_monday_register_mug_monday_post_type' );

function mug_monday_change_title_text( $title ){
     $screen = get_current_screen();
	 $count_posts = wp_count_posts('mug-monday');
     $pages = $count_posts->publish;
	 $draft_posts = $count_posts->draft;
	 $totalPosts = $pages + $draft_posts;

     if ( 'mug-monday' == $screen->post_type ) {
          return __('Mug Monday #', 'mug-monday') . $totalPosts . __(' ', 'mug-monday') . current_time( 'mysql' );
     }
}
add_filter( 'enter_title_here', 'mug_monday_change_title_text' );

function mug_monday_add_custom_title( $data, $postarr ) {
    if($data['post_type'] == 'mug-monday') {
        if(empty($data['post_title'])) {
			$count_posts = wp_count_posts('mug-monday');
	        $pages = $count_posts->publish;
			$draft_posts = $count_posts->draft;
			$totalPosts = $pages + $draft_posts;
            $data['post_title'] = __('Mug Monday #', 'mug-monday') . $totalPosts . __(' ', 'mug-monday') . current_time( 'mysql' );
        }


    }
    return $data;
}
add_filter('wp_insert_post_data', 'mug_monday_add_custom_title', 10, 2 );

function mug_monday_rewrite_flush() {
    mug_monday_register_mug_monday_post_type();

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mug_monday_rewrite_flush' );

// may need adjusted for other searches?
function add_mug_monday_to_query( $query ) {
	if ( !is_admin() && $query->is_main_query() ) {
	    if ($query->is_search) {
	      $query->set('post_type', array( 'post', 'mug-monday' ) );
	    }
  	}
}
add_action( 'pre_get_posts', 'add_mug_monday_to_query' );

add_theme_support( 'post-thumbnails', array('post', 'page','mug-monday'));

function mug_monday_add_default_taxonomy( $post_id ) {

	// Check if 'Mug Monday' category already exists
	$term = term_exists( 'Mug Monday', 'category' );
	if ( $term !== 0 && $term !== null ) {
	    // Mug Monday category already exists
	} else {
		 wp_create_category('Mug Monday');
	}
	// An array of IDs of categories we to add to this post.
	$cat_ids = array( get_cat_ID( 'Mug Monday' ) );
	$term_taxonomy_ids = wp_set_object_terms( $post_id, $cat_ids, 'category', true );

	$term = term_exists( 'Coffee', 'post_tag' );
	if ( $term !== 0 && $term !== null ) {
		
	    // Coffee tag already exists
			 wp_create_tag('Coffee');
	} else {
		 wp_create_tag('Coffee');
	}

	// An array of IDs of tags to add to this post.
	//$tag_ids2 = get_term_by('name', 'Coffee', 'post_tag');
	$tag_ids2 = array( get_term_by('name', 'Coffee', 'post_tag') );

	$tag_by_id = $tag_ids2->term_id;

	/*
	 * If this was coming from the database or another source, we would need to make sure
	 * these were integers:

	$cat_ids = array_map( 'intval', $cat_ids );
	$cat_ids = array_unique( $cat_ids );

	 */

	// Add these categories, note the last argument is true.
	//$term_taxonomy_ids = wp_set_object_terms( $post_id, $cat_ids, 'category', true );

		$term_taxonomy_ids = wp_set_object_terms( $post_id, $tag_by_id, 'post_tag', true );

	if ( is_wp_error( $term_taxonomy_ids ) ) {
	    // There was an error somewhere and the terms couldn't be set.
	} else {
	    // Success! These categories were added to the post.
	}
}
add_action( 'save_post', 'mug_monday_add_default_taxonomy' );
?>
