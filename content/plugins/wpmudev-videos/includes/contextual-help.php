<?php
global $wpmudev_video_pages;

$wpmudev_video_pages = array(
	'dashboard'          => array( 'dashboard', 'admin-bar', 'quickpress', 'change-password' ),
	'post'               => array(
		'add-new-post',
		'the-toolbar',
		'edit-text',
		'add-paragraph',
		'add-heading',
		'hyperlinks',
		'lists',
		'oEmbed',
		'playlists',
		'excerpt',
		'add-image-from-pc',
		'add-image-from-media-library',
		'add-image-from-url',
		'image-gallery',
		'edit-image',
		'replace-image',
		'delete-image',
		'image-editor',
		'featured-image',
		'revisions'
	),
	'edit-post'          => array( 'gutenberg-add-post', 'trash-post', 'restore-post', 'pages-v-posts' ),
	'page'               => array(
		'add-new-page',
		'the-toolbar',
		'edit-text',
		'add-paragraph',
		'add-heading',
		'hyperlinks',
		'lists',
		'oEmbed',
		'playlists',
		'add-image-from-pc',
		'add-image-from-media-library',
		'add-image-from-url',
		'image-gallery',
		'edit-image',
		'replace-image',
		'delete-image',
		'image-editor',
		'revisions'
	),
	'edit-page'          => array( 'gutenberg-add-page', 'trash-post', 'restore-page', 'pages-v-posts' ),
	'gutenberg-editor'   => array( 'gutenberg-editor-overview', 'gutenberg-reusable-blocks' ),
	'widgets'            => array( 'widgets' ),
	'nav-menus'          => array( 'menus' ),
	'themes'             => array( 'change-theme', 'customize' ),
	'profile'            => array( 'change-password' ),
	'edit-post_tag'      => array( 'tags' ),
	'edit-category'      => array( 'categories' ),
	'upload'             => array( 'media-library', 'image-editor' ),
	'media'              => array( 'add-media' ),
	'edit-comments'      => array( 'comments' ),
	'users'              => array( 'create-edit-user' ),
	'user'               => array( 'create-edit-user', 'change-password' ),
	'profile'            => array( 'create-edit-user', 'change-password' ),
	'user-edit'          => array( 'create-edit-user' ),
	'tools'              => array( 'tools' ),
	'import'             => array( 'tools' ),
	'export'             => array( 'tools' ),
	'options-general'    => array( 'settings' ),
	'options-writing'    => array( 'settings' ),
	'options-reading'    => array( 'settings' ),
	'options-discussion' => array( 'settings' ),
	'options-media'      => array( 'settings' ),
	'options-permalink'  => array( 'settings' ),
	'update-core'        => array( 'running-updates' ),
	'plugin-install'     => array( 'install-plugin' ),
	'theme-install'      => array( 'install-themes' ),
);

// if Classic editor is installed show old add new page/post videos
if ( class_exists( 'Classic_Editor' ) ) {
	$wpmudev_video_pages['edit-post'] = array( 'add-new-post', 'trash-post', 'restore-post', 'pages-v-posts' );
	$wpmudev_video_pages['edit-page'] = array( 'add-new-page', 'trash-post', 'restore-page', 'pages-v-posts' );
}

add_filter( 'contextual_help', 'wpmudev_vids_help', 10, 3 );

function wpmudev_vids_help( $old_help, $screen_id, $screen ) {
	global $wpmudev_video_pages, $wpmudev_vids, $wp_version;

	if ( isset( $wpmudev_video_pages[ $screen_id ] ) ) {
		$hidden          = $wpmudev_vids->get_setting( 'hide' );
		$contextual_help = '<div class="metabox-holder">';
		foreach ( $wpmudev_video_pages[ $screen_id ] as $video ) {
			//skip if not set in master list
			if ( ! isset( $wpmudev_vids->video_list[ $video ] ) ) {
				continue;
			}

			//remove any hidden videos from the list
			if ( isset( $hidden[ $video ] ) ) {
				continue;
			}

			$contextual_help .= '<div id="wpmudev_vid_' . $video . '" class="postbox" style="width: 520px;float: left;margin-right: 10px;">
				<h3 class="hndle"><span>' . esc_attr( $wpmudev_vids->video_list[ $video ] ) . '</span></h3>
				<div class="inside">
					<iframe data-src="' . $wpmudev_vids->create_embed_url( $video ) . '" frameborder="0" width="500" height="281" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
				</div>
			</div>';
		}
		$contextual_help .= '<div class="clear"></div></div>';

		$screen->add_help_tab( array(
			'id'      => 'wpmudev_vids',
			'title'   => $wpmudev_vids->get_setting( 'menu_title' ),
			'content' => $contextual_help
		) );
	}

	return $old_help;
}

function wpmudev_vids_help_js() {
	?>
	<script type="text/javascript">
		(function ($) {
			var $video = $('#tab-panel-wpmudev_vids iframe');
			$('[aria-controls="tab-panel-wpmudev_vids"]').one('click', function () {
				$video.each(function () {
					$(this).attr('src', $(this).attr('data-src'));
				});
			});
			var $gute_videos = $('#wpmudev_vids_gutenberg_metabox iframe');
			$('#wpmudev_vids_gutenberg_metabox').one('click', function () {
				$gute_videos.each(function () {
					$(this).attr('src', $(this).attr('data-src'));
				});
			});
			if (false === $('#wpmudev_vids_gutenberg_metabox').hasClass('closed')) {
				$gute_videos.each(function () {
					$(this).attr('src', $(this).attr('data-src'));
				});
			}
		})(jQuery);
	</script>
	<?php
}

add_action( 'admin_footer', 'wpmudev_vids_help_js' );


function wpmudev_vids_gutenberg_metabox() {
	global $wpmudev_video_pages, $wpmudev_vids, $wp_version;
	$current_screen = get_current_screen();
	if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		add_meta_box( 'wpmudev_vids_gutenberg_metabox', $wpmudev_vids->get_setting( 'menu_title' ), 'wpmudev_vids_gutenberg_metabox_callback', array( 'post', 'page' ), 'side' );
	}
}
add_action( 'add_meta_boxes', 'wpmudev_vids_gutenberg_metabox' );

function wpmudev_vids_gutenberg_metabox_callback( $post ) {
	global $wpmudev_video_pages, $wpmudev_vids, $wp_version;
	$hidden          = $wpmudev_vids->get_setting( 'hide' );
	$contextual_help = '';
	foreach ( $wpmudev_video_pages[ 'gutenberg-editor' ] as $video ) {
		//skip if not set in master list
		if ( ! isset( $wpmudev_vids->video_list[ $video ] ) ) {
			continue;
		}

		//remove any hidden videos from the list
		if ( isset( $hidden[ $video ] ) ) {
			continue;
		}

		$contextual_help .= '
				<h4>' . esc_attr( $wpmudev_vids->video_list[ $video ] ) . '</h4>
				<iframe data-src="' . $wpmudev_vids->create_embed_url( $video ) . '" frameborder="0" width="250" height="140" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>

			';
	}

	echo $contextual_help;
}