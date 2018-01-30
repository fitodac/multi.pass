<section class="tab wds-report-vertical-tab">
	<?php
		// Required
		$tab_id = empty($tab_id) ? '' : $tab_id;
		$tab_name = empty($tab_name) ? '' : $tab_name;
		$is_active = empty($is_active) ? false : $is_active;
		$tab_sections = !empty($tab_sections) && is_array($tab_sections) ? $tab_sections : array();
		$option_name = empty($_view['option_name']) ? '' : $_view['option_name'];

		// Optional
		$button_text = isset($button_text) ? $button_text : __('Save Settings', 'wds');
		$before_output = isset($before_output) ? $before_output : null;
		$after_output = isset($after_output) ? $after_output : null;
		$title_button = isset($title_button) ? $title_button : 'ignore';

		// Variables
		$is_singular = count($tab_sections) == 1;
		$wds_options = WDS_Settings::get_options();
	?>
	<input
		type="radio"
		name="wds-admin-active-tab"
		id="<?php echo esc_attr($tab_id); ?>"
		value="<?php echo esc_attr($tab_id); ?>"
		<?php checked($is_active); ?> />

	<label for="<?php echo esc_attr($tab_id); ?>">
		<?php echo esc_html($tab_name); ?>
		<span class="wds-issues wds-issues-warning"><span></span></span>
	</label>

	<div class="content wds-content-tabs <?php echo $tab_id; ?> <?php echo $is_singular ? '' : 'wds-accordion'; ?>">
		<h2 class="tab-title">
			<span class="wds-tab-title-part">
				<?php echo esc_html($tab_name); ?>
			</span>
			<span class="wds-tab-title-part">
				<span class="wds-issues wds-issues-warning"><span></span></span>
			</span>
			<span class="wds-tab-title-part">
				<?php if($title_button == 'ignore'): ?>
					<button class="wds-ignore-all wds-button-with-loader wds-button-with-left-loader wds-disabled-during-request button button-small button-dark button-dark-o"><?php esc_html_e('Ignore All', 'wds'); ?></button>
				<?php endif; ?>

				<?php if($title_button == 'upgrade'): ?>
					<button class="wds-upgrade-button button-green"><?php esc_html_e('Upgrade to Pro', 'wds'); ?></button>
				<?php endif; ?>
			</span>
		</h2>

		<?php if ($before_output): ?>
			<?php echo($before_output); ?>
			<input
				type="hidden"
				name="wds-admin-active-tab"
				id="<?php echo esc_attr($tab_id); ?>"
				value="<?php echo esc_attr($tab_id); ?>"/>
		<?php endif; ?>

		<?php foreach ($tab_sections as $section): ?>
			<?php
			$this->_render('vertical-tab-section', array_merge(
				$section,
				array(
					'show_accordion' => !$is_singular
				)
			));
			?>
		<?php endforeach; ?>

		<?php if ($button_text): ?>
			<div class="wds-seamless-footer"></div>
		<?php endif; ?>

		<?php
			if ($after_output) {
				echo($after_output);
			}
		?>
	</div>
</section>