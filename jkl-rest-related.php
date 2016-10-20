<?php
/**
 * Plugin Name: JKL REST Related
 * Description: Displays links to related posts through the WP-API
 * Version:     0.1
 * Author:      Aaron Snowberger
 * Author URI:  http://aaronsnowberger.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jkl-rest-related
 * Domain Path: /languages
 */

// Add various fields to the JSON output
function jkl_rr_register_fields() {
    // Add Author name
    register_rest_field( 'post',
            'author_name',
            array( 
                'get_callback'      => 'jkl_rr_get_author_name', // our function
                'update_callback'   => null,    // if we want to save to database
                'schema'            => null
            )   
    );
    // Add Featured Image
    register_rest_field( 'post',
            'featured_image_src',
            array( 
                'get_callback'      => 'jkl_rr_get_image_src', // our function
                'update_callback'   => null,    // if we want to save to database
                'schema'            => null
            )   
    );
}
function jkl_rr_get_author_name( $object, $field_name, $request ) {
    return get_the_author_meta( 'display_name' );
}
function jkl_rr_get_image_src( $object, $field_name, $request ) {
    global $post;
    $post_id = $post->ID;
    return get_the_post_thumbnail_url( $post_id, 'thumbnail' );
}
add_action( 'rest_api_init', 'jkl_rr_register_fields' );

// Hook in all the important things
function jkl_rr_scripts() {
    if( is_single() && is_main_query() ) {
        // Get plugin stylesheet
        wp_enqueue_style( 'jkl-rr-style', plugin_dir_url( __FILE__ ) . 'css/style.css', '20161020', 'all' );
        wp_enqueue_script( 'jkl-rr-script', plugin_dir_url( __FILE__ ) . 'js/rest.ajax.js', array( 'jquery' ), '20161020', true );
        
        global $post;
        $post_id = $post->ID;
        
        wp_localize_script( 'jkl-rr-script', 'Postdata', 
                array(
                    'post_id'   => $post_id,
                    'json_url'  => jkl_rr_get_json_query(),
                    'header'    => __( 'Related Posts:', 'jkl-rr' )
                )
        );
        
    }
}
add_action( 'wp_enqueue_scripts', 'jkl_rr_scripts' );

/**
 * Create REST API url
 * - Get the current categories
 * - Get the category IDs
 * - Create the arguments for categories and posts-per-page
 * - Create the URL
 */
function jkl_rr_get_json_query() {
    $cats = get_the_category();
    $cat_ids = array();
    foreach( $cats as $cat ) {
        $cat_ids[] = $cat->term_id;
    }
    
    $args = array(
        'filter[cat]' => implode( ",", $cat_ids ),
        'filter[posts_per_page]' => 5
    );
    
    $url = add_query_arg( $args, rest_url( 'wp/v2/posts' ) );
    
    return $url;
}

// Base HTML to be added to the bottom of a Post
function jkl_rr_baseline_html() {
    // Set up container etc
    $baseline  = '<section id="related-posts" class="related-posts">';
    $baseline .= '<a href="#" class="get-related-posts button">Get related posts</a>';
    $baseline .= '<div class="ajax-loader"><img src="' . plugin_dir_url( __FILE__ ) . 'css/spinner.svg" width="32" height="32"></div>';
    $baseline .= '</section><!-- .related-posts -->';
    
    return $baseline;
}

// Bootstrap this whole thing onto the bottom of single posts
function jkl_rr_display( $content ) {
    if( is_single() && is_main_query() ) {
        $content .= jkl_rr_baseline_html();
    }
    return $content;
}
add_filter( 'the_content', 'jkl_rr_display' );