<div id="wdqs-dashboard-widget">
	<?php if ($status) { ?>
		<div class="wdqs-update-notice updated below-h2">
			<p><?php printf( __('Post updated. <a href="%s">View post</a>'), esc_url( get_permalink($status) ) );?></p>
		</div>
	<?php } ?>
	<div id="wdqs-form-root">
		<?php do_action('wdqs-form-before_form'); ?>
		<p>
			<div id="wdqs-types-tabs">
				<b>Post:</b>
				<a href="#generic" class="wdqs-type-switch" id="wdqs-generic-switch"><?php _e("Status", "wdqs")?></a>
				<a href="#videos" class="wdqs-type-switch" id="wdqs-video-switch"><?php _e("Video", "wdqs")?></a>
				<a href="#images" class="wdqs-type-switch" id="wdqs-image-switch"><?php _e("Image", "wdqs")?></a>
				<a href="#links" class="wdqs-type-switch" id="wdqs-link-switch"><?php _e("Link", "wdqs")?></a>
			</div>
			<div id="wdqs-status-arrow-container">
			<div id="wdqs-status-arrow"></div>
			</div>
			<textarea rows="1" class="widefat" id="wdqs-status" name="wdqs-status"></textarea>
		</p>
		<p id="wdqs-controls">
			<input type="button" class="button" id="wdqs-preview" value="<?php _e("Preview", 'wdqs');?>" />
			<input type="button" class="button" id="wdqs-reset" value="<?php _e("Forget it", 'wdqs');?>" />
			<input type="button" class="button-primary" id="wdqs-post" value="<?php current_user_can("publish_posts")
				? _e("Post", 'wdqs')
				: _e("Submit for Review", "wdqs")
			;
			?>" />
			<input type="button" class="button" id="wdqs-draft" value="<?php _e("Draft", 'wdqs');?>" />
		</p>
		<?php do_action('wdqs-form-after_form'); ?>
	</div>
	<input type="hidden" id="wdqs-link-type" value="" />
	<?php do_action('wdqs-form-before_preview'); ?>
	<div id="wdqs-preview-root"></div>
	<?php do_action('wdqs-form-after_preview'); ?>
</div>