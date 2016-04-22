<?php
/**
 * This file adds the Landing page template to the No Sidebar Pro Theme.
 *
 * @author StudioPress
 * @package No Sidebar Pro Theme
 * @subpackage Customizations
 */

/*
Template Name: Landing
*/

//* Add landing body class to the head
add_filter( 'body_class', 'ns_add_body_class' );
function ns_add_body_class( $classes ) {

	$classes[] = 'ns-landing';
	return $classes;

}

//* Remove Skip Links from a template
remove_action ( 'genesis_before_header', 'genesis_skip_links', 5 );

//* Dequeue Skip Links Script
add_action( 'wp_enqueue_scripts', 'ns_dequeue_skip_links' );
function ns_dequeue_skip_links() {

	wp_dequeue_script( 'skip-links' );

}

//* Remove site header elements
remove_action( 'genesis_header', 'genesis_header_markup_open', 5 );
remove_action( 'genesis_header', 'genesis_do_header' );
remove_action( 'genesis_header', 'genesis_header_markup_close', 15 );

//* Remove search form from site header
remove_action( 'genesis_header', 'ns_search', 13 );

//* Remove navigation
remove_theme_support( 'genesis-menus' );

//* Remove breadcrumbs
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

//* Remove site footer elements
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

//* Run the Genesis loop
genesis();
