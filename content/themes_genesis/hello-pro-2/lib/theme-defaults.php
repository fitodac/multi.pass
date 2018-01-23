<?php
/**
 * Hello Pro Theme Defaults
 *
 * This file adds the theme defaults for Hello Pro
 *
 * @package Hello Pro
 * @author  BrandiD
 * @license GPL-2.0+
 * @link    https://thebrandid.com
 */

// * Theme Setting Defaults
add_filter( 'genesis_theme_settings_defaults', 'bid_hello_pro_theme_defaults' );
function bid_hello_pro_theme_defaults( $defaults ) {

	$defaults['blog_cat_num']              = 5;
	$defaults['content_archive']           = 'full';
	$defaults['content_archive_limit']     = 0;
	$defaults['content_archive_thumbnail'] = 0;
	$defaults['image_alignment']           = 'alignleft';
	$defaults['posts_nav']                 = 'numeric';
	$defaults['site_layout']               = 'content-sidebar';

	return $defaults;

}

// * Theme Setup
add_action( 'after_switch_theme', 'bid_hello_pro_theme_setting_defaults' );
function bid_hello_pro_theme_setting_defaults() {

	if ( function_exists( 'genesis_update_settings' ) ) {

		genesis_update_settings( array(
			'blog_cat_num'              => 5,
			'content_archive'           => 'full',
			'content_archive_limit'     => 0,
			'content_archive_thumbnail' => 0,
			'image_alignment'           => 'alignleft',
			'posts_nav'                 => 'numeric',
			'site_layout'               => 'content-sidebar',
		) );

	}

	update_option( 'posts_per_page', 5 );

}
