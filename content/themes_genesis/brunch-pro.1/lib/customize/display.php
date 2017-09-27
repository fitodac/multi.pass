<?php
/**
 * Implement options selected by the user via the customizer on the front end.
 *
 * @package   BrunchPro\Functions\Customizer
 * @copyright Copyright (c) 2017, Feast Design Co.
 * @license   GPL-2.0+
 * @since     1.0.0
 */

defined( 'WPINC' ) || die;

add_action( 'wp_enqueue_scripts', 'brunch_pro_load_google_fonts' );
/**
 * Load Google Fonts libraries using dynamically generated user data.
 *
 * @since  1.0.0
 * @access public
 * @uses   feastco_customizer_get_google_font_uri()
 * @uses   CHILD_THEME_VERSION
 * @return bool true if font styles have been loaded.
 */
function brunch_pro_load_google_fonts() {
	if ( apply_filters( 'brunch_pro_disable_google_fonts', false ) ) {
		return false;
	}

	$fonts = array();
	foreach ( brunch_pro_get_fonts() as $font => $setting ) {
		$fonts[] = get_theme_mod( "{$font}_family", $setting['default_family'] );
	}

	wp_enqueue_style(
		'brunch-pro-google-fonts',
		feastco_customizer_get_google_font_uri( $fonts ),
		array(),
		CHILD_THEME_VERSION
	);

	return true;
}

add_action( 'wp_enqueue_scripts', 'brunch_pro_inline_styles' );
/**
 * Load all CSS rules generated by the customizer.
 *
 * @since  1.0.0
 * @access public
 * @uses   Feastco_Customizer_Styles()
 * @return void
 */
function brunch_pro_inline_styles() {
	do_action( 'brunch_pro_inline_styles' );
	wp_add_inline_style( 'brunch-pro-theme', Feastco_Customizer_Styles::instance()->build() );
}

add_action( 'brunch_pro_inline_styles', 'brunch_pro_google_fonts_styles' );
/**
 * Build Google Fonts styles based on user input from the customizer.
 *
 * @since  1.0.0
 * @access public
 * @uses   brunch_pro_get_fonts()
 * @return bool
 */
function brunch_pro_google_fonts_styles() {
	if ( apply_filters( 'brunch_pro_disable_google_fonts', false ) ) {
		return false;
	}

	foreach ( brunch_pro_get_fonts() as $font => $data ) {
		if ( 'disabled' !== $data['default_family'] ) {
			brunch_pro_add_font(
				'family',
				get_theme_mod( "{$font}_family", $data['default_family'] ),
				$data
			);
		}

		if ( 'disabled' !== $data['default_weight'] ) {
			brunch_pro_add_font(
				'weight',
				get_theme_mod( "{$font}_weight", $data['default_weight'] ),
				$data
			);
		}

		if ( 'disabled' !== $data['default_size'] ) {
			brunch_pro_add_font(
				'size',
				get_theme_mod( "{$font}_size", $data['default_size'] ),
				$data
			);
		}

		if ( 'disabled' !== $data['default_style'] ) {
			brunch_pro_add_font(
				'style',
				get_theme_mod( "{$font}_style", $data['default_style'] ),
				$data
			);
		}
	}

	return true;
}

add_action( 'brunch_pro_inline_styles', 'brunch_pro_color_styles' );
/**
 * Build color styles based on user input from the customizer.
 *
 * @since  1.0.0
 * @access public
 * @uses   brunch_pro_get_colors()
 * @return bool
 */
function brunch_pro_color_styles() {
	if ( apply_filters( 'brunch_pro_disable_colors', false ) ) {
		return false;
	}

	foreach ( brunch_pro_get_colors() as $color => $data ) {
		brunch_pro_add_color( get_theme_mod( $color, $data['default'] ), $data );
	}

	return true;
}

add_action( 'genesis_before_loop', 'brunch_pro_blog_page_maybe_add_grid', 99 );
/**
 * Add the archive grid filter to the main loop.
 *
 * @since  1.0.0
 * @uses   genesis_is_blog_template()
 * @uses   brunch_pro_grid_exists()
 * @return bool true if the filter has been added, false otherwise.
 */
function brunch_pro_blog_page_maybe_add_grid() {
	if ( ! genesis_is_blog_template() || ! brunch_pro_blog_page_is_grid_enabled() ) {
		return false;
	}

	if ( $grid = brunch_pro_grid_exists( get_theme_mod( 'archive_grid', 'full' ) ) ) {
		return add_filter( 'post_class', "brunch_pro_grid_{$grid}" );
	}

	return false;
}

