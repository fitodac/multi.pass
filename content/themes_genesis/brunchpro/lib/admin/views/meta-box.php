<?php
/**
 * Template to display the Brunch Pro admin sidebar meta box.
 *
 * @package   BrunchPro\Admin\Views
 * @copyright Copyright (c) 2017, Feast Design Co.
 * @license   GPL-2.0+
 * @since     0.1.0
 */

?>
<div id="brunch-pro-enable-wrap" class="misc-pub-section brunch-pro-enable" style="position:relative;">
	<label for="_brunch_pro_enable_blog_grid">
		<input type="checkbox" name="_brunch_pro_enable_blog_grid" id="_brunch_pro_enable_blog_grid" value="yes"<?php checked( $enable, true ); ?> />
		<?php esc_html_e( 'Enable Archive Grid', 'brunch-pro' ); ?>
	</label>
</div>
<?php wp_nonce_field( 'save_brunch_pro_metabox', 'brunch_pro_metabox_nonce' ); ?>