add_action( 'genesis_after_loop', 'brunch_pro_blog_page_maybe_remove_grid', 5 );
/**
 * Remove the archive grid filter to ensure other loops are unaffected.
 *
 * @since  1.0.0
 * @uses   genesis_is_blog_template()
 * @uses   brunch_pro_grid_exists()
 * @return bool true if the filter has been removed, false otherwise.
 */
function brunch_pro_blog_page_maybe_remove_grid() {
	if ( ! genesis_is_blog_template() || ! brunch_pro_blog_page_is_grid_enabled() ) {
		return false;
	}

	if ( $grid = brunch_pro_grid_exists( get_theme_mod( 'archive_grid', 'full' ) ) ) {
		return remove_filter( 'post_class', "brunch_pro_grid_{$grid}" );
	}

	return false;
}

add_action( 'genesis_before_entry', 'brunch_pro_archive_maybe_add_grid', 10 );
/**
 * Add the archive grid filter to the main loop.
 *
 * @since  1.0.0
 * @uses   brunch_pro_is_blog_archive()
 * @uses   brunch_pro_grid_exists()
 * @return bool true if the filter has been added, false otherwise.
 */
function brunch_pro_archive_maybe_add_grid() {
	if ( ! brunch_pro_is_blog_archive() ) {
		return false;
	}

	if ( $grid = brunch_pro_grid_exists( get_theme_mod( 'archive_grid', 'full' ) ) ) {
		return add_filter( 'post_class', "brunch_pro_grid_{$grid}_main" );
	}

	return false;
}

add_action( 'genesis_before_entry', 'brunch_pro_archive_maybe_remove_title', 10 );
/**
 * Remove the entry title if the user has disabled it via the customizer.
 *
 * @since  1.0.0
 * @uses   brunch_pro_is_blog_archive()
 * @return void
 */
function brunch_pro_archive_maybe_remove_title() {
	if ( brunch_pro_is_blog_archive() && ! get_theme_mod( 'archive_show_title', true ) ) {
		remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
	}
}

add_action( 'genesis_before_entry', 'brunch_pro_archive_maybe_remove_info', 10 );
/**
 * Remove the entry info if the user has disabled it via the customizer.
 *
 * @since  1.0.0
 * @uses   brunch_pro_is_blog_archive()
 * @return void
 */
function brunch_pro_archive_maybe_remove_info() {
	if ( brunch_pro_is_blog_archive() && ! get_theme_mod( 'archive_show_info', true ) ) {
		remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
	}
}

add_action( 'genesis_before_entry', 'brunch_pro_archive_maybe_remove_content', 10 );
/**
 * Remove the entry content if the user has disabled it via the customizer.
 *
 * @since  1.0.0
 * @uses   brunch_pro_is_blog_archive()
 * @return void
 */
function brunch_pro_archive_maybe_remove_content() {
	if ( brunch_pro_is_blog_archive() && ! get_theme_mod( 'archive_show_content', true ) ) {
		remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
	}
}

add_action( 'genesis_before_entry', 'brunch_pro_archive_maybe_remove_meta', 10 );
/**
 * Remove the entry meta if the user has disabled it via the customizer.
 *
 * @since  1.0.0
 * @uses   brunch_pro_is_blog_archive()
 * @return void
 */
function brunch_pro_archive_maybe_remove_meta() {
	if ( brunch_pro_is_blog_archive() && ! get_theme_mod( 'archive_show_meta', true ) ) {
		remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
	}
}

add_action( 'genesis_before_entry', 'brunch_pro_archive_maybe_move_image', 10 );
/**
 * Move the post image if the user has changed the placement via the customizer.
 *
 * @since  1.0.0
 * @uses   brunch_pro_is_blog_archive()
 * @return void
 */
function brunch_pro_archive_maybe_move_image() {
	if ( ! brunch_pro_is_blog_archive() ) {
		return;
	}
	$placement = get_theme_mod( 'archive_image_placement', 'after_title' );
	if ( 'after_title' !== $placement ) {
		remove_action( 'genesis_entry_content', 'genesis_do_post_image', 8 );
	}
	if ( 'before_title' === $placement ) {
		add_action( 'genesis_entry_header', 'genesis_do_post_image', 5 );
	}
	if ( 'after_content' === $placement ) {
		add_action( 'genesis_entry_footer', 'genesis_do_post_image', 0 );
	}
}